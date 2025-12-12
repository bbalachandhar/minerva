import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/staff_profile/data/repositories/staff_profile_repository.dart';

part 'staff_profile_event.dart';
part 'staff_profile_state.dart';

class StaffProfileBloc extends Bloc<StaffProfileEvent, StaffProfileState> {
  final StaffProfileRepository staffProfileRepository;

  StaffProfileBloc({required this.staffProfileRepository}) : super(StaffProfileInitial()) {
    on<FetchStaffProfile>(_onFetchStaffProfile);
  }

  void _onFetchStaffProfile(FetchStaffProfile event, Emitter<StaffProfileState> emit) async {
    emit(StaffProfileLoading());
    try {
      final profileData = await staffProfileRepository.getStaffProfile();
      emit(StaffProfileLoaded(profileData));
    } catch (e) {
      emit(StaffProfileError(e.toString()));
    }
  }
}
