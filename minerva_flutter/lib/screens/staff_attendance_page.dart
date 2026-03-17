import 'package:flutter/material.dart';

import '../services/api/staff_workspace_api.dart';

class StaffAttendancePage extends StatefulWidget {
  const StaffAttendancePage({super.key});

  @override
  State<StaffAttendancePage> createState() => _StaffAttendancePageState();
}

class _StaffAttendancePageState extends State<StaffAttendancePage> {
  bool _isLoading = true;
  String? _error;
  DateTime _selectedMonth = DateTime(DateTime.now().year, DateTime.now().month);
  Map<String, int> _counts = <String, int>{};
  List<Map<String, dynamic>> _recent = <Map<String, dynamic>>[];

  @override
  void initState() {
    super.initState();
    _load();
  }

  String _monthKey(DateTime date) {
    final month = date.month.toString().padLeft(2, '0');
    return '${date.year}-$month';
  }

  String _monthLabel(DateTime date) {
    const names = <String>[
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December',
    ];
    return '${names[date.month - 1]} ${date.year}';
  }

  String _normToken(String value) {
    return value.trim().toLowerCase().replaceAll(RegExp(r'[^a-z0-9]+'), '_');
  }

  bool _isPresentToken(String token) {
    const presentVariants = <String>{
      'p',
      'present',
      'fhl',
      'shl',
      'fhp',
      'shp',
      'fha',
      'sha',
      'first_half_late',
      'second_half_late',
      'first_half_permission',
      'second_half_permission',
      'first_half_absent',
      'second_half_absent',
      'permission_first_session',
      'permission_second_session',
    };
    return presentVariants.contains(token);
  }

  bool _isLateToken(String token) {
    return token == 'l' || token == 'late';
  }

  bool _isAbsentToken(String token) {
    return token == 'a' || token == 'absent';
  }

  bool _isHalfDayToken(String token) {
    return token == 'h' ||
        token == 'half_day' ||
        token == 'halfday' ||
        token == 'fh' ||
        token == 'sh';
  }

  Map<String, int> _buildSummaryCounts(Map<String, dynamic> response) {
    // Use whatever counts come from API directly
    final byKeyObj = response['counts_by_key'];
    final counts = <String, int>{};
    if (byKeyObj is Map) {
      byKeyObj.forEach((key, value) {
        final keyStr = key.toString().trim();
        if (keyStr.isNotEmpty && keyStr != 'H' && keyStr != 'W') {
          // Include all attendance type counts, exclude H/W which are non-working days
          counts[keyStr] = int.tryParse(value.toString()) ?? 0;
        }
      });
    }
    return counts;
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final response = await StaffWorkspaceApi.getAttendanceSummary(
      month: _monthKey(_selectedMonth),
    );

    final status = (response['status'] ?? 0).toString();
    if (!mounted) return;

    if (status == '1') {
      final counts = _buildSummaryCounts(response);

      final recent = <Map<String, dynamic>>[];
      final recentRaw = response['recent_records'];
      if (recentRaw is List) {
        for (final item in recentRaw) {
          if (item is Map) {
            recent.add(Map<String, dynamic>.from(item));
          }
        }
      }

      setState(() {
        _counts = counts;
        _recent = recent;
        _isLoading = false;
      });
      return;
    }

    setState(() {
      _error = (response['message'] ?? 'Unable to load staff attendance')
          .toString();
      _isLoading = false;
    });
  }

  Widget _buildSummaryCard(String label, int value, Color color) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            value.toString(),
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 4),
          Text(label, style: const TextStyle(color: Colors.black54)),
        ],
      ),
    );
  }

  Color _statusColor(Map<String, dynamic> record) {
    final key = _normToken((record['status_key'] ?? '').toString());
    final label = _normToken((record['status_label'] ?? '').toString());
    final token = key.isNotEmpty ? key : label;

    if (token == 'h' || token == 'holiday') {
      return Colors.deepPurple;
    }
    if (token == 'w' || token == 'weekend') {
      return Colors.blueGrey;
    }

    if (_isPresentToken(token)) {
      return Colors.green;
    }
    if (_isLateToken(token)) {
      return Colors.orange;
    }
    if (_isAbsentToken(token)) {
      return Colors.red;
    }
    if (_isHalfDayToken(token)) {
      return Colors.blue;
    }

    return Colors.grey;
  }

  List<Widget> _buildDynamicSummaryCards() {
    final keyColorMap = <String, Color>{
      'P': Colors.green,
      'L': Colors.orange,
      'FHL': Colors.amber,
      'SHL': Colors.amber,
      'FHP': Colors.purple,
      'SHP': Colors.purple,
      'HD': Colors.blue,
      'A': Colors.red,
      'FHA': Colors.red,
      'SHA': Colors.red,
    };

    final keyLabelMap = <String, String>{
      'P': 'Present',
      'L': 'Late',
      'FHL': 'First Half Late',
      'SHL': 'Second Half Late',
      'FHP': 'Permission (FH)',
      'SHP': 'Permission (SH)',
      'HD': 'Half Day',
      'A': 'Absent',
      'FHA': 'Absent (FH)',
      'SHA': 'Absent (SH)',
    };

    final cards = <Widget>[];
    _counts.forEach((key, value) {
      final label = keyLabelMap[key] ?? key;
      final color = keyColorMap[key] ?? Colors.grey;
      cards.add(_buildSummaryCard(label, value, color));
    });

    return cards;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Staff Attendance'),
        actions: [
          IconButton(onPressed: _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(child: Text(_error!))
          : RefreshIndicator(
              onRefresh: _load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      IconButton(
                        onPressed: () {
                          setState(() {
                            _selectedMonth = DateTime(
                              _selectedMonth.year,
                              _selectedMonth.month - 1,
                            );
                          });
                          _load();
                        },
                        icon: const Icon(Icons.chevron_left),
                      ),
                      Text(
                        _monthLabel(_selectedMonth),
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      IconButton(
                        onPressed: () {
                          setState(() {
                            _selectedMonth = DateTime(
                              _selectedMonth.year,
                              _selectedMonth.month + 1,
                            );
                          });
                          _load();
                        },
                        icon: const Icon(Icons.chevron_right),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  if (_counts.isEmpty)
                    const Text('No attendance data for this month.')
                  else
                    GridView.count(
                      crossAxisCount: 2,
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      childAspectRatio: 1.9,
                      children: _buildDynamicSummaryCards(),
                    ),
                  const SizedBox(height: 18),
                  const Text(
                    'Month Records',
                    style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 8),
                  if (_recent.isEmpty)
                    const Padding(
                      padding: EdgeInsets.all(12),
                      child: Text('No records found for this month.'),
                    )
                  else
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: DataTable(
                        columnSpacing: 16,
                        border: TableBorder.all(color: Colors.grey.shade300),
                        columns: const [
                          DataColumn(
                            label: Text(
                              'Date',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                          DataColumn(
                            label: Text(
                              'Day',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                          DataColumn(
                            label: Text(
                              'Status',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                          DataColumn(
                            label: Text(
                              'In Time',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                          DataColumn(
                            label: Text(
                              'Out Time',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                          DataColumn(
                            label: Text(
                              'Remark',
                              style: TextStyle(fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                        rows: _recent
                            .map(
                              (record) => DataRow(
                                cells: [
                                  DataCell(
                                    Text(
                                      (record['date'] ?? '')
                                          .toString()
                                          .substring(5),
                                    ),
                                  ), // MM-DD
                                  DataCell(
                                    Text(_getDayName(record['date'] ?? '')),
                                  ),
                                  DataCell(
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(
                                        color: _statusColor(
                                          record,
                                        ).withValues(alpha: 0.2),
                                        borderRadius: BorderRadius.circular(4),
                                      ),
                                      child: Text(
                                        _getStatusCode(record),
                                        style: TextStyle(
                                          color: _statusColor(record),
                                          fontSize: 11,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ),
                                  DataCell(
                                    Text((record['in_time'] ?? '-').toString()),
                                  ),
                                  DataCell(
                                    Text(
                                      (record['out_time'] ?? '-').toString(),
                                    ),
                                  ),
                                  DataCell(
                                    Text(
                                      _getRemark(record),
                                      style: const TextStyle(fontSize: 11),
                                    ),
                                  ),
                                ],
                              ),
                            )
                            .toList(),
                      ),
                    ),
                ],
              ),
            ),
    );
  }

  String _getDayName(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
      return days[date.weekday - 1];
    } catch (e) {
      return '';
    }
  }

  String _getStatusCode(Map<String, dynamic> record) {
    final key = (record['status_key'] ?? '').toString().trim();
    final label = (record['status_label'] ?? '').toString().trim();

    if (key == 'H') return 'H';
    if (key == 'W') return 'W';
    if (key.isNotEmpty) return key;
    if (label == 'Holiday') return 'H';
    if (label == 'Weekend') return 'W';
    if (label == 'Not Marked') return '-';
    return label.isNotEmpty
        ? label.substring(0, label.length > 3 ? 3 : label.length)
        : '-';
  }

  String _getRemark(Map<String, dynamic> record) {
    final key = (record['status_key'] ?? '').toString().trim().toLowerCase();
    final label = (record['status_label'] ?? '').toString().trim();
    final remarks = (record['remark'] ?? '').toString().trim();

    // Map keys to readable remarks
    if (key == 'p' || key == 'present') {
      return 'Present';
    }
    if (key == 'h' || key == 'holiday') {
      return 'Holiday';
    }
    if (key == 'w' || key == 'weekend') {
      return 'Weekend';
    }
    if (key == 'a' || key == 'absent') {
      return 'Absent';
    }
    if (key == 'l' || key == 'late') {
      return 'Late';
    }
    if (key == 'fhl' || key == 'first_half_late') {
      return 'First Half Late';
    }
    if (key == 'shl' || key == 'second_half_late') {
      return 'Second Half Late';
    }
    if (key == 'fhp' || key == 'first_half_permission') {
      return 'First Half Permission';
    }
    if (key == 'shp' || key == 'second_half_permission') {
      return 'Second Half Permission';
    }
    if (key == 'fha' || key == 'first_half_absent') {
      return 'First Half Absent';
    }
    if (key == 'sha' || key == 'second_half_absent') {
      return 'Second Half Absent';
    }
    if (key == 'hd' || key == 'half_day') {
      return 'Half Day';
    }

    // Use label if key didn't match
    if (label.isNotEmpty && label != 'Not Marked') {
      return label;
    }

    // Use remark field if available
    if (remarks.isNotEmpty) {
      return remarks;
    }

    return '-';
  }
}
