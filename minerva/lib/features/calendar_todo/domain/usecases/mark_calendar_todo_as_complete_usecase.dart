import 'package:minerva_flutter/features/calendar_todo/domain/repositories/calendar_todo_repository.dart';

class MarkCalendarTodoAsCompleteUseCase {
  final CalendarTodoRepository repository;

  MarkCalendarTodoAsCompleteUseCase({required this.repository});

  Future<void> call(String id, bool isCompleted) async {
    return await repository.markTaskAsComplete(id, isCompleted);
  }
}
