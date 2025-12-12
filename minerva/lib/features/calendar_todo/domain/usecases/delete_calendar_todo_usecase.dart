import 'package:minerva_flutter/features/calendar_todo/domain/repositories/calendar_todo_repository.dart';

class DeleteCalendarTodoUseCase {
  final CalendarTodoRepository repository;

  DeleteCalendarTodoUseCase({required this.repository});

  Future<void> call(String id) async {
    return await repository.deleteTask(id);
  }
}
