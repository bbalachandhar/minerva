// minerva/lib/features/apply_leave/domain/entities/leave_application_entity.dart
import 'package:equatable/equatable.dart';
import 'package:intl/intl.dart'; // Import intl for DateFormat

class LeaveApplicationEntity extends Equatable {
  const LeaveApplicationEntity({
    required this.id,
    required this.applicantName,
    required this.fromDate,
    required this.toDate,
    required this.status,
    this.approveDate, // Nullable as it might be empty
    required this.reason,
    required this.applyDate,
    this.docs, // Nullable as it might be empty
    required this.rawFromDate,
    required this.rawToDate,
    required this.rawApplyDate,
  });

  factory LeaveApplicationEntity.fromJson(Map<String, dynamic> json, String dateFormat) {
    // Helper to parse and format dates
    String _formatDate(String? dateString, String format) {
      if (dateString == null || dateString.isEmpty) return '';
      try {
        // Assuming original API date format is "yyyy-MM-dd"
        final inputFormat = DateFormat('yyyy-MM-dd');
        final outputFormat = DateFormat(format);
        return outputFormat.format(inputFormat.parse(dateString));
      } catch (e) {
        return dateString; // Return original if parsing fails
      }
    }

    // Name combining
    final String firstName = json['firstname'] ?? '';
    final String lastName = json['lastname'] ?? '';
    final String applicantName = '$firstName $lastName'.trim();


    return LeaveApplicationEntity(
      id: json['id'] as String,
      applicantName: applicantName,
      fromDate: _formatDate(json['from_date'] as String?, dateFormat),
      toDate: _formatDate(json['to_date'] as String?, dateFormat),
      status: json['status'] as String,
      approveDate: _formatDate(json['approve_date'] as String?, dateFormat),
      reason: json['reason'] as String,
      applyDate: _formatDate(json['apply_date'] as String?, dateFormat),
      docs: json['docs'] as String?,
      rawFromDate: json['from_date'] as String,
      rawToDate: json['to_date'] as String,
      rawApplyDate: json['apply_date'] as String,
    );
  }

  final String id;
  final String applicantName;
  final String fromDate;
  final String toDate;
  final String status;
  final String? approveDate;
  final String reason;
  final String applyDate;
  final String? docs;
  final String rawFromDate;
  final String rawToDate;
  final String rawApplyDate;

  @override
  List<Object?> get props => [
        id,
        applicantName,
        fromDate,
        toDate,
        status,
        approveDate,
        reason,
        applyDate,
        docs,
        rawFromDate,
        rawToDate,
        rawApplyDate,
      ];
}
