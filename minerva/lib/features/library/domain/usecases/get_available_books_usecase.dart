import 'package:minerva_flutter/features/library/domain/entities/library_book_entity.dart';
import 'package:minerva_flutter/features/library/domain/repositories/library_repository.dart';

class GetAvailableBooksUseCase {
  final LibraryRepository repository;

  GetAvailableBooksUseCase({required this.repository});

  Future<List<LibraryBookEntity>> call() async {
    return await repository.getAvailableBooks();
  }
}
