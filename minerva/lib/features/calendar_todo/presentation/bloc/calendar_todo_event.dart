part of 'calendar_todo_bloc.dart';

abstract class CalendarTodoEvent extends Equatable {
  const CalendarTodoEvent();

  @override
  List<Object> get props => [];
}

class FetchCalendarTodosEvent extends CalendarTodoEvent {
  final String? date;

  const FetchCalendarTodosEvent({this.date});

  @override
  List<Object> get props => [date ?? ''];
}

class CreateCalendarTodoEvent extends CalendarTodoEvent {
  final CalendarTodoEntity task;

  const CreateCalendarTodoEvent({required this.task});

  @override
  List<Object> get props => [task];
}

class UpdateCalendarTodoEvent extends CalendarTodoEvent {
  final CalendarTodoEntity task;

  const UpdateCalendarTodoEvent({required this.task});

  @override
  List<Object> get props => [task];
}

class DeleteCalendarTodoEvent extends CalendarTodoEvent {
  final String id;

  const DeleteCalendarTodoEvent({required this.id});

  @override
  List<Object> get props => [id];
}

class MarkCalendarTodoAsCompleteEvent extends CalendarTodoEvent {
  final String id;
  final bool isCompleted;

  const MarkCalendarTodoAsCompleteEvent({required this.id, required this.isCompleted});

  @override
  List<Object> get props => [id, isCompleted];
}
