// minerva/lib/features/apply_leave/presentation/bloc/apply_leave_event.dart
import 'package:equatable/equatable.dart';

abstract class ApplyLeaveEvent extends Equatable {
  const ApplyLeaveEvent();

  @override
  List<Object> get props => [];
}

class FetchLeaveApplications extends ApplyLeaveEvent {
  const FetchLeaveApplications();
}

class DeleteLeaveApplication extends ApplyLeaveEvent {
  final String leaveId;

  const DeleteLeaveApplication({required this.leaveId});

  @override
  List<Object> get props => [leaveId];
}

// Event for adding/submitting a new leave application
class SubmitLeaveApplication extends ApplyLeaveEvent {
  final String fromDate;
  final String toDate;
  final String reason;
  final String applyDate;
  final String? documentPath; // Optional document attachment

  const SubmitLeaveApplication({
    required this.fromDate,
    required this.toDate,
    required this.reason,
    required this.applyDate,
    this.documentPath,
  });

  @override
  List<Object> get props => [fromDate, toDate, reason, applyDate, documentPath ?? ''];
}

// Event for editing an existing leave application
class EditLeaveApplication extends ApplyLeaveEvent {
  final String leaveId;
  final String fromDate;
  final String toDate;
  final String reason;
  final String applyDate;
  final String? documentPath; // Optional document attachment

  const EditLeaveApplication({
    required this.leaveId,
    required this.fromDate,
    required this.toDate,
    required this.reason,
    required this.applyDate,
    this.documentPath,
  });

  @override
  List<Object> get props => [leaveId, fromDate, toDate, reason, applyDate, documentPath ?? ''];
}
