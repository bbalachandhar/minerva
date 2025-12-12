// minerva/lib/features/notice/presentation/bloc/notice_event.dart
import 'package:equatable/equatable.dart';

abstract class NoticeEvent extends Equatable {
  const NoticeEvent();

  @override
  List<Object> get props => [];
}

class FetchNotices extends NoticeEvent {
  const FetchNotices();
}
