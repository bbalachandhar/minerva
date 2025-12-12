// minerva/lib/features/apply_leave/presentation/bloc/apply_leave_state.dart
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';

abstract class ApplyLeaveState extends Equatable {
  const ApplyLeaveState();

  @override
  List<Object> get props => [];
}

class ApplyLeaveInitial extends ApplyLeaveState {}

class ApplyLeaveLoading extends ApplyLeaveState {}

class LeaveApplicationsLoaded extends ApplyLeaveState {
  final List<LeaveApplicationEntity> leaveApplications;

  const LeaveApplicationsLoaded({required this.leaveApplications});

  @override
  List<Object> get props => [leaveApplications];
}

class ApplyLeaveOperationSuccess extends ApplyLeaveState {
  final String message;

  const ApplyLeaveOperationSuccess({required this.message});

  @override
  List<Object> get props => [message];
}

class ApplyLeaveError extends ApplyLeaveState {
  final String message;

  const ApplyLeaveError({required this.message});

  @override
  List<Object> get props => [message];
}
