part of 'about_school_bloc.dart';

abstract class AboutSchoolEvent extends Equatable {
  const AboutSchoolEvent();

  @override
  List<Object> get props => [];
}

class FetchAboutSchoolDetails extends AboutSchoolEvent {}
