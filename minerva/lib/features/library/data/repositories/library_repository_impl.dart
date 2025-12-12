import 'package:minerva_flutter/features/library/domain/entities/library_book_entity.dart';
import 'package:minerva_flutter/features/library/domain/repositories/library_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';

class LibraryRepositoryImpl implements LibraryRepository {
  final SharedPreferences sharedPreferences;

  LibraryRepositoryImpl({required this.sharedPreferences});

  @override
  Future<List<LibraryBookEntity>> getAvailableBooks() async {
    await Future.delayed(const Duration(seconds: 1));
    return [
      const LibraryBookEntity(
        id: '1',
        bookName: 'The Lord of the Rings',
        authorName: 'J.R.R. Tolkien',
        subjectName: 'Fantasy',
        publisherName: 'Allen & Unwin',
        rackNumber: 'A1',
        quantity: '10',
        price: '25.00',
        postDate: '2023-01-01',
      ),
       const LibraryBookEntity(
        id: '2',
        bookName: 'The Hobbit',
        authorName: 'J.R.R. Tolkien',
        subjectName: 'Fantasy',
        publisherName: 'Allen & Unwin',
        rackNumber: 'A1',
        quantity: '5',
        price: '20.00',
        postDate: '2023-01-02',
      ),
    ];
  }

  @override
  Future<List<LibraryBookEntity>> getIssuedBooks() async {
    await Future.delayed(const Duration(seconds: 1));
    return [
      const LibraryBookEntity(
        id: '3',
        bookName: 'Foundation',
        authorName: 'Isaac Asimov',
        issueDate: '2023-03-01',
        returnDate: '',
        dueDate: '2023-03-15',
        status: 'Not Returned',
      ),
      const LibraryBookEntity(
        id: '4',
        bookName: 'Dune',
        authorName: 'Frank Herbert',
        issueDate: '2023-02-15',
        returnDate: '2023-03-01',
        dueDate: '2023-03-01',
        status: 'Returned',
      ),
    ];
  }
}
