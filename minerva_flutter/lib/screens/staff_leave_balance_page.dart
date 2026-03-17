import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api/staff_workspace_api.dart';
import '../services/auth_service.dart';

class StaffLeaveBalancePage extends StatefulWidget {
  const StaffLeaveBalancePage({super.key});

  @override
  State<StaffLeaveBalancePage> createState() => _StaffLeaveBalancePageState();
}

class _StaffLeaveBalancePageState extends State<StaffLeaveBalancePage> {
  bool _isLoading = true;
  String? _error;
  int _staffId = 0;
  List<Map<String, dynamic>> _leaveBalance = [];
  String? _employeeId;

  String? _findEmployeeId(dynamic node) {
    if (node is Map) {
      final direct = (node['employee_id'] ?? '').toString().trim();
      if (direct.isNotEmpty) return direct;
      for (final value in node.values) {
        final found = _findEmployeeId(value);
        if (found != null && found.isNotEmpty) return found;
      }
    } else if (node is List) {
      for (final item in node) {
        final found = _findEmployeeId(item);
        if (found != null && found.isNotEmpty) return found;
      }
    }
    return null;
  }

  @override
  void initState() {
    super.initState();
    _loadLeaveBalance();
  }

  Future<void> _loadLeaveBalance() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final staffIdStr = prefs.getString('staff_id') ?? '';
      _staffId = int.tryParse(staffIdStr) ?? 0;

      if (_staffId <= 0) {
        // Try to get from auth service or user ID
        final userId = await AuthService.getUserId();
        _staffId = int.tryParse(userId ?? '') ?? 0;
      }

      final loginDataStr = prefs.getString('login_data');
      if (loginDataStr != null && loginDataStr.isNotEmpty) {
        try {
          final loginData = jsonDecode(loginDataStr);
          final emp = _findEmployeeId(loginData);
          if (emp != null && emp.isNotEmpty) {
            _employeeId = emp;
          }
        } catch (_) {
          // Ignore malformed cached login_data.
        }
      }

      // Fetch authoritative staff identity from backend profile endpoint.
      // This avoids mismatches between users.id and staff.id across installations.
      final staffProfileResponse = await StaffWorkspaceApi.getStaffProfile();
      if ((staffProfileResponse['status'] ?? 0).toString() == '1') {
        final staffResult = staffProfileResponse['staff_result'];
        if (staffResult is Map) {
          final profileStaffId = int.tryParse(
            (staffResult['staff_id'] ?? '').toString(),
          );
          if ((profileStaffId ?? 0) > 0) {
            _staffId = profileStaffId!;
          }

          final profileEmployeeId = (staffResult['employee_id'] ?? '')
              .toString()
              .trim();
          if (profileEmployeeId.isNotEmpty) {
            _employeeId = profileEmployeeId;
          }
        }
      }

      if (_staffId <= 0) {
        setState(() {
          _error = 'Staff ID not found. Please log in again.';
          _isLoading = false;
        });
        return;
      }

      final response = await StaffWorkspaceApi.getStaffLeaveBalance(
        staffId: _staffId,
        employeeId: _employeeId,
      );

      if (!mounted) return;

      if ((response['status'] ?? 0) == 1) {
        final balance =
            (response['leave_balance'] as List<dynamic>?)
                ?.map((b) => Map<String, dynamic>.from(b as Map))
                .toList() ??
            [];

        setState(() {
          _leaveBalance = balance;
          _isLoading = false;
        });
      } else {
        setState(() {
          _error =
              response['message']?.toString() ?? 'Failed to load leave balance';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Error loading leave balance: $e';
        _isLoading = false;
      });
    }
  }

  Widget _buildLeaveCard(Map<String, dynamic> leave) {
    final type = leave['type']?.toString() ?? 'Unknown';
    final allocated = double.tryParse('${leave['allocated'] ?? 0}') ?? 0;
    final used = double.tryParse('${leave['used'] ?? 0}') ?? 0;
    final remaining = double.tryParse('${leave['remaining'] ?? 0}') ?? 0;

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  type,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: remaining > 0
                        ? Colors.green.shade100
                        : Colors.red.shade100,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '${remaining.toStringAsFixed(2)} left',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: remaining > 0 ? Colors.green.shade700 : Colors.red,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Allocated',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        allocated.toStringAsFixed(2),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Used',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        used.toStringAsFixed(2),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Remaining',
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        remaining.toStringAsFixed(2),
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: remaining > 0 ? Colors.green : Colors.red,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: LinearProgressIndicator(
                value: allocated > 0 ? used / allocated : 0,
                minHeight: 6,
                backgroundColor: Colors.grey.shade200,
                valueColor: AlwaysStoppedAnimation<Color>(
                  (allocated > 0 ? used / allocated : 0) < 0.75
                      ? Colors.blue
                      : (allocated > 0 ? used / allocated : 0) < 1.0
                      ? Colors.orange
                      : Colors.red,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Leave Balance'),
        elevation: 0,
        backgroundColor: Colors.blue.shade700,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.error, size: 64, color: Colors.red.shade300),
                  const SizedBox(height: 16),
                  Text(
                    _error!,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 16),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton(
                    onPressed: _loadLeaveBalance,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : _leaveBalance.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.info, size: 64, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  const Text(
                    'No leave types allocated',
                    style: TextStyle(fontSize: 16),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadLeaveBalance,
              child: ListView(
                children: [
                  const Padding(
                    padding: EdgeInsets.all(16),
                    child: Text(
                      'Leave entitlements and usage',
                      style: TextStyle(fontSize: 14, color: Colors.grey),
                    ),
                  ),
                  ..._leaveBalance.map((leave) => _buildLeaveCard(leave)),
                  const SizedBox(height: 16),
                ],
              ),
            ),
    );
  }
}
