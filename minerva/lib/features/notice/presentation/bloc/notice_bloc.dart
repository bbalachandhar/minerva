// minerva/lib/features/notice/presentation/bloc/notice_bloc.dart
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/notice/domain/usecases/get_notices_usecase.dart';
import 'notice_event.dart';
import 'notice_state.dart';

class NoticeBloc extends Bloc<NoticeEvent, NoticeState> {
  final GetNoticesUseCase getNoticesUseCase;

  NoticeBloc({required this.getNoticesUseCase}) : super(NoticeInitial()) {
    on<FetchNotices>(_onFetchNotices);
  }

  Future<void> _onFetchNotices(
    FetchNotices event,
    Emitter<NoticeState> emit,
  ) async {
    emit(NoticeLoading());
    try {
      final notices = await getNoticesUseCase();
      emit(NoticeLoaded(notices: notices));
    } catch (e) {
      emit(NoticeError(message: e.toString()));
    }
  }
}
