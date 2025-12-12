// minerva/lib/features/notice/domain/usecases/get_notices_usecase.dart
import 'package:minerva_flutter/features/notice/domain/entities/notice_entity.dart';
import 'package:minerva_flutter/features/notice/domain/repositories/notice_repository.dart';

class GetNoticesUseCase {
  final NoticeRepository repository;

  GetNoticesUseCase({required this.repository});

  Future<List<NoticeEntity>> call() async {
    return await repository.getNotices();
  }
}
