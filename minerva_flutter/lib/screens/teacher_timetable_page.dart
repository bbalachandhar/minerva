import 'package:flutter/material.dart';

import '../services/api/staff_workspace_api.dart';

class TeacherTimetablePage extends StatefulWidget {
  const TeacherTimetablePage({super.key});

  @override
  State<TeacherTimetablePage> createState() => _TeacherTimetablePageState();
}

class _TeacherTimetablePageState extends State<TeacherTimetablePage> {
  bool _isLoading = true;
  String? _error;
  Map<String, List<Map<String, dynamic>>> _timetable =
      <String, List<Map<String, dynamic>>>{};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    final start = DateTime.now();
    final end = start.add(const Duration(days: 6));
    String dateKey(DateTime d) =>
        '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';

    final response = await StaffWorkspaceApi.getTeacherTimetable(
      startDate: dateKey(start),
      endDate: dateKey(end),
    );

    if (!mounted) return;

    if ((response['status'] ?? 0).toString() == '1') {
      final mapped = <String, List<Map<String, dynamic>>>{};
      final table = response['timetable'];
      if (table is Map) {
        table.forEach((date, value) {
          final rows = <Map<String, dynamic>>[];
          if (value is List) {
            for (final item in value) {
              if (item is Map) {
                rows.add(Map<String, dynamic>.from(item));
              }
            }
          }
          mapped[date.toString()] = rows;
        });
      }

      setState(() {
        _timetable = mapped;
        _isLoading = false;
      });
      return;
    }

    setState(() {
      _error = (response['message'] ?? 'Unable to load timetable').toString();
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Teacher Timetable'),
        actions: [
          IconButton(onPressed: _load, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(child: Text(_error!))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                if (_timetable.isEmpty)
                  const Text('No timetable records found for this week.'),
                for (final entry in _timetable.entries) ...[
                  Padding(
                    padding: const EdgeInsets.only(top: 12, bottom: 8),
                    child: Text(
                      entry.key,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  if (entry.value.isEmpty)
                    const Card(
                      child: ListTile(title: Text('No classes scheduled')),
                    ),
                  for (final row in entry.value)
                    Card(
                      child: ListTile(
                        title: Text(
                          '${(row['subject_name'] ?? '').toString()} (${(row['subject_code'] ?? '').toString()})',
                        ),
                        subtitle: Text(
                          'Class ${(row['class'] ?? '').toString()}-${(row['section'] ?? '').toString()}  Room ${(row['room_no'] ?? '-').toString()}',
                        ),
                        trailing: Text(
                          '${(row['time_from'] ?? '').toString()} - ${(row['time_to'] ?? '').toString()}',
                          style: const TextStyle(fontSize: 12),
                        ),
                      ),
                    ),
                ],
              ],
            ),
    );
  }
}
