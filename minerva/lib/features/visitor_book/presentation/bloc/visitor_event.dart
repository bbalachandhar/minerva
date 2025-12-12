// minerva/lib/features/visitor_book/presentation/bloc/visitor_event.dart

import 'package:equatable/equatable.dart';

abstract class VisitorEvent extends Equatable {
  const VisitorEvent();

  @override
  List<Object> get props => [];
}

class FetchVisitors extends VisitorEvent {
  final String studentId;

  const FetchVisitors({required this.studentId});

  @override
  List<Object> get props => [studentId];
}

class VisitorErrorOccurred extends VisitorEvent {
  final String message;

  const VisitorErrorOccurred({required this.message});

  @override
  List<Object> get props => [message];
}
