import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/about_school/data/repositories/about_school_repository.dart';
import 'package:minerva_flutter/features/about_school/domain/entities/about_school.dart';
import 'dart:developer';

part 'about_school_event.dart';
part 'about_school_state.dart';

class AboutSchoolBloc extends Bloc<AboutSchoolEvent, AboutSchoolState> {
  final AboutSchoolRepository aboutSchoolRepository;

  AboutSchoolBloc({required this.aboutSchoolRepository}) : super(AboutSchoolInitial()) {
    on<FetchAboutSchoolDetails>(_onFetchAboutSchoolDetails);
  }

  void _onFetchAboutSchoolDetails(
      FetchAboutSchoolDetails event, Emitter<AboutSchoolState> emit) async {
    emit(AboutSchoolLoading());
    try {
      final details = await aboutSchoolRepository.getSchoolDetails();
      emit(AboutSchoolLoaded(details: details));
    } catch (e) {
      log('About School Error: $e');
      emit(AboutSchoolError(e.toString()));
    }
  }
}
