import 'package:minerva_flutter/features/library/domain/entities/library_book_entity.dart';

abstract class LibraryRepository {
  Future<List<LibraryBookEntity>> getAvailableBooks();
  Future<List<LibraryBookEntity>> getIssuedBooks(); // Assuming there's also an issued books list
}
