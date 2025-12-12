import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/teachers_rating/data/models/teacher_model.dart';

abstract class TeachersRatingState extends Equatable {
  const TeachersRatingState();

  @override
  List<Object> get props => [];
}

class TeachersRatingInitial extends TeachersRatingState {}

class TeachersRatingLoading extends TeachersRatingState {}

class TeachersRatingLoaded extends TeachersRatingState {
  final List<Teacher> teachers;

  const TeachersRatingLoaded(this.teachers);

  @override
  List<Object> get props => [teachers];
}

class TeachersRatingError extends TeachersRatingState {
  final String message;

  const TeachersRatingError(this.message);

  @override
  List<Object> get props => [message];
}
