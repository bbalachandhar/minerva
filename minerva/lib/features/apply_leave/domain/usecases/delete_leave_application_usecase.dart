// minerva/lib/features/apply_leave/domain/usecases/delete_leave_application_usecase.dart
import 'package:minerva_flutter/features/apply_leave/domain/repositories/leave_repository.dart';

class DeleteLeaveApplicationUseCase {
  final LeaveRepository repository;

  DeleteLeaveApplicationUseCase({required this.repository});

  Future<void> call(String leaveId) async {
    return await repository.deleteLeaveApplication(leaveId);
  }
}
