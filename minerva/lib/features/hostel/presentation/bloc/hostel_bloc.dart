import 'dart:async';
import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/hostel/domain/entities/hostel_room_entity.dart';
import 'package:minerva_flutter/features/hostel/domain/usecases/get_hostel_rooms_usecase.dart';

part 'hostel_event.dart';
part 'hostel_state.dart';

class HostelBloc extends Bloc<HostelEvent, HostelState> {
  final GetHostelRoomsUseCase getHostelRoomsUseCase;

  HostelBloc({required this.getHostelRoomsUseCase}) : super(HostelInitial()) {
    on<FetchHostelRoomsEvent>(_onFetchHostelRooms);
  }

  Future<void> _onFetchHostelRooms(
    FetchHostelRoomsEvent event,
    Emitter<HostelState> emit,
  ) async {
    emit(HostelLoading());
    try {
      final rooms = await getHostelRoomsUseCase();
      emit(HostelLoaded(rooms: rooms));
    } catch (e) {
      emit(HostelError(message: e.toString()));
    }
  }
}
