// minerva/lib/features/apply_leave/presentation/widgets/apply_leave_list_item_widget.dart
import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:url_launcher/url_launcher.dart';

class ApplyLeaveListItemWidget extends StatefulWidget {
  final LeaveApplicationEntity leaveApplication;
  final Color primaryColour;
  final Color secondaryColour;
  final String imagesUrl;
  final Function(String leaveId) onDelete;
  final Function(LeaveApplicationEntity leaveApplication) onEdit;

  const ApplyLeaveListItemWidget({
    Key? key,
    required this.leaveApplication,
    required this.primaryColour,
    required this.secondaryColour,
    required this.imagesUrl,
    required this.onDelete,
    required this.onEdit,
  }) : super(key: key);

  @override
  State<ApplyLeaveListItemWidget> createState() => _ApplyLeaveListItemWidgetState();
}

class _ApplyLeaveListItemWidgetState extends State<ApplyLeaveListItemWidget> {
  Color _primaryColour = Colors.blue; // Default for internal use
  Color _secondaryColour = Colors.blue; // Default for internal use
  String _imagesUrl = '';

  @override
  void initState() {
    super.initState();
    _primaryColour = widget.primaryColour;
    _secondaryColour = widget.secondaryColour;
    _imagesUrl = widget.imagesUrl;
  }

  void _launchURL(String url) async {
    final uri = Uri.parse(url);
    if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
      throw 'Could not launch $url';
    }
  }

  @override
  Widget build(BuildContext context) {
    String statusText;
    Color statusBackgroundColor;
    Color statusBorderColor; // For the dynamic border on status

    switch (widget.leaveApplication.status) {
      case '0':
        statusText = 'Pending';
        statusBackgroundColor = Colors.yellow.shade700; // Yellow for pending
        statusBorderColor = Colors.yellow.shade900;
        break;
      case '1':
        statusText = 'Approved';
        statusBackgroundColor = Colors.green; // Green for approved
        statusBorderColor = Colors.green.shade700;
        break;
      case '2':
        statusText = 'Disapproved';
        statusBackgroundColor = Colors.red; // Red for disapproved
        statusBorderColor = Colors.red.shade700;
        break;
      default:
        statusText = 'Unknown';
        statusBackgroundColor = Colors.grey;
        statusBorderColor = Colors.grey.shade700;
        break;
    }

    // Append approve_date if status is Approved and date is present
    if (widget.leaveApplication.status == '1' && widget.leaveApplication.approveDate != null && widget.leaveApplication.approveDate!.isNotEmpty) {
      statusText = '$statusText (${widget.leaveApplication.approveDate})';
    }

    return Card(
      elevation: 5.0,
      margin: const EdgeInsets.all(10.0),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15.0)),
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          children: [
            // Header Section
            Container(
              padding: const EdgeInsets.symmetric(vertical: 5.0, horizontal: 10.0),
              decoration: BoxDecoration(
                color: _secondaryColour,
                borderRadius: BorderRadius.circular(10.0), // Slightly rounded corners for the header bar
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Applied Date - ${widget.leaveApplication.applyDate}',
                    style: TextStyle(
                      fontSize: 14.0,
                      fontWeight: FontWeight.bold,
                      color: _primaryColour,
                    ),
                  ),
                  Row(
                    children: [
                      if (widget.leaveApplication.docs != null && widget.leaveApplication.docs!.isNotEmpty)
                        IconButton(
                          icon: Icon(Icons.download, color: _primaryColour),
                          onPressed: () {
                          final fullDocsUrl = '${widget.imagesUrl}uploads/student_leavedocuments/${widget.leaveApplication.docs}';
                            _launchURL(fullDocsUrl);
                          },
                          tooltip: 'Download Document',
                        ),
                      if (widget.leaveApplication.status == '0') // Only show edit/delete for pending
                        IconButton(
                          icon: Icon(Icons.edit, color: _primaryColour),
                          onPressed: () => widget.onEdit(widget.leaveApplication),
                          tooltip: 'Edit Leave',
                        ),
                      if (widget.leaveApplication.status == '0') // Only show edit/delete for pending
                        IconButton(
                          icon: Icon(Icons.delete, color: _primaryColour),
                          onPressed: () {
                            showDialog(
                              context: context,
                              builder: (BuildContext context) {
                                return AlertDialog(
                                  title: const Text('Confirm Delete'),
                                  content: const Text('Are you sure you want to delete this leave application?'),
                                  actions: <Widget>[
                                    TextButton(
                                      child: const Text('No'),
                                      onPressed: () {
                                        Navigator.of(context).pop();
                                      },
                                    ),
                                    TextButton(
                                      child: const Text('Yes'),
                                      onPressed: () {
                                        widget.onDelete(widget.leaveApplication.id);
                                        Navigator.of(context).pop();
                                      },
                                    ),
                                  ],
                                );
                              },
                            );
                          },
                          tooltip: 'Delete Leave',
                        ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 10.0),
            // Details Section
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 10.0),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    flex: 2,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildDetailRow(
                          label: 'From Date:',
                          value: widget.leaveApplication.fromDate,
                          icon: Icons.date_range,
                        ),
                        _buildDetailRow(
                          label: 'To Date:',
                          value: widget.leaveApplication.toDate,
                          icon: Icons.date_range,
                        ),
                        _buildDetailRow(
                          label: 'Reason:',
                          value: widget.leaveApplication.reason,
                          icon: Icons.info_outline,
                        ),
                      ],
                    ),
                  ),
                  Expanded(
                    flex: 1,
                    child: Align(
                      alignment: Alignment.topRight,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
                        decoration: BoxDecoration(
                          color: statusBackgroundColor,
                          borderRadius: BorderRadius.circular(5.0),
                          border: Border.all(color: statusBorderColor, width: 1.0),
                        ),
                        child: Text(
                          statusText,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                            fontSize: 12.0,
                          ),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDetailRow({
    required String label,
    required String value,
    required IconData icon,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Icon(icon, size: 18.0, color: _primaryColour),
          const SizedBox(width: 8.0),
          Text(
            label,
            style: TextStyle(
              fontWeight: FontWeight.bold,
              fontSize: 14.0,
              color: _primaryColour,
            ),
          ),
          const SizedBox(width: 5.0),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14.0,
                color: Colors.black87,
              ),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}