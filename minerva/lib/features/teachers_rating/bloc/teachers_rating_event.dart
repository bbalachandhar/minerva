import 'package:equatable/equatable.dart';

abstract class TeachersRatingEvent extends Equatable {
  const TeachersRatingEvent();

  @override
  List<Object> get props => [];
}

class FetchTeachers extends TeachersRatingEvent {}
