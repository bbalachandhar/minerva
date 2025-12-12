part of 'hostel_bloc.dart';

abstract class HostelState extends Equatable {
  const HostelState();

  @override
  List<Object> get props => [];
}

class HostelInitial extends HostelState {}

class HostelLoading extends HostelState {}

class HostelLoaded extends HostelState {
  final List<HostelRoomEntity> rooms;

  const HostelLoaded({required this.rooms});

  @override
  List<Object> get props => [rooms];
}

class HostelError extends HostelState {
  final String message;

  const HostelError({required this.message});

  @override
  List<Object> get props => [message];
}
