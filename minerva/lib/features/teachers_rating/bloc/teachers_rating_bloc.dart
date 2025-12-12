import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/teachers_rating/bloc/teachers_rating_event.dart';
import 'package:minerva_flutter/features/teachers_rating/bloc/teachers_rating_state.dart';
import 'package:minerva_flutter/features/teachers_rating/data/repositories/teacher_repository.dart';

class TeachersRatingBloc extends Bloc<TeachersRatingEvent, TeachersRatingState> {
  final TeacherRepository teacherRepository;

  TeachersRatingBloc({required this.teacherRepository}) : super(TeachersRatingInitial()) {
    on<FetchTeachers>((event, emit) async {
      emit(TeachersRatingLoading());
      try {
        final teachers = await teacherRepository.getTeachers();
        emit(TeachersRatingLoaded(teachers));
      } catch (e) {
        emit(TeachersRatingError(e.toString()));
      }
    });
  }
}
