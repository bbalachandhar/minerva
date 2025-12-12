part of 'staff_profile_bloc.dart';

abstract class StaffProfileEvent extends Equatable {
  const StaffProfileEvent();

  @override
  List<Object> get props => [];
}

class FetchStaffProfile extends StaffProfileEvent {}
