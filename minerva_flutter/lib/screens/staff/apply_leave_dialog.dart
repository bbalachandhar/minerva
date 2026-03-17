import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../models/staff_leave_request.dart';
import '../../providers/leave_request_provider.dart';

class ApplyLeaveDialog extends StatefulWidget {
  final int staffId;
  final StaffLeaveRequest? initialRequest;
  final Function()? onSaved;

  const ApplyLeaveDialog({
    Key? key,
    required this.staffId,
    this.initialRequest,
    this.onSaved,
  }) : super(key: key);

  @override
  State<ApplyLeaveDialog> createState() => _ApplyLeaveDialogState();
}

class _ApplyLeaveDialogState extends State<ApplyLeaveDialog> {
  late DateTime leaveFromDate;
  late DateTime leaveToDate;
  late TextEditingController reasonController;
  int? selectedLeaveTypeId;
  bool isHalfDay = false;
  bool isLoading = false;

  final dateFormat = DateFormat('dd MMM yyyy');

  int _parseLeaveTypeId(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  @override
  void initState() {
    super.initState();
    leaveFromDate = widget.initialRequest?.leaveFrom ?? DateTime.now();
    leaveToDate = widget.initialRequest?.leaveTo ?? DateTime.now();
    reasonController = TextEditingController(
      text: widget.initialRequest?.reason ?? '',
    );
    selectedLeaveTypeId = widget.initialRequest?.leaveTypeId;
    isHalfDay = (widget.initialRequest?.leaveDays ?? 1) == 0.5;

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) {
        context.read<LeaveRequestProvider>().loadLeaveTypes(widget.staffId);
      }
    });
  }

  @override
  void dispose() {
    reasonController.dispose();
    super.dispose();
  }

  void _selectFromDate(BuildContext context) async {
    final now = DateTime.now();
    final firstDate = DateTime(now.year - 5, 1, 1);
    final lastDate = DateTime(now.year + 5, 12, 31);
    final selectedDate = await showDatePicker(
      context: context,
      initialDate: leaveFromDate,
      firstDate: firstDate,
      lastDate: lastDate,
    );

    if (selectedDate != null) {
      setState(() {
        leaveFromDate = selectedDate;
        if (isHalfDay || leaveToDate.isBefore(leaveFromDate)) {
          leaveToDate = leaveFromDate;
        }
      });
    }
  }

  void _selectToDate(BuildContext context) async {
    if (isHalfDay) {
      return;
    }

    final now = DateTime.now();
    final lastDate = DateTime(now.year + 5, 12, 31);
    final selectedDate = await showDatePicker(
      context: context,
      initialDate: leaveToDate,
      firstDate: leaveFromDate,
      lastDate: lastDate,
    );

    if (selectedDate != null) {
      setState(() {
        leaveToDate = selectedDate;
      });
    }
  }

  DateTime _dateOnly(DateTime value) {
    return DateTime(value.year, value.month, value.day);
  }

  double _calculateLeaveDays() {
    if (isHalfDay) {
      return 0.5;
    }

    final fromDate = _dateOnly(leaveFromDate);
    final toDate = _dateOnly(leaveToDate);
    return toDate.difference(fromDate).inDays + 1;
  }

  void _submitForm() async {
    if (selectedLeaveTypeId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a leave type')),
      );
      return;
    }

    if (reasonController.text.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Please enter a reason')));
      return;
    }

    setState(() => isLoading = true);

    try {
      final provider = context.read<LeaveRequestProvider>();
      bool success;

      if (widget.initialRequest != null) {
        success = await provider.updateLeaveRequest(
          leaveId: widget.initialRequest!.id,
          leaveTypeId: selectedLeaveTypeId!,
          leaveFrom: leaveFromDate,
          leaveTo: leaveToDate,
          reason: reasonController.text,
          isHalfDay: isHalfDay,
        );
      } else {
        success = await provider.addLeaveRequest(
          leaveTypeId: selectedLeaveTypeId!,
          leaveFrom: leaveFromDate,
          leaveTo: leaveToDate,
          reason: reasonController.text,
          isHalfDay: isHalfDay,
        );
      }

      if (mounted) {
        if (success) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                widget.initialRequest != null
                    ? 'Leave request updated successfully'
                    : 'Leave request submitted successfully',
              ),
            ),
          );
          Navigator.pop(context);
          widget.onSaved?.call();
        } else {
          final errorMsg = provider.error ?? 'Failed to save leave request';
          ScaffoldMessenger.of(
            context,
          ).showSnackBar(SnackBar(content: Text(errorMsg)));
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    } finally {
      if (mounted) {
        setState(() => isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                widget.initialRequest != null
                    ? 'Edit Leave Request'
                    : 'Apply for Leave',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 20),

              Consumer<LeaveRequestProvider>(
                builder: (context, leaveProvider, _) {
                  final leaveTypes = leaveProvider.leaveTypes;
                  final hasSelectedType = leaveTypes.any(
                    (leaveType) =>
                        _parseLeaveTypeId(leaveType['leave_type_id']) ==
                        selectedLeaveTypeId,
                  );

                  if (leaveProvider.isLoadingLeaveTypes) {
                    return const SizedBox(
                      height: 56,
                      child: Center(child: CircularProgressIndicator()),
                    );
                  }

                  if (leaveTypes.isEmpty) {
                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Leave Type',
                          style: TextStyle(fontSize: 12, color: Colors.grey),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          leaveProvider.error ??
                              'No leave types available. Please contact admin.',
                          style: const TextStyle(color: Colors.red),
                        ),
                        const SizedBox(height: 8),
                        OutlinedButton(
                          onPressed: () {
                            context.read<LeaveRequestProvider>().loadLeaveTypes(
                              widget.staffId,
                            );
                          },
                          child: const Text('Retry'),
                        ),
                      ],
                    );
                  }

                  return DropdownButtonFormField<int>(
                    initialValue: hasSelectedType ? selectedLeaveTypeId : null,
                    decoration: const InputDecoration(
                      labelText: 'Leave Type',
                      border: OutlineInputBorder(),
                    ),
                    isExpanded: true,
                    hint: const Text('Select leave type'),
                    items: leaveTypes.map((leaveType) {
                      final id = _parseLeaveTypeId(leaveType['leave_type_id']);
                      final typeName = (leaveType['type'] ?? 'Unknown')
                          .toString();
                      final requiresBalanceCheckRaw =
                          (leaveType['requires_balance_check'] ?? 1)
                              .toString()
                              .trim();
                      final requiresBalanceCheck =
                          requiresBalanceCheckRaw == '1' ||
                          requiresBalanceCheckRaw.toLowerCase() == 'yes' ||
                          requiresBalanceCheckRaw.toLowerCase() == 'true';
                      final remaining =
                          (leaveType['remaining'] ??
                                  leaveType['remaining_leave'] ??
                                  leaveType['leave_remaining'] ??
                                  leaveType['alloted_leave'] ??
                                  0)
                              .toString();
                      return DropdownMenuItem<int>(
                        value: id,
                        child: Text(
                          requiresBalanceCheck
                              ? '$typeName ($remaining left)'
                              : typeName,
                        ),
                      );
                    }).toList(),
                    onChanged: (value) {
                      setState(() {
                        selectedLeaveTypeId = value;
                      });
                    },
                  );
                },
              ),
              const SizedBox(height: 16),

              SwitchListTile(
                contentPadding: EdgeInsets.zero,
                title: const Text('Apply for half day'),
                value: isHalfDay,
                onChanged: (value) {
                  setState(() {
                    isHalfDay = value;
                    if (isHalfDay) {
                      leaveToDate = leaveFromDate;
                    }
                  });
                },
              ),
              const SizedBox(height: 8),

              // From Date picker
              GestureDetector(
                onTap: () => _selectFromDate(context),
                child: InputDecorator(
                  decoration: const InputDecoration(
                    labelText: 'From Date',
                    border: OutlineInputBorder(),
                  ),
                  child: Text(dateFormat.format(leaveFromDate)),
                ),
              ),
              const SizedBox(height: 16),

              // To Date picker
              GestureDetector(
                onTap: isHalfDay ? null : () => _selectToDate(context),
                child: InputDecorator(
                  decoration: InputDecoration(
                    labelText: 'To Date',
                    border: OutlineInputBorder(),
                    enabled: !isHalfDay,
                  ),
                  child: Text(dateFormat.format(leaveToDate)),
                ),
              ),
              const SizedBox(height: 16),

              // Leave Days display
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Total Leave Days:',
                      style: TextStyle(fontWeight: FontWeight.w500),
                    ),
                    Text(
                      _calculateLeaveDays().toStringAsFixed(
                        _calculateLeaveDays() % 1 == 0 ? 0 : 1,
                      ),
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: Colors.blue,
                        fontSize: 16,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Reason textarea
              TextField(
                controller: reasonController,
                decoration: const InputDecoration(
                  labelText: 'Reason for Leave',
                  border: OutlineInputBorder(),
                  hintText: 'Enter your reason for leave',
                ),
                maxLines: 4,
                minLines: 2,
              ),
              const SizedBox(height: 20),

              // Action buttons
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton(
                    onPressed: isLoading ? null : () => Navigator.pop(context),
                    child: const Text('Cancel'),
                  ),
                  const SizedBox(width: 8),
                  ElevatedButton(
                    onPressed: isLoading ? null : _submitForm,
                    child: isLoading
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Text(
                            widget.initialRequest != null ? 'Update' : 'Submit',
                          ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
