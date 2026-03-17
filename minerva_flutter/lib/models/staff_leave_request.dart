class StaffLeaveRequest {
  final int id;
  final int staffId;
  final int leaveTypeId;
  final DateTime leaveFrom;
  final DateTime leaveTo;
  final double leaveDays;
  final String reason;
  final DateTime date;
  final String status;
  final String recommenderStatus;
  final String approverStatus;
  final String type;
  final String? staffName;
  final String? staffSurname;
  final String? employeeId;
  final int? recommenderId;
  final String? recommenderName;
  final String? recommenderSurname;
  final int? approverId;
  final String? approverName;
  final String? approverSurname;
  final String? documentFile;

  StaffLeaveRequest({
    required this.id,
    required this.staffId,
    required this.leaveTypeId,
    required this.leaveFrom,
    required this.leaveTo,
    required this.leaveDays,
    required this.reason,
    required this.date,
    required this.status,
    required this.recommenderStatus,
    required this.approverStatus,
    required this.type,
    this.staffName,
    this.staffSurname,
    this.employeeId,
    this.recommenderId,
    this.recommenderName,
    this.recommenderSurname,
    this.approverId,
    this.approverName,
    this.approverSurname,
    this.documentFile,
  });

  static int _parseInt(dynamic value, {int fallback = 0}) {
    if (value == null) return fallback;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString()) ?? fallback;
  }

  static int? _parseNullableInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static double _parseDouble(dynamic value, {double fallback = 0}) {
    if (value == null) return fallback;
    if (value is double) return value;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString()) ?? fallback;
  }

  static DateTime _parseDate(dynamic value) {
    if (value == null) return DateTime.now();
    final parsed = DateTime.tryParse(value.toString());
    return parsed ?? DateTime.now();
  }

  factory StaffLeaveRequest.fromJson(Map<String, dynamic> json) {
    return StaffLeaveRequest(
      id: _parseInt(json['id']),
      staffId: _parseInt(json['staff_id']),
      leaveTypeId: _parseInt(json['leave_type_id']),
      leaveFrom: _parseDate(json['leave_from']),
      leaveTo: _parseDate(json['leave_to']),
      leaveDays: _parseDouble(json['leave_days']),
      reason: (json['reason'] ?? json['employee_remark'] ?? '').toString(),
      date: _parseDate(json['date']),
      status: json['status'] ?? 'pending',
      recommenderStatus: json['recommender_status'] ?? 'pending',
      approverStatus: json['approver_status'] ?? 'pending',
      type: json['type'] ?? '',
      staffName: json['name'],
      staffSurname: json['surname'],
      employeeId: json['employee_id'],
      recommenderId: _parseNullableInt(json['recommender_id']),
      recommenderName: json['recommender_name'],
      recommenderSurname: json['recommender_surname'],
      approverId: _parseNullableInt(json['approver_id']),
      approverName: json['approver_name'],
      approverSurname: json['approver_surname'],
      documentFile: json['document_file'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'staff_id': staffId,
      'leave_type_id': leaveTypeId,
      'leave_from': leaveFrom.toString().split(' ')[0],
      'leave_to': leaveTo.toString().split(' ')[0],
      'leave_days': leaveDays,
      'reason': reason,
      'date': date.toString().split(' ')[0],
      'status': status,
      'recommender_status': recommenderStatus,
      'approver_status': approverStatus,
      'type': type,
      'name': staffName,
      'surname': staffSurname,
      'employee_id': employeeId,
      'recommender_id': recommenderId,
      'recommender_name': recommenderName,
      'recommender_surname': recommenderSurname,
      'approver_id': approverId,
      'approver_name': approverName,
      'approver_surname': approverSurname,
      'document_file': documentFile,
    };
  }

  String getFullRequesterName() {
    if (staffName == null && staffSurname == null) return '-';
    return '${staffName ?? ''} ${staffSurname ?? ''}'.trim();
  }

  String getFullRecommenderName() {
    if (recommenderName == null && recommenderSurname == null) return '-';
    return '${recommenderName ?? ''} ${recommenderSurname ?? ''}'.trim();
  }

  String getFullApproverName() {
    if (approverName == null && approverSurname == null) return '-';
    return '${approverName ?? ''} ${approverSurname ?? ''}'.trim();
  }

  String getStatusDisplay() {
    switch (status.toLowerCase()) {
      case 'pending':
        return 'Pending';
      case 'approved':
      case 'approve':
        return 'Approved';
      case 'disapproved':
      case 'disapprove':
      case 'rejected':
        return 'Rejected';
      default:
        return status;
    }
  }
}
