part of 'auth_bloc.dart';
abstract class AuthEvent extends Equatable {
  const AuthEvent();

  @override
  List<Object> get props => [];
}

class LoginRequested extends AuthEvent {
  final String username;
  final String password;

  const LoginRequested({required this.username, required this.password});

  @override
  List<Object> get props => [username, password];
}

class StaffLoginRequested extends AuthEvent {
  final String email;
  final String password;

  const StaffLoginRequested({required this.email, required this.password});

  @override
  List<Object> get props => [email, password];
}

class StaffLogoutRequested extends AuthEvent {}