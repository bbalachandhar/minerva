part of 'about_school_bloc.dart';

abstract class AboutSchoolState extends Equatable {
  const AboutSchoolState();

  @override
  List<Object> get props => [];
}

class AboutSchoolInitial extends AboutSchoolState {}

class AboutSchoolLoading extends AboutSchoolState {}

class AboutSchoolLoaded extends AboutSchoolState {
  final AboutSchoolDetails details;

  const AboutSchoolLoaded({required this.details});

  @override
  List<Object> get props => [details];
}

class AboutSchoolError extends AboutSchoolState {
  final String message;

  const AboutSchoolError(this.message);

  @override
  List<Object> get props => [message];
}
