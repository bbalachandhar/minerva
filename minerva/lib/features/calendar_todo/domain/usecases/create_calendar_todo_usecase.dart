import 'package:minerva_flutter/features/calendar_todo/domain/entities/calendar_todo_entity.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/repositories/calendar_todo_repository.dart';

class CreateCalendarTodoUseCase {
  final CalendarTodoRepository repository;

  CreateCalendarTodoUseCase({required this.repository});

  Future<void> call(CalendarTodoEntity task) async {
    return await repository.createTask(task);
  }
}
