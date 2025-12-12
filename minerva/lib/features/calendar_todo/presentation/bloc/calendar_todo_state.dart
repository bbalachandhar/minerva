part of 'calendar_todo_bloc.dart';

abstract class CalendarTodoState extends Equatable {
  const CalendarTodoState();

  @override
  List<Object> get props => [];
}

class CalendarTodoInitial extends CalendarTodoState {}

class CalendarTodoLoading extends CalendarTodoState {}

class CalendarTodoLoaded extends CalendarTodoState {
  final List<CalendarTodoEntity> tasks;

  const CalendarTodoLoaded({required this.tasks});

  @override
  List<Object> get props => [tasks];
}

class CalendarTodoError extends CalendarTodoState {
  final String message;

  const CalendarTodoError({required this.message});

  @override
  List<Object> get props => [message];
}

class CalendarTodoActionSuccess extends CalendarTodoState {
  final String message;

  const CalendarTodoActionSuccess({required this.message});

  @override
  List<Object> get props => [message];
}