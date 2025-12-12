// minerva/lib/features/visitor_book/presentation/bloc/visitor_bloc.dart

import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/utils/app_constants.dart';
import 'package:minerva_flutter/utils/app_utility.dart';
import '../../data/repositories/visitor_repository.dart';
import 'visitor_event.dart';
import 'visitor_state.dart';

class VisitorBloc extends Bloc<VisitorEvent, VisitorState> {
  final VisitorRepository visitorRepository;

  VisitorBloc({required this.visitorRepository}) : super(VisitorInitial()) {
    on<FetchVisitors>(_onFetchVisitors);
    on<VisitorErrorOccurred>(_onVisitorErrorOccurred);
  }

  void _onVisitorErrorOccurred(
    VisitorErrorOccurred event,
    Emitter<VisitorState> emit,
  ) {
    emit(VisitorError(message: event.message));
  }

  void _onFetchVisitors(
    FetchVisitors event,
    Emitter<VisitorState> emit,
  ) async {
    emit(VisitorLoading());
    try {
      final primaryColor = await AppUtility.getSharedPreference(AppConstants.primaryColourKey);
      final secondaryColor = await AppUtility.getSharedPreference(AppConstants.secondaryColourKey);
      final imagesUrl = await AppUtility.getSharedPreference(AppConstants.imagesUrlKey);
      final dateFormat = await AppUtility.getSharedPreference("dateFormat"); // Native app uses "dateFormat" directly

      if (primaryColor.isEmpty || secondaryColor.isEmpty || imagesUrl.isEmpty || dateFormat.isEmpty) {
        // This is a critical error, as UI depends on these values
        emit(const VisitorError(message: "Missing application configuration (colors, image URL, date format)."));
        return;
      }

      final visitors = await visitorRepository.getVisitors(
        event.studentId,
      );

      emit(VisitorLoaded(
        visitors: visitors,
        primaryColor: primaryColor,
        secondaryColor: secondaryColor,
        imagesUrl: imagesUrl,
        dateFormat: dateFormat,
      ));
    } catch (e) {
      emit(VisitorError(message: e.toString()));
    }
  }
}
