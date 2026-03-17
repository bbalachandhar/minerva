import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../models/staff_leave_request.dart';
import '../../providers/leave_request_provider.dart';
import '../../services/api/staff_workspace_api.dart';
import 'apply_leave_dialog.dart';

class MyLeaveRequestPage extends StatefulWidget {
  const MyLeaveRequestPage({Key? key}) : super(key: key);

  @override
  State<MyLeaveRequestPage> createState() => _MyLeaveRequestPageState();
}

class _MyLeaveRequestPageState extends State<MyLeaveRequestPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final DateFormat dateFormat = DateFormat('dd MMM yyyy');
  int _staffId = 0;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _loadInitialData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
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
      });

      if (staffId > 0) {
        final provider = context.read<LeaveRequestProvider>();
        await provider.loadLeaveRequests(staffId);
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
      appBar: AppBar(
        title: const Text('My Leave Requests'),
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Pending'),
            Tab(text: 'Approved'),
            Tab(text: 'Rejected'),
          ],
        ),
      ),
      body: Consumer<LeaveRequestProvider>(
        builder: (context, leaveProvider, _) {
          if (leaveProvider.isLoading) {
            return const Center(child: CircularProgressIndicator());
          }

          if (leaveProvider.error != null) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
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

          return TabBarView(
            controller: _tabController,
            children: [
              _buildLeaveList(
                leaveProvider.pendingRequests,
                'No pending leave requests',
              ),
              _buildLeaveList(
                leaveProvider.approvedRequests,
                'No approved leave requests',
              ),
              _buildLeaveList(
                leaveProvider.rejectedRequests,
                'No rejected leave requests',
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          showDialog(
            context: context,
            builder: (context) => ApplyLeaveDialog(
              staffId: _staffId,
              onSaved: () {
                _loadLeaveRequests();
              },
            ),
          );
        },
        child: const Icon(Icons.add),
      ),
    );
  }

  Widget _buildLeaveList(
    List<StaffLeaveRequest> filteredRequests,
    String emptyMessage,
  ) {
    if (filteredRequests.isEmpty) {
      return Center(
        child: Text(emptyMessage, style: const TextStyle(color: Colors.grey)),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: filteredRequests.length,
      itemBuilder: (context, index) {
        final request = filteredRequests[index];
        return _buildLeaveCard(request);
      },
    );
  }

  Widget _buildLeaveCard(StaffLeaveRequest request) {
    final statusColor = _getStatusColor(request.status);

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
                        request.type,
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
                  label: Text(request.getStatusDisplay()),
                  backgroundColor: statusColor.withOpacity(0.2),
                  labelStyle: TextStyle(color: statusColor),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              '${request.leaveDays.toInt()} days',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
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
            const SizedBox(height: 8),
            Text(
              'Recommender: ${request.getFullRecommenderName()}',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
            ),
            const SizedBox(height: 4),
            Text(
              'Approver: ${request.getFullApproverName()}',
              style: const TextStyle(fontSize: 12, color: Colors.grey),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    'Applied: ${dateFormat.format(request.date)}',
                    style: const TextStyle(fontSize: 11, color: Colors.grey),
                  ),
                ),
                if (request.status.toLowerCase() == 'pending') ...[
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    onPressed: () {
                      _showEditDialog(request);
                    },
                    icon: const Icon(Icons.edit, size: 16),
                    label: const Text('Edit'),
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                    ),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton.icon(
                    onPressed: () {
                      _showDeleteConfirmation(request);
                    },
                    icon: const Icon(Icons.delete, size: 16),
                    label: const Text('Delete'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.red,
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showEditDialog(StaffLeaveRequest request) {
    showDialog(
      context: context,
      builder: (context) => ApplyLeaveDialog(
        staffId: _staffId,
        initialRequest: request,
        onSaved: () {
          _loadLeaveRequests();
        },
      ),
    );
  }

  void _showDeleteConfirmation(StaffLeaveRequest request) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Delete Leave Request'),
        content: const Text(
          'Are you sure you want to delete this leave request?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              _deleteLeaveRequest(request);
            },
            child: const Text('Delete', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
  }

  void _deleteLeaveRequest(StaffLeaveRequest request) async {
    final provider = context.read<LeaveRequestProvider>();

    ScaffoldMessenger.of(
      context,
    ).showSnackBar(const SnackBar(content: Text('Deleting leave request...')));

    final success = await provider.deleteLeaveRequest(request.id);

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Leave request deleted successfully')),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Failed to delete leave request: ${provider.error ?? "Unknown error"}',
            ),
          ),
        );
      }
    }
  }

  Color _getStatusColor(String status) {
    final lowerStatus = status.toLowerCase();
    if (lowerStatus == 'pending') return Colors.orange;
    if (lowerStatus == 'approved') return Colors.green;
    return Colors.red;
  }
}
