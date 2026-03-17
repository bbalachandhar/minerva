import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api/staff_workspace_api.dart';
import '../services/auth_service.dart';

class StaffLeaveRequestsPage extends StatefulWidget {
  const StaffLeaveRequestsPage({super.key});

  @override
  State<StaffLeaveRequestsPage> createState() => _StaffLeaveRequestsPageState();
}

class _StaffLeaveRequestsPageState extends State<StaffLeaveRequestsPage> {
  bool _isLoading = true;
  String? _error;
  int _staffId = 0;
  List<Map<String, dynamic>> _leaveRequests = [];

  @override
  void initState() {
    super.initState();
    _loadLeaveRequests();
  }

  Future<void> _loadLeaveRequests() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final prefs = await SharedPreferences.getInstance();
      final staffIdStr = prefs.getString('staff_id') ?? '';
      _staffId = int.tryParse(staffIdStr) ?? 0;

      if (_staffId <= 0) {
        // Try to get from auth service
        final userId = await AuthService.getUserId();
        _staffId = int.tryParse(userId ?? '') ?? 0;
      }

      if (_staffId <= 0) {
        setState(() {
          _error = 'Staff ID not found. Please log in again.';
          _isLoading = false;
        });
        return;
      }

      final response = await StaffWorkspaceApi.getStaffLeaveRequests(
        staffId: _staffId,
      );

      if (!mounted) return;

      if ((response['status'] ?? 0) == 1) {
        final requests =
            (response['leave_requests'] as List<dynamic>?)
                ?.map((r) => Map<String, dynamic>.from(r as Map))
                .toList() ??
            [];

        setState(() {
          _leaveRequests = requests;
          _isLoading = false;
        });
      } else {
        setState(() {
          _error =
              response['message']?.toString() ??
              'Failed to load leave requests';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Error loading leave requests: $e';
        _isLoading = false;
      });
    }
  }

  Color _getStatusColor(String status) {
    final s = status.toLowerCase();
    if (s == 'approve') return Colors.green;
    if (s == 'disapprove') return Colors.red;
    return Colors.orange; // pending
  }

  String _getStatusLabel(String status) {
    final s = status.toLowerCase();
    if (s == 'approve') return 'Approved';
    if (s == 'disapprove') return 'Disapproved';
    return 'Pending';
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return 'N/A';
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('dd MMM yyyy').format(date);
    } catch (_) {
      return dateStr;
    }
  }

  Widget _buildLeaveRequestCard(Map<String, dynamic> request) {
    final leaveType = request['type']?.toString() ?? 'Unknown';
    final fromDate = _formatDate(request['leave_from']?.toString());
    final toDate = _formatDate(request['leave_to']?.toString());
    final status = request['status']?.toString() ?? 'pending';
    final days = request['leave_days']?.toString() ?? '0';
    final reason = request['reason']?.toString() ?? '';

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        leaveType,
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '$days ${int.parse(days) == 1 ? 'day' : 'days'}',
                        style: TextStyle(fontSize: 13, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(status).withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    _getStatusLabel(status),
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: _getStatusColor(status),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                const Icon(Icons.calendar_today, size: 16, color: Colors.grey),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    '$fromDate to $toDate',
                    style: const TextStyle(fontSize: 13),
                  ),
                ),
              ],
            ),
            if (reason.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                'Reason: $reason',
                style: TextStyle(fontSize: 13, color: Colors.grey[700]),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Leave Requests'),
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
                    onPressed: _loadLeaveRequests,
                    child: const Text('Retry'),
                  ),
                ],
              ),
            )
          : _leaveRequests.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.assignment_ind_outlined,
                    size: 64,
                    color: Colors.grey.shade400,
                  ),
                  const SizedBox(height: 16),
                  const Text(
                    'No leave requests yet',
                    style: TextStyle(fontSize: 16),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadLeaveRequests,
              child: ListView(
                children: [
                  Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Total Requests: ${_leaveRequests.length}',
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                            color: Colors.grey,
                          ),
                        ),
                      ],
                    ),
                  ),
                  ..._leaveRequests.map(
                    (request) => _buildLeaveRequestCard(request),
                  ),
                  const SizedBox(height: 16),
                ],
              ),
            ),
    );
  }
}
