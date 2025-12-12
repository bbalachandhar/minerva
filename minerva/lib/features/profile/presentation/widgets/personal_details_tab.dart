import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/profile/domain/entities/profile.dart';

class PersonalDetailsTab extends StatelessWidget {
  final Profile profile;
  const PersonalDetailsTab({Key? key, required this.profile}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildDetailRow('Admission No', profile.admissionNo),
          _buildDetailRow('Roll No', profile.rollNo),
          _buildDetailRow('Class', profile.className),
          _buildDetailRow('Section', profile.section),
          _buildDetailRow('Session', profile.session),
          _buildDetailRow('First Name', profile.firstName),
          _buildDetailRow('Last Name', profile.lastName),
          _buildDetailRow('Gender', profile.gender),
          _buildDetailRow('Behaviour Score', profile.behaviourScore),
          // Add more personal details fields here as needed
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    if (value.isEmpty) {
      return const SizedBox.shrink(); // Hide if value is empty
    }
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            '$label:',
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          Flexible(child: Text(value)), // Use Flexible to prevent overflow
        ],
      ),
    );
  }
}