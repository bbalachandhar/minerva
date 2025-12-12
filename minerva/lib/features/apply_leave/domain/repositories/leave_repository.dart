// minerva/lib/features/apply_leave/domain/repositories/leave_repository.dart
import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';
import 'dart:io'; // Import for File

abstract class LeaveRepository {
  Future<List<LeaveApplicationEntity>> getLeaveApplications();
  Future<void> deleteLeaveApplication(String leaveId);
  Future<void> submitLeaveApplication({
    required String fromDate,
    required String toDate,
    required String reason,
    required String applyDate,
    File? document, // Optional file attachment
  });
  Future<void> editLeaveApplication({
    required String leaveId,
    required String fromDate,
    required String toDate,
    required String reason,
    required String applyDate,
    File? document, // Optional file attachment
  });
}
