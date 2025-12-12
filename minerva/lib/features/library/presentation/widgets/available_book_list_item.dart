import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/library/domain/entities/library_book_entity.dart';

class AvailableBookListItem extends StatelessWidget {
  final LibraryBookEntity book;

  const AvailableBookListItem({super.key, required this.book});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15.0)),
      elevation: 5.0,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildHeader(),
          _buildDetails(),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(10.0),
      decoration: const BoxDecoration(
        color: Color(0xFFE7F1EE),
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(15.0),
          topRight: Radius.circular(15.0),
        ),
      ),
      child: Text(
        book.bookName ?? 'N/A',
        style: const TextStyle(
          fontWeight: FontWeight.bold,
          fontSize: 18,
        ),
      ),
    );
  }

  Widget _buildDetails() {
    return Padding(
      padding: const EdgeInsets.all(10.0),
      child: Column(
        children: [
          _buildDetailRow('Author', book.authorName),
          _buildDetailRow('Subject', book.subjectName),
          _buildDetailRow('Publisher', book.publisherName),
          _buildDetailRow('Rack No', book.rackNumber),
          _buildDetailRow('Quantity', book.quantity),
          _buildDetailRow('Price', book.price),
          _buildDetailRow('Created On', book.postDate),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String? value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        children: [
          SizedBox(
            width: 100,
            child: Text(label),
          ),
          Expanded(
            child: Text(
              value ?? 'N/A',
              style: const TextStyle(color: Colors.black54),
            ),
          ),
        ],
      ),
    );
  }
}
