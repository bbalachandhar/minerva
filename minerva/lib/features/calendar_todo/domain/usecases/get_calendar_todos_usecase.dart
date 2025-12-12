import 'package:minerva_flutter/features/calendar_todo/domain/entities/calendar_todo_entity.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/repositories/calendar_todo_repository.dart';

class GetCalendarTodosUseCase {
  final CalendarTodoRepository repository;

  GetCalendarTodosUseCase({required this.repository});

  Future<List<CalendarTodoEntity>> call({String? date}) async {
    return await repository.getTasks(date: date);
  }
}
