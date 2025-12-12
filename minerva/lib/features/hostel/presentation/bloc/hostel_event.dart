part of 'hostel_bloc.dart';

abstract class HostelEvent extends Equatable {
  const HostelEvent();

  @override
  List<Object> get props => [];
}

class FetchHostelRoomsEvent extends HostelEvent {}
