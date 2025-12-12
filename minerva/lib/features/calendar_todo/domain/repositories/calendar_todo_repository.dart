import 'package:minerva_flutter/features/calendar_todo/domain/entities/calendar_todo_entity.dart';

abstract class CalendarTodoRepository {
  Future<List<CalendarTodoEntity>> getTasks({String? date});
  Future<void> createTask(CalendarTodoEntity task);
  Future<void> updateTask(CalendarTodoEntity task);
  Future<void> deleteTask(String id);
  Future<void> markTaskAsComplete(String id, bool isCompleted);
}
