part of 'staff_profile_bloc.dart';

abstract class StaffProfileState extends Equatable {
  const StaffProfileState();

  @override
  List<Object> get props => [];
}

class StaffProfileInitial extends StaffProfileState {}

class StaffProfileLoading extends StaffProfileState {}

class StaffProfileLoaded extends StaffProfileState {
  final Map<String, dynamic> profileData;

  const StaffProfileLoaded(this.profileData);

  @override
  List<Object> get props => [profileData];
}

class StaffProfileError extends StaffProfileState {
  final String error;

  const StaffProfileError(this.error);

  @override
  List<Object> get props => [error];
}
