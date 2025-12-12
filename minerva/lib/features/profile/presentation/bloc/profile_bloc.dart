import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/profile/data/repositories/profile_repository.dart';
import 'package:minerva_flutter/features/profile/domain/entities/profile.dart';
import 'dart:developer';

part 'profile_event.dart';
part 'profile_state.dart';

class ProfileBloc extends Bloc<ProfileEvent, ProfileState> {
  final ProfileRepository profileRepository;

  ProfileBloc({required this.profileRepository}) : super(ProfileInitial()) {
    on<FetchProfileDetails>(_onFetchProfileDetails);
  }

  void _onFetchProfileDetails(
      FetchProfileDetails event, Emitter<ProfileState> emit) async {
    emit(ProfileLoading());
    try {
      final profile = await profileRepository.getStudentProfile();
      emit(ProfileLoaded(profile: profile));
    } catch (e) {
      log('Profile Error: $e');
      emit(ProfileError(e.toString()));
    }
  }
}
