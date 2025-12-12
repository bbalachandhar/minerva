import 'package:equatable/equatable.dart';

class CalendarTodoEntity extends Equatable {
  const CalendarTodoEntity({
    required this.id,
    required this.taskName,
    required this.taskDate,
    required this.isCompleted,
  });

  final String id;
  final String taskName;
  final String taskDate;
  final bool isCompleted;

  @override
  List<Object?> get props => [
        id,
        taskName,
        taskDate,
        isCompleted,
      ];
}
