// minerva/lib/features/notice/domain/repositories/notice_repository.dart
import 'package:minerva_flutter/features/notice/domain/entities/notice_entity.dart';

abstract class NoticeRepository {
  Future<List<NoticeEntity>> getNotices();
}
