import 'package:flutter/material.dart';
import 'package:minerva_flutter/features/profile/domain/entities/profile.dart';
import 'dart:developer';

class OtherDetailsTab extends StatelessWidget {
  final Profile profile;
  const OtherDetailsTab({Key? key, required this.profile}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    // Removed debugging logs

    return SingleChildScrollView(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildDetailRow('Previous School', profile.previousSchool),
          _buildDetailRow('National ID No', profile.nationalIdNo),
          _buildDetailRow('Local ID No', profile.localIdNo),
          _buildDetailRow('Bank Account No', profile.bankAccountNo),
          _buildDetailRow('Bank Name', profile.bankName),
          _buildDetailRow('IFSC Code', profile.ifscCode),
          _buildDetailRow('RTE', profile.rte),
          _buildDetailRow('Student House', profile.studentHouse),
          // Transport Details
          if (profile.pickupPoint.isNotEmpty || profile.vehicleRoute.isNotEmpty || profile.vehicleNo.isNotEmpty ||
              profile.driverName.isNotEmpty || profile.driverContact.isNotEmpty)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8.0),
                  child: Text(
                    'Transport Details:',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ),
                _buildDetailRow('Pickup Point', profile.pickupPoint),
                _buildDetailRow('Vehicle Route', profile.vehicleRoute),
                _buildDetailRow('Vehicle No', profile.vehicleNo),
                _buildDetailRow('Driver Name', profile.driverName),
                _buildDetailRow('Driver Contact', profile.driverContact),
              ],
            ),
          // Hostel Details
          if (profile.hostelName.isNotEmpty || profile.roomNo.isNotEmpty || profile.roomType.isNotEmpty)
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8.0),
                  child: Text(
                    'Hostel Details:',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ),
                _buildDetailRow('Hostel Name', profile.hostelName),
                _buildDetailRow('Room No', profile.roomNo),
                _buildDetailRow('Room Type', profile.roomType),
              ],
            ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    // Hide if value is empty
    if (value.isEmpty) {
      return const SizedBox.shrink();
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
          Flexible(child: Text(value)),
        ],
      ),
    );
  }
}