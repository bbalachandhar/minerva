// minerva/lib/features/apply_leave/domain/usecases/get_leave_applications_usecase.dart
import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';
import 'package:minerva_flutter/features/apply_leave/domain/repositories/leave_repository.dart';

class GetLeaveApplicationsUseCase {
  final LeaveRepository repository;

  GetLeaveApplicationsUseCase({required this.repository});

  Future<List<LeaveApplicationEntity>> call() async {
    return await repository.getLeaveApplications();
  }
}
