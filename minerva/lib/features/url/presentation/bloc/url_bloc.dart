import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/url/data/repositories/settings_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';

part 'url_event.dart';
part 'url_state.dart';

class UrlBloc extends Bloc<UrlEvent, UrlState> {
  final SettingsRepository settingsRepository;

  UrlBloc({required this.settingsRepository}) : super(UrlInitial()) {
    on<UrlSubmitted>(_onUrlSubmitted);
  }

  void _onUrlSubmitted(UrlSubmitted event, Emitter<UrlState> emit) async {
    emit(UrlValidationLoading());
    try {
      await settingsRepository.validateUrl(event.url);
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('base_url', event.url);
      emit(UrlValidationSuccess());
    } catch (e) {
      emit(UrlValidationFailure(e.toString()));
    }
  }
}
