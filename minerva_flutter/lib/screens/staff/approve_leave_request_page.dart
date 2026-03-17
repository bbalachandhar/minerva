import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../models/staff_leave_request.dart';
import '../../providers/leave_request_provider.dart';
import '../../services/api/staff_workspace_api.dart';

class ApproveLeaveRequestPage extends StatefulWidget {
  const ApproveLeaveRequestPage({Key? key}) : super(key: key);

  @override
  State<ApproveLeaveRequestPage> createState() =>
      _ApproveLeaveRequestPageState();
}

class _ApproveLeaveRequestPageState extends State<ApproveLeaveRequestPage> {
  final DateFormat dateFormat = DateFormat('dd MMM yyyy');
  int _staffId = 0;
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _loadInitialData();
  }

  Future<void> _loadInitialData() async {
    final prefs = await SharedPreferences.getInstance();
    int staffId = prefs.getInt('staff_id') ?? 0;
    if (staffId <= 0) {
      final staffIdStr = prefs.getString('staff_id') ?? '';
      staffId = int.tryParse(staffIdStr) ?? 0;
    }

    final profile = await StaffWorkspaceApi.getStaffProfile();
    if ((profile['status'] ?? 0).toString() == '1' &&
        profile['staff_result'] is Map) {
      final profileStaffId = int.tryParse(
        (profile['staff_result']['staff_id'] ?? '').toString(),
      );
      if ((profileStaffId ?? 0) > 0) {
        staffId = profileStaffId!;
      }
    }

    if (mounted) {
      setState(() {
        _staffId = staffId;
        _isLoading = true;
      });

      if (staffId > 0) {
        final provider = context.read<LeaveRequestProvider>();
        await provider.loadLeaveRequests(staffId);
      }

      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  void _loadLeaveRequests() {
    if (_staffId > 0) {
      final provider = context.read<LeaveRequestProvider>();
      provider.loadLeaveRequests(_staffId);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Approve Leave Requests'), elevation: 0),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : Consumer<LeaveRequestProvider>(
              builder: (context, leaveProvider, _) {
                if (leaveProvider.error != null) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.error_outline,
                          size: 48,
                          color: Colors.red,
                        ),
                        const SizedBox(height: 16),
                        Text('Error: ${leaveProvider.error}'),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _loadLeaveRequests,
                          child: const Text('Retry'),
                        ),
                      ],
                    ),
                  );
                }

                final pendingRequests = leaveProvider.leaveRequests
                    .where(
                      (req) =>
                          req.approverStatus.toLowerCase() == 'pending' &&
                          (req.recommenderStatus.toLowerCase() ==
                                  'recommended' ||
                              req.recommenderStatus.toLowerCase() ==
                                  'approved'),
                    )
                    .toList();

                if (pendingRequests.isEmpty) {
                  return Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.check_circle,
                          size: 48,
                          color: Colors.green.shade300,
                        ),
                        const SizedBox(height: 16),
                        const Text(
                          'No pending leave requests to approve',
                          style: TextStyle(color: Colors.grey),
                        ),
                      ],
                    ),
                  );
                }

                return ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: pendingRequests.length,
                  itemBuilder: (context, index) {
                    final request = pendingRequests[index];
                    return _buildLeaveCard(request);
                  },
                );
              },
            ),
    );
  }

  Widget _buildLeaveCard(StaffLeaveRequest request) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
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
                        'Requester: ${request.getFullRequesterName()}',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        '${dateFormat.format(request.leaveFrom)} - ${dateFormat.format(request.leaveTo)}',
                        style: const TextStyle(
                          fontSize: 14,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
                Chip(
                  label: const Text('Pending'),
                  backgroundColor: Colors.orange.withOpacity(0.2),
                  labelStyle: const TextStyle(color: Colors.orange),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${request.leaveDays.toInt()} days',
                  style: const TextStyle(fontSize: 12, color: Colors.grey),
                ),
                if (request.recommenderName != null)
                  Text(
                    'Recommended by: ${request.getFullRecommenderName()}',
                    style: const TextStyle(
                      fontSize: 11,
                      color: Colors.green,
                      fontStyle: FontStyle.italic,
                    ),
                  ),
              ],
            ),
            if (request.reason.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text(
                'Reason: ${request.reason}',
                style: const TextStyle(fontSize: 12),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ],
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                ElevatedButton.icon(
                  onPressed: () {
                    _showApproveDialog(request, false);
                  },
                  icon: const Icon(Icons.close, size: 16),
                  label: const Text('Reject'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.red,
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton.icon(
                  onPressed: () {
                    _showApproveDialog(request, true);
                  },
                  icon: const Icon(Icons.check, size: 16),
                  label: const Text('Approve'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showApproveDialog(StaffLeaveRequest request, bool isApprove) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          isApprove ? 'Approve Leave Request' : 'Reject Leave Request',
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Leave Type: ${request.type}',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8),
            Text('Days: ${request.leaveDays.toInt()}'),
            const SizedBox(height: 8),
            Text('Reason: ${request.reason}'),
            const SizedBox(height: 8),
            Text(
              'Recommended by: ${request.getFullRecommenderName()}',
              style: const TextStyle(fontStyle: FontStyle.italic),
            ),
            const SizedBox(height: 16),
            Text(
              isApprove
                  ? 'Do you want to approve this leave request?'
                  : 'Do you want to reject this leave request?',
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    isApprove
                        ? 'Leave request approved successfully'
                        : 'Leave request rejected successfully',
                  ),
                ),
              );
              // TODO: Call API to update approver_status
            },
            child: Text(
              isApprove ? 'Approve' : 'Reject',
              style: TextStyle(color: isApprove ? Colors.green : Colors.red),
            ),
          ),
        ],
      ),
    );
  }
}
