// minerva/lib/features/visitor_book/presentation/widgets/visitor_item.dart

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:minerva_flutter/features/visitor_book/data/models/visitor_model.dart';

class VisitorItem extends StatelessWidget {
  final VisitorModel visitor;
  final String primaryColor;
  final String secondaryColor;
  final String imagesUrl;
  final String dateFormat;

  const VisitorItem({super.key,
    required this.visitor,
    required this.primaryColor,
    required this.secondaryColor,
    required this.imagesUrl,
    required this.dateFormat,
  });

  @override
  Widget build(BuildContext context) {
    // Format date based on dateFormat
    String formattedDate = visitor.date;
    try {
      final inputFormat = DateFormat('yyyy-MM-dd'); // API format
      final outputFormat = DateFormat(dateFormat); // User's preferred format
      formattedDate = outputFormat.format(inputFormat.parse(visitor.date));
    } catch (e) {
      // Fallback to original if parsing fails
      formattedDate = visitor.date;
    }

    final String attachmentUrl = visitor.image.isNotEmpty
        ? '$imagesUrl/uploads/visitor_book/${visitor.image}'
        : '';

    return Card(
      margin: const EdgeInsets.all(10.0), // Matches android:layout_margin="10dp"
      elevation: 5.0, // Matches app:cardElevation="5dp"
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15.0)), // Matches app:cardCornerRadius="15dp"
      color: Colors.white, // Matches app:cardBackgroundColor="@color/white"
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(5.0), // Matches android:padding="5dp" for headLayout
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Padding(
                  padding: const EdgeInsets.only(left: 10.0), // Matches android:layout_marginStart="10dp" for visitor_name
                  child: Text(
                    visitor.name,
                    style: TextStyle(
                      fontSize: 18.0, // Matches @dimen/heading (assuming this is 18sp/dp)
                      fontWeight: FontWeight.bold,
                      color: Color(int.parse(primaryColor.replaceFirst('#', '0xFF'))), // Matches @color/textHeading (assuming this is primaryColor for title)
                    ),
                  ),
                ),
                if (attachmentUrl.isNotEmpty)
                  Align(
                    alignment: Alignment.centerRight,
                    child: Padding(
                      padding: const EdgeInsets.only(right: 15.0), // Matches android:paddingEnd="15dp"
                      child: InkWell(
                        onTap: () {
                          context.push(
                            '/document_viewer',
                            extra: {
                              'documentUrl': attachmentUrl,
                              'title': 'Attachment for ${visitor.name}',
                            },
                          );
                        },
                        child: Padding(
                          padding: const EdgeInsets.all(2.0), // Matches android:padding="2dp" for ImageView
                          child: Image.asset(
                            'assets/ic_nav_download.png', // Use the copied custom icon
                            width: 20, // Matches android:layout_width="20dp"
                            height: 20, // Matches android:layout_height="20dp"
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
          // Info Section
          Padding(
            padding: const EdgeInsets.fromLTRB(5.0, 0, 10.0, 0), // Matches android:layout_marginStart="5dp" and android:layout_marginEnd="10dp"
            child: Column(
              children: [
                _buildInfoRow('Purpose', visitor.purpose),
                _buildInfoRow('Phone', visitor.contact),
                _buildInfoRow('ID Proof', visitor.idProof),
                _buildInfoRow('No. of People', visitor.numberOfPeople),
                _buildInfoRow('Date', formattedDate),
                _buildInfoRow('In-Time', visitor.inTime),
                _buildInfoRow('Out-Time', visitor.outTime),
                _buildInfoRow('Note', visitor.note),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Modified _buildInfoRow to include specific native padding
  Widget _buildInfoRow(String label, String value) {
    if (value.isEmpty) {
      return const SizedBox.shrink();
    }
    return Padding(
      padding: const EdgeInsets.only(left: 10.0, bottom: 5.0), // Matches android:layout_marginStart="10dp" and paddingBottom="5dp"
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120.0,
            child: Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14.0), // Assuming @dimen/secondaryText is 14sp/dp
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 14.0), // Assuming @dimen/secondaryText is 14sp/dp
            ),
          ),
        ],
      ),
    );
  }
}
