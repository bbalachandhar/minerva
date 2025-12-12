import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/profile/domain/entities/profile.dart';
import 'package:url_launcher/url_launcher.dart';

class ParentsDetailsTab extends StatelessWidget {
  final Profile profile;
  const ParentsDetailsTab({Key? key, required this.profile}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Father Details
          _buildParentSection(
            context,
            'Father',
            profile.fatherName,
            profile.fatherPhone,
            profile.fatherOccupation,
            profile.fatherImageUrl,
          ),
          // Mother Details
          _buildParentSection(
            context,
            'Mother',
            profile.motherName,
            profile.motherPhone,
            profile.motherOccupation,
            profile.motherImageUrl,
          ),
          // Guardian Details
          _buildGuardianSection(
            context,
            profile.guardianName,
            profile.guardianPhone,
            profile.guardianOccupation,
            profile.guardianRelation,
            profile.guardianEmail,
            profile.guardianAddress,
            profile.guardianImageUrl,
          ),
        ],
      ),
    );
  }

  Widget _buildParentSection(
      BuildContext context,
      String role,
      String name,
      String phone,
      String occupation,
      String imageUrl, // This imageUrl now directly contains the network URL or local asset path
      ) {
    // Only show the section if at least one sub-field has data
    bool hasData = name.isNotEmpty || phone.isNotEmpty || occupation.isNotEmpty || imageUrl.isNotEmpty;
    if (!hasData) {
      return const SizedBox.shrink();
    }
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8.0),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '$role Details',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const Divider(),
            if (imageUrl.isNotEmpty) // showPic still relevant if API returns a flag for it
              Center(
                child: CircleAvatar(
                  radius: 40,
                  backgroundImage: imageUrl.startsWith('http') || imageUrl.startsWith('https')
                      ? NetworkImage(imageUrl)
                      : AssetImage(imageUrl) as ImageProvider,
                  onBackgroundImageError: (exception, stackTrace) {
                    // Fallback handled by the conditional check
                  },
                ),
              ),
            if (name.isNotEmpty) _buildDetailRow('Name', name),
            if (phone.isNotEmpty)
              _buildClickableDetailRow(context, 'Phone', phone, () => _makePhoneCall(phone)),
            if (occupation.isNotEmpty) _buildDetailRow('Occupation', occupation),
          ],
        ),
      ),
    );
  }

  Widget _buildGuardianSection(
      BuildContext context,
      String name,
      String phone,
      String occupation,
      String relation,
      String email,
      String address,
      String imageUrl, // This imageUrl now directly contains the network URL or local asset path
      ) {
    // Only show the section if at least one sub-field has data
    bool hasData = name.isNotEmpty || phone.isNotEmpty || occupation.isNotEmpty || relation.isNotEmpty ||
                   email.isNotEmpty || address.isNotEmpty || imageUrl.isNotEmpty;
    if (!hasData) {
      return const SizedBox.shrink();
    }
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8.0),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Guardian Details',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const Divider(),
            if (imageUrl.isNotEmpty)
              Center(
                child: CircleAvatar(
                  radius: 40,
                  backgroundImage: imageUrl.startsWith('http') || imageUrl.startsWith('https')
                      ? NetworkImage(imageUrl)
                      : AssetImage(imageUrl) as ImageProvider,
                  onBackgroundImageError: (exception, stackTrace) {
                    // Fallback handled by the conditional check
                  },
                ),
              ),
            if (name.isNotEmpty) _buildDetailRow('Name', name),
            if (phone.isNotEmpty)
              _buildClickableDetailRow(context, 'Phone', phone, () => _makePhoneCall(phone)),
            if (occupation.isNotEmpty) _buildDetailRow('Occupation', occupation),
            if (relation.isNotEmpty) _buildDetailRow('Relation', relation),
            if (email.isNotEmpty) _buildDetailRow('Email', email),
            if (address.isNotEmpty) _buildDetailRow('Address', address),
          ],
        ),
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

  Widget _buildClickableDetailRow(BuildContext context, String label, String value, VoidCallback onTap) {
    if (value.isEmpty) {
      return const SizedBox.shrink();
    }
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: InkWell(
        onTap: onTap,
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              '$label:',
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            Flexible(
              child: Text(
                value,
                style: TextStyle(
                  color: Theme.of(context).primaryColor,
                  decoration: TextDecoration.underline,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _makePhoneCall(String phoneNumber) async {
    final Uri launchUri = Uri(
      scheme: 'tel',
      path: phoneNumber,
    );
    await launchUrl(launchUri);
  }
}
