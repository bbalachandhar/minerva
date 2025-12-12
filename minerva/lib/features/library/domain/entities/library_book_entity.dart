import 'package:equatable/equatable.dart';

class LibraryBookEntity extends Equatable {
  const LibraryBookEntity({
    this.id,
    this.bookName,
    this.authorName,
    this.subjectName,
    this.publisherName,
    this.rackNumber,
    this.quantity,
    this.price,
    this.postDate,
    this.issueDate,
    this.returnDate,
    this.dueDate,
    this.status,
  });

  final String? id;
  final String? bookName;
  final String? authorName;
  final String? subjectName;
  final String? publisherName;
  final String? rackNumber;
  final String? quantity;
  final String? price;
  final String? postDate;
  final String? issueDate;
  final String? returnDate;
  final String? dueDate;
  final String? status;


  @override
  List<Object?> get props => [
        id,
        bookName,
        authorName,
        subjectName,
        publisherName,
        rackNumber,
        quantity,
        price,
        postDate,
        issueDate,
        returnDate,
        dueDate,
        status,
      ];
}
