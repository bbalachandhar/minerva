import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import '../../data/repositories/authentication_repository.dart';

part 'auth_event.dart';
part 'auth_state.dart';

class AuthBloc extends Bloc<AuthEvent, AuthState> {
  final AuthenticationRepository authenticationRepository;

  AuthBloc({required this.authenticationRepository}) : super(AuthInitial()) {
    on<LoginRequested>(_onLoginRequested);
    on<StaffLoginRequested>(_onStaffLoginRequested);
    on<StaffLogoutRequested>(_onStaffLogoutRequested);
  }

  void _onLoginRequested(LoginRequested event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final role = await authenticationRepository.login(
        username: event.username,
        password: event.password,
      );
      emit(AuthSuccess(role));
    } catch (e) {
      emit(AuthFailure(e.toString()));
    }
  }

  void _onStaffLoginRequested(
      StaffLoginRequested event, Emitter<AuthState> emit) async {
    emit(AuthLoading());
    try {
      final role = await authenticationRepository.staffLogIn(
        email: event.email,
        password: event.password,
      );
      emit(AuthSuccess(role));
    } catch (e) {
      emit(AuthFailure(e.toString()));
    }
  }

  void _onStaffLogoutRequested(
      StaffLogoutRequested event, Emitter<AuthState> emit) async {
    // No loading state needed for logout, it should be quick
    try {
      await authenticationRepository.staffLogOut();
      emit(AuthInitial());
    } catch (e) {
      // Handle potential errors during logout, though it's less critical
      emit(AuthFailure(e.toString()));
    }
  }
}