import 'dart:async';
import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/library/domain/entities/library_book_entity.dart';
import 'package:minerva_flutter/features/library/domain/usecases/get_available_books_usecase.dart';
import 'package:minerva_flutter/features/library/domain/usecases/get_issued_books_usecase.dart';
import 'dart:developer';

part 'library_event.dart';
part 'library_state.dart';

class LibraryBloc extends Bloc<LibraryEvent, LibraryState> {
  final GetAvailableBooksUseCase getAvailableBooksUseCase;
  final GetIssuedBooksUseCase getIssuedBooksUseCase;

  LibraryBloc({
    required this.getAvailableBooksUseCase,
    required this.getIssuedBooksUseCase,
  }) : super(LibraryInitial()) {
    on<FetchAvailableBooksEvent>(_onFetchAvailableBooks);
    on<FetchIssuedBooksEvent>(_onFetchIssuedBooks);
  }

  Future<void> _onFetchAvailableBooks(
    FetchAvailableBooksEvent event,
    Emitter<LibraryState> emit,
  ) async {
    emit(LibraryLoading());
    try {
      final availableBooks = await getAvailableBooksUseCase();
      // To keep existing issued books if any or pass empty list if not fetched yet
      final issuedBooks = (state is LibraryLoaded) ? (state as LibraryLoaded).issuedBooks : <LibraryBookEntity>[];
      emit(LibraryLoaded(availableBooks: availableBooks, issuedBooks: issuedBooks));
    } catch (e) {
      log('Error fetching available books: $e');
      emit(LibraryError(message: e.toString()));
    }
  }

  Future<void> _onFetchIssuedBooks(
    FetchIssuedBooksEvent event,
    Emitter<LibraryState> emit,
  ) async {
    emit(LibraryLoading());
    try {
      final issuedBooks = await getIssuedBooksUseCase();
      // To keep existing available books if any or pass empty list if not fetched yet
      final availableBooks = (state is LibraryLoaded) ? (state as LibraryLoaded).availableBooks : <LibraryBookEntity>[];
      emit(LibraryLoaded(availableBooks: availableBooks, issuedBooks: issuedBooks));
    } catch (e) {
      log('Error fetching issued books: $e');
      emit(LibraryError(message: e.toString()));
    }
  }
}
