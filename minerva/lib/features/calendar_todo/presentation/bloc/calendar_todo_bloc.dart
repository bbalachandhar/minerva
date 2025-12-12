import 'dart:async';
import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/entities/calendar_todo_entity.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/create_calendar_todo_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/delete_calendar_todo_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/get_calendar_todos_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/mark_calendar_todo_as_complete_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/update_calendar_todo_usecase.dart';
import 'dart:developer';

part 'calendar_todo_event.dart';
part 'calendar_todo_state.dart';

class CalendarTodoBloc extends Bloc<CalendarTodoEvent, CalendarTodoState> {
  final GetCalendarTodosUseCase getCalendarTodosUseCase;
  final CreateCalendarTodoUseCase createCalendarTodoUseCase;
  final UpdateCalendarTodoUseCase updateCalendarTodoUseCase;
  final DeleteCalendarTodoUseCase deleteCalendarTodoUseCase;
  final MarkCalendarTodoAsCompleteUseCase markCalendarTodoAsCompleteUseCase;

  CalendarTodoBloc({
    required this.getCalendarTodosUseCase,
    required this.createCalendarTodoUseCase,
    required this.updateCalendarTodoUseCase,
    required this.deleteCalendarTodoUseCase,
    required this.markCalendarTodoAsCompleteUseCase,
  }) : super(CalendarTodoInitial()) {
    on<FetchCalendarTodosEvent>(_onFetchCalendarTodos);
    on<CreateCalendarTodoEvent>(_onCreateCalendarTodo);
    on<UpdateCalendarTodoEvent>(_onUpdateCalendarTodo);
    on<DeleteCalendarTodoEvent>(_onDeleteCalendarTodo);
    on<MarkCalendarTodoAsCompleteEvent>(_onMarkCalendarTodoAsComplete);
  }

  Future<void> _onFetchCalendarTodos(
    FetchCalendarTodosEvent event,
    Emitter<CalendarTodoState> emit,
  ) async {
    emit(CalendarTodoLoading());
    try {
      final tasks = await getCalendarTodosUseCase(date: event.date);
      emit(CalendarTodoLoaded(tasks: tasks));
    } catch (e) {
      log('Error fetching calendar todos: $e');
      emit(CalendarTodoError(message: e.toString()));
    }
  }

  Future<void> _onCreateCalendarTodo(
    CreateCalendarTodoEvent event,
    Emitter<CalendarTodoState> emit,
  ) async {
    try {
      await createCalendarTodoUseCase(event.task);
      emit(const CalendarTodoActionSuccess(message: 'Task created successfully'));
      add(FetchCalendarTodosEvent(date: event.task.taskDate)); // Refresh tasks for the current date
    } catch (e) {
      log('Error creating calendar todo: $e');
      emit(CalendarTodoError(message: e.toString()));
    }
  }

  Future<void> _onUpdateCalendarTodo(
    UpdateCalendarTodoEvent event,
    Emitter<CalendarTodoState> emit,
  ) async {
    try {
      await updateCalendarTodoUseCase(event.task);
      emit(const CalendarTodoActionSuccess(message: 'Task updated successfully'));
      add(FetchCalendarTodosEvent(date: event.task.taskDate)); // Refresh tasks for the current date
    } catch (e) {
      log('Error updating calendar todo: $e');
      emit(CalendarTodoError(message: e.toString()));
    }
  }

  Future<void> _onDeleteCalendarTodo(
    DeleteCalendarTodoEvent event,
    Emitter<CalendarTodoState> emit,
  ) async {
    try {
      await deleteCalendarTodoUseCase(event.id);
      emit(const CalendarTodoActionSuccess(message: 'Task deleted successfully'));
      // No date provided for refresh, so it might fetch all tasks or rely on a generic refresh.
      // For now, assuming current loaded date if available, or just re-fetch all.
      add(const FetchCalendarTodosEvent()); 
    } catch (e) {
      log('Error deleting calendar todo: $e');
      emit(CalendarTodoError(message: e.toString()));
    }
  }

  Future<void> _onMarkCalendarTodoAsComplete(
    MarkCalendarTodoAsCompleteEvent event,
    Emitter<CalendarTodoState> emit,
  ) async {
    try {
      await markCalendarTodoAsCompleteUseCase(event.id, event.isCompleted);
      emit(CalendarTodoActionSuccess(
          message: event.isCompleted
              ? 'Task marked as complete'
              : 'Task marked as incomplete'));
      // This will refetch all tasks, should be refined to refetch specific date if needed
      add(const FetchCalendarTodosEvent()); 
    } catch (e) {
      log('Error marking calendar todo as complete: $e');
      emit(CalendarTodoError(message: e.toString()));
    }
  }
}
