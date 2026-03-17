import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api/profile_api.dart';

class StaffProfilePage extends StatefulWidget {
  const StaffProfilePage({super.key});

  @override
  State<StaffProfilePage> createState() => _StaffProfilePageState();
}

class _StaffProfilePageState extends State<StaffProfilePage> {
  Map<String, dynamic> _profile = <String, dynamic>{};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadStaffProfile();
  }

  Future<void> _loadStaffProfile() async {
    Map<String, dynamic>? profile;

    try {
      final apiResponse = await ProfileApi.getStaffProfile();
      final staffResult = apiResponse['staff_result'];
      if (staffResult is Map) {
        profile = Map<String, dynamic>.from(staffResult);
      }
    } catch (_) {
      // Fall back to locally cached login payload when API fetch fails.
    }

    profile ??= await _loadStaffProfileFromSession();

    if (!mounted) return;
    setState(() {
      _profile = profile ?? <String, dynamic>{};
      _isLoading = false;
    });
  }

  Future<Map<String, dynamic>> _loadStaffProfileFromSession() async {
    final prefs = await SharedPreferences.getInstance();
    final loginData = prefs.getString('login_data');
    final role = (prefs.getString('role') ?? 'staff').toLowerCase();

    final profile = <String, dynamic>{'role': role};

    if (loginData != null && loginData.isNotEmpty) {
      try {
        final decoded = jsonDecode(loginData);
        if (decoded is Map && decoded['record'] is Map) {
          profile.addAll(Map<String, dynamic>.from(decoded['record']));
        }
      } catch (_) {
        // Ignore malformed stored login payload.
      }
    }

    profile.putIfAbsent(
      'username',
      () => prefs.getString('student_name') ?? 'Staff User',
    );
    profile.putIfAbsent('email', () => prefs.getString('email') ?? '');
    profile.putIfAbsent('image', () => prefs.getString('image') ?? '');

    return profile;
  }

  String _valueOf(List<String> keys, {String fallback = 'N/A'}) {
    for (final key in keys) {
      final value = _profile[key];
      if (value != null) {
        final text = value.toString().trim();
        if (text.isNotEmpty && text.toLowerCase() != 'null') {
          return text;
        }
      }
    }
    return fallback;
  }

  Widget _infoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: const TextStyle(
                fontWeight: FontWeight.w600,
                color: Colors.black54,
              ),
            ),
          ),
          const Text(': ', style: TextStyle(color: Colors.black54)),
          Expanded(
            child: Text(value, style: const TextStyle(color: Colors.black87)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final displayName = _valueOf(<String>['name', 'username']);
    final role = _valueOf(<String>[
      'role',
      'staff_role',
      'designation',
    ], fallback: 'Staff');

    return Scaffold(
      appBar: AppBar(title: const Text('Staff Profile')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.08),
                      blurRadius: 10,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const CircleAvatar(
                          radius: 28,
                          child: Icon(Icons.badge, size: 28),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                displayName,
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                role,
                                style: const TextStyle(color: Colors.black54),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 16),
                    const Divider(),
                    _infoRow(
                      'Employee ID',
                      _valueOf(<String>['employee_id', 'staff_id', 'id']),
                    ),
                    _infoRow(
                      'Designation',
                      _valueOf(<String>[
                        'designation',
                        'staff_role',
                      ], fallback: '-'),
                    ),
                    _infoRow(
                      'Department',
                      _valueOf(<String>[
                        'department',
                        'department_name',
                      ], fallback: '-'),
                    ),
                    _infoRow(
                      'Email',
                      _valueOf(<String>['email'], fallback: '-'),
                    ),
                    _infoRow(
                      'Mobile',
                      _valueOf(<String>[
                        'mobileno',
                        'mobile',
                        'contact_no',
                      ], fallback: '-'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}
