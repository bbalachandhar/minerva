// minerva/lib/features/apply_leave/domain/usecases/edit_leave_application_usecase.dart
import 'dart:io';
import 'package:minerva_flutter/features/apply_leave/domain/repositories/leave_repository.dart';

class EditLeaveApplicationUseCase {
  final LeaveRepository repository;

  EditLeaveApplicationUseCase({required this.repository});

  Future<void> call({
    required String leaveId,
    required String fromDate,
    required String toDate,
    required String reason,
    required String applyDate,
    File? document,
  }) async {
    return await repository.editLeaveApplication(
      leaveId: leaveId,
      fromDate: fromDate,
      toDate: toDate,
      reason: reason,
      applyDate: applyDate,
      document: document,
    );
  }
}
