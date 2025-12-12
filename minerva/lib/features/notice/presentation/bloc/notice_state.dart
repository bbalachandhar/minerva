// minerva/lib/features/notice/presentation/bloc/notice_state.dart
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/notice/domain/entities/notice_entity.dart';

abstract class NoticeState extends Equatable {
  const NoticeState();

  @override
  List<Object> get props => [];
}

class NoticeInitial extends NoticeState {}

class NoticeLoading extends NoticeState {}

class NoticeLoaded extends NoticeState {
  final List<NoticeEntity> notices;

  const NoticeLoaded({required this.notices});

  @override
  List<Object> get props => [notices];
}

class NoticeError extends NoticeState {
  final String message;

  const NoticeError({required this.message});

  @override
  List<Object> get props => [message];
}
