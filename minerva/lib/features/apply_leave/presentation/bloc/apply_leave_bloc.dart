// minerva/lib/features/apply_leave/presentation/bloc/apply_leave_bloc.dart
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/apply_leave/domain/usecases/get_leave_applications_usecase.dart';
import 'package:minerva_flutter/features/apply_leave/domain/usecases/delete_leave_application_usecase.dart';
import 'package:minerva_flutter/features/apply_leave/domain/usecases/submit_leave_application_usecase.dart'; // New import
import 'package:minerva_flutter/features/apply_leave/domain/usecases/edit_leave_application_usecase.dart'; // New import
import 'dart:io'; // Import for File

import 'apply_leave_event.dart';
import 'apply_leave_state.dart';

class ApplyLeaveBloc extends Bloc<ApplyLeaveEvent, ApplyLeaveState> {
  final GetLeaveApplicationsUseCase getLeaveApplicationsUseCase;
  final DeleteLeaveApplicationUseCase deleteLeaveApplicationUseCase;
  final SubmitLeaveApplicationUseCase submitLeaveApplicationUseCase; // New dependency
  final EditLeaveApplicationUseCase editLeaveApplicationUseCase; // New dependency

  ApplyLeaveBloc({
    required this.getLeaveApplicationsUseCase,
    required this.deleteLeaveApplicationUseCase,
    required this.submitLeaveApplicationUseCase, // New dependency
    required this.editLeaveApplicationUseCase, // New dependency
  }) : super(ApplyLeaveInitial()) {
    on<FetchLeaveApplications>(_onFetchLeaveApplications);
    on<DeleteLeaveApplication>(_onDeleteLeaveApplication);
    on<SubmitLeaveApplication>(_onSubmitLeaveApplication); // New handler
    on<EditLeaveApplication>(_onEditLeaveApplication); // New handler
  }

  Future<void> _onFetchLeaveApplications(
    FetchLeaveApplications event,
    Emitter<ApplyLeaveState> emit,
  ) async {
    emit(ApplyLeaveLoading());
    try {
      final leaveApplications = await getLeaveApplicationsUseCase();
      emit(LeaveApplicationsLoaded(leaveApplications: leaveApplications));
    } catch (e) {
      emit(ApplyLeaveError(message: e.toString()));
    }
  }

  Future<void> _onDeleteLeaveApplication(
    DeleteLeaveApplication event,
    Emitter<ApplyLeaveState> emit,
  ) async {
    emit(ApplyLeaveLoading()); // Show loading while deleting
    try {
      await deleteLeaveApplicationUseCase(event.leaveId);
      emit(const ApplyLeaveOperationSuccess(message: 'Leave application deleted successfully.'));
      add(const FetchLeaveApplications()); // Refresh the list after deletion
    } catch (e) {
      emit(ApplyLeaveError(message: 'Failed to delete leave application: ${e.toString()}'));
      // After error, try to reload the list to show current state
      add(const FetchLeaveApplications());
    }
  }

  Future<void> _onSubmitLeaveApplication(
    SubmitLeaveApplication event,
    Emitter<ApplyLeaveState> emit,
  ) async {
    emit(ApplyLeaveLoading()); // Show loading while submitting
    try {
      await submitLeaveApplicationUseCase(
        fromDate: event.fromDate,
        toDate: event.toDate,
        reason: event.reason,
        applyDate: event.applyDate,
        document: event.documentPath != null ? File(event.documentPath!) : null,
      );
      emit(const ApplyLeaveOperationSuccess(message: 'Leave application submitted successfully.'));
      add(const FetchLeaveApplications()); // Refresh the list after submission
    } catch (e) {
      emit(ApplyLeaveError(message: 'Failed to submit leave application: ${e.toString()}'));
      add(const FetchLeaveApplications()); // Try to reload the list
    }
  }

  Future<void> _onEditLeaveApplication(
    EditLeaveApplication event,
    Emitter<ApplyLeaveState> emit,
  ) async {
    emit(ApplyLeaveLoading()); // Show loading while editing
    try {
      await editLeaveApplicationUseCase(
        leaveId: event.leaveId,
        fromDate: event.fromDate,
        toDate: event.toDate,
        reason: event.reason,
        applyDate: event.applyDate,
        document: event.documentPath != null ? File(event.documentPath!) : null,
      );
      emit(const ApplyLeaveOperationSuccess(message: 'Leave application updated successfully.'));
      add(const FetchLeaveApplications()); // Refresh the list after update
    } catch (e) {
      emit(ApplyLeaveError(message: 'Failed to update leave application: ${e.toString()}'));
      add(const FetchLeaveApplications()); // Try to reload the list
    }
  }
}
