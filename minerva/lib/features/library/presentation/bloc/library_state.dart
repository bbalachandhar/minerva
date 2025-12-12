part of 'library_bloc.dart';

abstract class LibraryState extends Equatable {
  const LibraryState();

  @override
  List<Object> get props => [];
}

class LibraryInitial extends LibraryState {}

class LibraryLoading extends LibraryState {}

class LibraryLoaded extends LibraryState {
  final List<LibraryBookEntity> availableBooks;
  final List<LibraryBookEntity> issuedBooks;

  const LibraryLoaded({
    required this.availableBooks,
    required this.issuedBooks,
  });

  @override
  List<Object> get props => [availableBooks, issuedBooks];
}

class LibraryError extends LibraryState {
  final String message;

  const LibraryError({required this.message});

  @override
  List<Object> get props => [message];
}
