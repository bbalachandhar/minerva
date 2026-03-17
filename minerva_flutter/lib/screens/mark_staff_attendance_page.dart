import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api/staff_workspace_api.dart';

class MarkStaffAttendancePage extends StatefulWidget {
  const MarkStaffAttendancePage({super.key, required this.attendanceType});

  // 0 = day wise, 1 = period wise
  final int attendanceType;

  @override
  State<MarkStaffAttendancePage> createState() =>
      _MarkStaffAttendancePageState();
}

class _MarkStaffAttendancePageState extends State<MarkStaffAttendancePage> {
  bool _loadingTimetable = true;
  String? _timetableError;

  DateTime _selectedDate = DateTime.now();

  List<Map<String, dynamic>> _todayPeriods = <Map<String, dynamic>>[];
  List<Map<String, dynamic>> _dayWiseClassSections = <Map<String, dynamic>>[];

  Map<String, dynamic>? _selectedPeriod;
  Map<String, dynamic>? _selectedDayWiseClassSection;

  bool _loadingRoster = false;
  String? _rosterError;

  List<Map<String, dynamic>> _attendanceTypes = <Map<String, dynamic>>[];
  List<Map<String, dynamic>> _students = <Map<String, dynamic>>[];

  final Map<int, TextEditingController> _remarkControllers =
      <int, TextEditingController>{};

  bool _saving = false;

  bool get _isPeriodWise => widget.attendanceType == 1;

  @override
  void initState() {
    super.initState();
    _loadTodayTimetable();
  }

  @override
  void dispose() {
    for (final controller in _remarkControllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  String _fmt(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

  String _dayName(DateTime d) => const <String>[
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
    'Sunday',
  ][d.weekday - 1];

  String _modeLabel() => _isPeriodWise ? 'Period Wise' : 'Day Wise';

  Future<void> _loadTodayTimetable() async {
    setState(() {
      _loadingTimetable = true;
      _timetableError = null;
      _todayPeriods = <Map<String, dynamic>>[];
      _dayWiseClassSections = <Map<String, dynamic>>[];
      _selectedPeriod = null;
      _selectedDayWiseClassSection = null;
      _students = <Map<String, dynamic>>[];
      _rosterError = null;
      _clearRemarkControllers();
    });

    final dateStr = _fmt(_selectedDate);
    final result = await StaffWorkspaceApi.getTeacherTimetable(
      startDate: dateStr,
      endDate: dateStr,
    );

    if (!mounted) return;

    if ((result['status'] ?? 0).toString() == '1') {
      final timetable =
          result['timetable'] as Map<String, dynamic>? ?? <String, dynamic>{};
      final dayEntries = timetable[dateStr] as List<dynamic>? ?? <dynamic>[];
      final periods = dayEntries
          .map((e) => Map<String, dynamic>.from(e as Map))
          .toList();

      final uniqClassSection = <String, Map<String, dynamic>>{};
      for (final period in periods) {
        final classId =
            int.tryParse((period['class_id'] ?? '').toString()) ?? 0;
        final sectionId =
            int.tryParse((period['section_id'] ?? '').toString()) ?? 0;
        if (classId <= 0 || sectionId <= 0) continue;
        final key = '$classId-$sectionId';
        uniqClassSection[key] = <String, dynamic>{
          'class_id': classId,
          'section_id': sectionId,
          'class': (period['class'] ?? '').toString(),
          'section': (period['section'] ?? '').toString(),
        };
      }

      setState(() {
        _todayPeriods = periods;
        _dayWiseClassSections = uniqClassSection.values.toList();
        _loadingTimetable = false;
      });
    } else {
      setState(() {
        _timetableError =
            result['message']?.toString() ?? 'Failed to load timetable';
        _loadingTimetable = false;
      });
    }
  }

  int _presentAttendanceTypeId(List<Map<String, dynamic>> types) {
    for (final t in types) {
      final token = (t['key_value'] ?? '').toString().toLowerCase();
      if (token == 'present' || token == 'p') {
        return int.tryParse((t['id'] ?? '0').toString()) ?? 0;
      }
    }
    if (types.isEmpty) return 0;
    return int.tryParse((types.first['id'] ?? '0').toString()) ?? 0;
  }

  List<Map<String, dynamic>> _filterAllowedTypes(
    List<Map<String, dynamic>> raw,
  ) {
    const allowed = <String>{
      'present',
      'p',
      'late',
      'l',
      'absent',
      'a',
      'holiday',
      'h',
      'half day',
      'half_day',
      'halfday',
      'hd',
    };

    return raw.where((type) {
      final keyToken = (type['key_value'] ?? '').toString().toLowerCase();
      final labelToken = (type['type'] ?? '').toString().toLowerCase();
      return allowed.contains(keyToken) || allowed.contains(labelToken);
    }).toList();
  }

  Future<void> _loadRosterForPeriod(Map<String, dynamic> period) async {
    final subjectTimetableId =
        int.tryParse((period['id'] ?? '').toString()) ?? 0;
    if (subjectTimetableId <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Invalid subject timetable id.')),
      );
      return;
    }

    setState(() {
      _selectedPeriod = period;
      _loadingRoster = true;
      _rosterError = null;
      _students = <Map<String, dynamic>>[];
      _attendanceTypes = <Map<String, dynamic>>[];
      _clearRemarkControllers();
    });

    final result =
        await StaffWorkspaceApi.getPeriodWiseStudentRosterForAttendance(
          subjectTimetableId: subjectTimetableId,
          date: _fmt(_selectedDate),
        );

    if (!mounted) return;

    if ((result['status'] ?? 0).toString() == '1') {
      final allTypes =
          (result['attendance_types'] as List<dynamic>? ?? <dynamic>[])
              .map((t) => Map<String, dynamic>.from(t as Map))
              .toList();
      final types = _filterAllowedTypes(allTypes);
      final defaultTypeId = _presentAttendanceTypeId(types);

      final students = (result['students'] as List<dynamic>? ?? <dynamic>[])
          .map((s) => Map<String, dynamic>.from(s as Map))
          .toList();

      for (final student in students) {
        final selected = int.tryParse(
          (student['attendence_type_id'] ?? '').toString(),
        );
        student['_selected_type_id'] = (selected != null && selected > 0)
            ? selected
            : defaultTypeId;

        final sid =
            int.tryParse((student['student_session_id'] ?? '').toString()) ?? 0;
        if (sid > 0) {
          _remarkControllers[sid] = TextEditingController(
            text: (student['remark'] ?? '').toString(),
          );
        }
      }

      setState(() {
        _attendanceTypes = types;
        _students = students;
        _loadingRoster = false;
      });
    } else {
      setState(() {
        _rosterError =
            result['message']?.toString() ?? 'Failed to load students';
        _loadingRoster = false;
      });
    }
  }

  Future<void> _loadRosterForDayWise(Map<String, dynamic> classSection) async {
    final classId =
        int.tryParse((classSection['class_id'] ?? '').toString()) ?? 0;
    final sectionId =
        int.tryParse((classSection['section_id'] ?? '').toString()) ?? 0;
    if (classId <= 0 || sectionId <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Invalid class or section.')),
      );
      return;
    }

    setState(() {
      _selectedDayWiseClassSection = classSection;
      _loadingRoster = true;
      _rosterError = null;
      _students = <Map<String, dynamic>>[];
      _attendanceTypes = <Map<String, dynamic>>[];
      _clearRemarkControllers();
    });

    final result = await StaffWorkspaceApi.getStudentRosterForAttendance(
      classId: classId,
      sectionId: sectionId,
      date: _fmt(_selectedDate),
    );

    if (!mounted) return;

    if ((result['status'] ?? 0).toString() == '1') {
      final allTypes =
          (result['attendance_types'] as List<dynamic>? ?? <dynamic>[])
              .map((t) => Map<String, dynamic>.from(t as Map))
              .toList();
      final types = _filterAllowedTypes(allTypes);
      final defaultTypeId = _presentAttendanceTypeId(types);

      final students = (result['students'] as List<dynamic>? ?? <dynamic>[])
          .map((s) => Map<String, dynamic>.from(s as Map))
          .toList();

      for (final student in students) {
        final selected = int.tryParse(
          (student['attendence_type_id'] ?? '').toString(),
        );
        student['_selected_type_id'] = (selected != null && selected > 0)
            ? selected
            : defaultTypeId;

        final sid =
            int.tryParse((student['student_session_id'] ?? '').toString()) ?? 0;
        if (sid > 0) {
          _remarkControllers[sid] = TextEditingController(
            text: (student['remark'] ?? '').toString(),
          );
        }
      }

      setState(() {
        _attendanceTypes = types;
        _students = students;
        _loadingRoster = false;
      });
    } else {
      setState(() {
        _rosterError =
            result['message']?.toString() ?? 'Failed to load students';
        _loadingRoster = false;
      });
    }
  }

  void _clearRemarkControllers() {
    for (final controller in _remarkControllers.values) {
      controller.dispose();
    }
    _remarkControllers.clear();
  }

  Future<void> _saveAttendance() async {
    if (_students.isEmpty) return;

    setState(() => _saving = true);

    final dateStr = _fmt(_selectedDate);
    final rows = <Map<String, dynamic>>[];

    for (final student in _students) {
      final sid =
          int.tryParse((student['student_session_id'] ?? '').toString()) ?? 0;
      final typeId =
          int.tryParse((student['_selected_type_id'] ?? '').toString()) ?? 0;
      if (sid <= 0 || typeId <= 0) continue;

      final row = <String, dynamic>{
        'student_session_id': sid,
        'attendence_type_id': typeId,
        'date': dateStr,
      };

      final remark = _remarkControllers[sid]?.text.trim() ?? '';
      row['remark'] = remark;

      if (_isPeriodWise && _selectedPeriod != null) {
        final subjectTimetableId =
            int.tryParse((_selectedPeriod!['id'] ?? '').toString()) ?? 0;
        row['subject_timetable_id'] = subjectTimetableId;
      } else if (!_isPeriodWise && _selectedDayWiseClassSection != null) {
        row['class_id'] =
            int.tryParse(
              (_selectedDayWiseClassSection!['class_id'] ?? '').toString(),
            ) ??
            0;
        row['section_id'] =
            int.tryParse(
              (_selectedDayWiseClassSection!['section_id'] ?? '').toString(),
            ) ??
            0;
      }

      rows.add(row);
    }

    if (rows.isEmpty) {
      setState(() => _saving = false);
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('No valid attendance rows to save.')),
      );
      return;
    }

    final result = _isPeriodWise
        ? await StaffWorkspaceApi.savePeriodWiseStudentAttendance(rows: rows)
        : await StaffWorkspaceApi.saveStudentAttendance(rows: rows);

    if (!mounted) return;
    setState(() => _saving = false);

    if ((result['status'] ?? 0).toString() == '1') {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Attendance saved for ${rows.length} student(s).'),
          backgroundColor: Colors.green,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result['message']?.toString() ?? 'Failed to save attendance.',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 60)),
      lastDate: DateTime.now(),
    );

    if (picked != null && picked != _selectedDate) {
      _selectedDate = picked;
      _loadTodayTimetable();
    }
  }

  Color _typeColor(String? keyValue) {
    final token = (keyValue ?? '').toLowerCase();
    switch (token) {
      case 'present':
      case 'p':
        return Colors.green;
      case 'late':
      case 'l':
        return Colors.orange;
      case 'absent':
      case 'a':
        return Colors.red;
      case 'holiday':
      case 'h':
        return Colors.purple;
      case 'half day':
      case 'half_day':
      case 'halfday':
      case 'hd':
        return Colors.blue;
      default:
        return Colors.blueGrey;
    }
  }

  Widget _buildDateBar() {
    return Container(
      color: Colors.blue[600],
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      child: Row(
        children: [
          const Icon(Icons.calendar_today, color: Colors.white70, size: 16),
          const SizedBox(width: 8),
          Text(
            '${_dayName(_selectedDate)}, ${_selectedDate.day}/${_selectedDate.month}/${_selectedDate.year}',
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w600,
            ),
          ),
          const Spacer(),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white.withOpacity(0.18),
              borderRadius: BorderRadius.circular(6),
            ),
            child: Text(
              _modeLabel(),
              style: const TextStyle(
                color: Colors.white,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          const SizedBox(width: 8),
          TextButton(
            onPressed: _pickDate,
            child: const Text(
              'Change',
              style: TextStyle(color: Colors.white70),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError(String message, VoidCallback onRetry) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.error_outline, size: 48, color: Colors.red[400]),
            const SizedBox(height: 12),
            Text(
              message,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey[700]),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh),
              label: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildPeriodTile(int index, Map<String, dynamic> period) {
    final isSelected = _selectedPeriod == period;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      color: isSelected ? Colors.blue[50] : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
        side: BorderSide(
          color: isSelected ? Colors.blue[400]! : Colors.grey[200]!,
          width: isSelected ? 1.5 : 1,
        ),
      ),
      child: ListTile(
        onTap: () => _loadRosterForPeriod(period),
        leading: CircleAvatar(
          backgroundColor: isSelected ? Colors.blue[600] : Colors.grey[300],
          child: Text(
            '${index + 1}',
            style: TextStyle(
              color: isSelected ? Colors.white : Colors.grey[700],
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(
          period['subject_name']?.toString() ?? 'Subject',
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        subtitle: Text(
          '${period['class'] ?? ''}-${period['section'] ?? ''}  •  ${period['time_from'] ?? ''}-${period['time_to'] ?? ''}',
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
        trailing: isSelected
            ? const Icon(Icons.check_circle, color: Colors.blue)
            : const Icon(Icons.arrow_forward_ios, size: 14),
      ),
    );
  }

  Widget _buildDayWiseClassSectionTile(Map<String, dynamic> item) {
    final isSelected = _selectedDayWiseClassSection == item;

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      color: isSelected ? Colors.blue[50] : Colors.white,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(10),
        side: BorderSide(
          color: isSelected ? Colors.blue[400]! : Colors.grey[200]!,
          width: isSelected ? 1.5 : 1,
        ),
      ),
      child: ListTile(
        onTap: () => _loadRosterForDayWise(item),
        title: Text(
          '${item['class'] ?? ''} - ${item['section'] ?? ''}',
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        subtitle: const Text(
          'Select class-section to mark day-wise attendance',
        ),
        trailing: isSelected
            ? const Icon(Icons.check_circle, color: Colors.blue)
            : const Icon(Icons.arrow_forward_ios, size: 14),
      ),
    );
  }

  Widget _buildMarkAllButtons() {
    return Wrap(
      spacing: 4,
      runSpacing: 4,
      children: _attendanceTypes.map((type) {
        final label = type['type']?.toString() ?? '';
        final tid = int.tryParse((type['id'] ?? '').toString()) ?? 0;
        final color = _typeColor(type['key_value']?.toString());
        return OutlinedButton(
          onPressed: tid <= 0
              ? null
              : () {
                  setState(() {
                    for (final student in _students) {
                      student['_selected_type_id'] = tid;
                    }
                  });
                },
          style: OutlinedButton.styleFrom(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            minimumSize: Size.zero,
            textStyle: const TextStyle(fontSize: 11),
            side: BorderSide(color: color),
            foregroundColor: color,
          ),
          child: Text('All $label'),
        );
      }).toList(),
    );
  }

  Widget _buildStudentTile(int index, Map<String, dynamic> student) {
    final sid =
        int.tryParse((student['student_session_id'] ?? '').toString()) ?? 0;
    final selectedTypeId =
        int.tryParse((student['_selected_type_id'] ?? '').toString()) ?? 0;
    final name = '${student['firstname'] ?? ''} ${student['lastname'] ?? ''}'
        .trim();
    final rollNo = (student['roll_no'] ?? '').toString();

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      child: Padding(
        padding: const EdgeInsets.all(10),
        child: Column(
          children: [
            Row(
              children: [
                CircleAvatar(
                  backgroundColor: Colors.blue[100],
                  radius: 18,
                  child: Text(
                    name.isNotEmpty ? name[0].toUpperCase() : '?',
                    style: TextStyle(
                      color: Colors.blue[700],
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: const TextStyle(
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                        ),
                      ),
                      if (rollNo.isNotEmpty)
                        Text(
                          'Roll: $rollNo',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey[600],
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _attendanceTypes.map((type) {
                  final tid = int.tryParse((type['id'] ?? '').toString()) ?? 0;
                  final selected = selectedTypeId == tid;
                  final color = _typeColor(type['key_value']?.toString());
                  return Padding(
                    padding: const EdgeInsets.only(right: 4),
                    child: GestureDetector(
                      onTap: () {
                        setState(() {
                          _students[index]['_selected_type_id'] = tid;
                        });
                      },
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 5,
                        ),
                        decoration: BoxDecoration(
                          color: selected ? color : Colors.transparent,
                          border: Border.all(color: color),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          (type['type'] ?? '').toString(),
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                            color: selected ? Colors.white : color,
                          ),
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 8),
            TextField(
              controller: sid > 0 ? _remarkControllers[sid] : null,
              maxLines: 1,
              decoration: const InputDecoration(
                isDense: true,
                labelText: 'Remark',
                border: OutlineInputBorder(),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildContent() {
    if (_loadingTimetable) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_timetableError != null) {
      return _buildError(_timetableError!, _loadTodayTimetable);
    }

    if (_isPeriodWise && _todayPeriods.isEmpty) {
      return Center(
        child: Text(
          'No periods on ${_dayName(_selectedDate)}.',
          style: TextStyle(color: Colors.grey[700]),
        ),
      );
    }

    if (!_isPeriodWise && _dayWiseClassSections.isEmpty) {
      return Center(
        child: Text(
          'No class-section mapped for ${_dayName(_selectedDate)}.',
          style: TextStyle(color: Colors.grey[700]),
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Text(
          _isPeriodWise
              ? 'Select a period to mark attendance:'
              : 'Select a class-section to mark attendance:',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Colors.grey[700],
          ),
        ),
        const SizedBox(height: 8),
        if (_isPeriodWise)
          ..._todayPeriods.asMap().entries.map(
            (entry) => _buildPeriodTile(entry.key, entry.value),
          )
        else
          ..._dayWiseClassSections.map(_buildDayWiseClassSectionTile),

        if (_selectedPeriod != null ||
            _selectedDayWiseClassSection != null) ...[
          const Divider(height: 28),
          if (_loadingRoster)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(24),
                child: CircularProgressIndicator(),
              ),
            )
          else if (_rosterError != null)
            _buildError(_rosterError!, () {
              if (_isPeriodWise && _selectedPeriod != null) {
                _loadRosterForPeriod(_selectedPeriod!);
              } else if (!_isPeriodWise &&
                  _selectedDayWiseClassSection != null) {
                _loadRosterForDayWise(_selectedDayWiseClassSection!);
              }
            })
          else if (_students.isEmpty)
            const Padding(
              padding: EdgeInsets.all(16),
              child: Text('No students found for selected target.'),
            )
          else ...[
            Row(
              children: [
                Text(
                  '${_students.length} Student(s)',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const Spacer(),
              ],
            ),
            const SizedBox(height: 8),
            if (_attendanceTypes.isNotEmpty) _buildMarkAllButtons(),
            const SizedBox(height: 8),
            ..._students.asMap().entries.map(
              (entry) => _buildStudentTile(entry.key, entry.value),
            ),
            const SizedBox(height: 80),
          ],
        ],
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Mark Student Attendance (${_modeLabel()})',
          style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600),
        ),
        backgroundColor: Colors.blue[600],
        foregroundColor: Colors.white,
        elevation: 0,
        systemOverlayStyle: SystemUiOverlayStyle.light,
      ),
      backgroundColor: Colors.grey[50],
      body: Column(
        children: [
          _buildDateBar(),
          Expanded(child: _buildContent()),
        ],
      ),
      floatingActionButton: _students.isNotEmpty
          ? FloatingActionButton.extended(
              onPressed: _saving ? null : _saveAttendance,
              icon: _saving
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(
                        color: Colors.white,
                        strokeWidth: 2,
                      ),
                    )
                  : const Icon(Icons.save),
              label: Text(_saving ? 'Saving...' : 'Save Attendance'),
              backgroundColor: Colors.green[600],
            )
          : null,
    );
  }
}
