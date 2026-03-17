import 'dart:convert';

import 'package:http/http.dart' as http;

import '../../config/app_config.dart';
import '../../utils/dynamic_api_headers.dart';

class StaffWorkspaceApi {
  static Future<Map<String, dynamic>> getStaffProfile() async {
    try {
      final url = await AppConfig.getApiEndpoint('getStaffProfile');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(<String, dynamic>{}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load staff profile: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading staff profile: $e'};
    }
  }

  static Future<Map<String, dynamic>> getAttendanceSummary({
    required String month,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('getStaffAttendanceSummary');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({'month': month}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load attendance summary: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading attendance summary: $e'};
    }
  }

  static Future<Map<String, dynamic>> getTeacherTimetable({
    required String startDate,
    required String endDate,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('getTeacherTimetableForStaff');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({'start_date': startDate, 'end_date': endDate}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load timetable: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading timetable: $e'};
    }
  }

  static Future<Map<String, dynamic>> markMyAttendance({
    required String attendanceDate,
    required int attendanceTypeId,
    String? inTime,
    String? outTime,
    String? remark,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('markMyAttendance');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final payload = <String, dynamic>{
        'attendance_date': attendanceDate,
        'attendance_type_id': attendanceTypeId,
        'remark': (remark ?? '').trim(),
      };

      if (inTime != null && inTime.trim().isNotEmpty) {
        payload['in_time'] = inTime.trim();
      }
      if (outTime != null && outTime.trim().isNotEmpty) {
        payload['out_time'] = outTime.trim();
      }

      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(payload),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to save attendance: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error saving attendance: $e'};
    }
  }

  static Future<Map<String, dynamic>> getMyAttendanceByDate({
    required String attendanceDate,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('getMyAttendanceByDate');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({'attendance_date': attendanceDate}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load attendance: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading attendance: $e'};
    }
  }

  static Future<Map<String, dynamic>> getStudentRosterForAttendance({
    required int classId,
    required int sectionId,
    required String date,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint(
        'getStudentRosterForAttendance',
      );
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({
          'class_id': classId,
          'section_id': sectionId,
          'date': date,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load roster: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading roster: $e'};
    }
  }

  static Future<Map<String, dynamic>> saveStudentAttendance({
    required List<Map<String, dynamic>> rows,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('saveStudentAttendance');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({'rows': rows}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to save attendance: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error saving attendance: $e'};
    }
  }

  static Future<Map<String, dynamic>> getPeriodWiseStudentRosterForAttendance({
    required int subjectTimetableId,
    required String date,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint(
        'getPeriodWiseStudentRosterForAttendance',
      );
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({
          'subject_timetable_id': subjectTimetableId,
          'date': date,
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load period-wise roster: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading period-wise roster: $e'};
    }
  }

  static Future<Map<String, dynamic>> savePeriodWiseStudentAttendance({
    required List<Map<String, dynamic>> rows,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint(
        'savePeriodWiseStudentAttendance',
      );
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({'rows': rows}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message':
            'Failed to save period-wise attendance: ${response.statusCode}',
      };
    } catch (e) {
      return {
        'status': 0,
        'message': 'Error saving period-wise attendance: $e',
      };
    }
  }

  static Future<Map<String, dynamic>> getStaffLeaveBalance({
    required int staffId,
    String? employeeId,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('getStaffLeaveBalance');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({
          'staff_id': staffId,
          if (employeeId != null && employeeId.trim().isNotEmpty)
            'employee_id': employeeId.trim(),
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load leave balance: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading leave balance: $e'};
    }
  }

  static Future<Map<String, dynamic>> getStaffLeaveRequests({
    required int staffId,
    String? employeeId,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('getStaffLeaveRequests');
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode({
          'staff_id': staffId,
          if (employeeId != null && employeeId.trim().isNotEmpty)
            'employee_id': employeeId.trim(),
        }),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to load leave requests: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error loading leave requests: $e'};
    }
  }

  static Future<Map<String, dynamic>> addStaffLeaveRequest({
    required int leaveTypeId,
    required DateTime leaveFrom,
    required DateTime leaveTo,
    required String reason,
    bool isHalfDay = false,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('addStaffLeaveRequest');
      final headers = await DynamicApiHeaders.getCompleteHeaders();

      final payload = {
        'leave_type_id': leaveTypeId,
        'leave_from': leaveFrom.toString().split(' ')[0],
        'leave_to': leaveTo.toString().split(' ')[0],
        'reason': reason,
        'is_half_day': isHalfDay,
      };

      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(payload),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to create leave request: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error creating leave request: $e'};
    }
  }

  static Future<Map<String, dynamic>> updateStaffLeaveRequest({
    required int leaveId,
    required int leaveTypeId,
    required DateTime leaveFrom,
    required DateTime leaveTo,
    required String reason,
    bool isHalfDay = false,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('updateStaffLeaveRequest');
      final headers = await DynamicApiHeaders.getCompleteHeaders();

      final payload = {
        'leave_id': leaveId,
        'leave_type_id': leaveTypeId,
        'leave_from': leaveFrom.toString().split(' ')[0],
        'leave_to': leaveTo.toString().split(' ')[0],
        'reason': reason,
        'is_half_day': isHalfDay,
      };

      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(payload),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to update leave request: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error updating leave request: $e'};
    }
  }

  static Future<Map<String, dynamic>> deleteStaffLeaveRequest({
    required int leaveId,
  }) async {
    try {
      final url = await AppConfig.getApiEndpoint('deleteStaffLeaveRequest');
      final headers = await DynamicApiHeaders.getCompleteHeaders();

      final payload = {'leave_id': leaveId};

      final response = await http.post(
        Uri.parse(url),
        headers: headers,
        body: jsonEncode(payload),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body) as Map<String, dynamic>;
      }

      return {
        'status': 0,
        'message': 'Failed to delete leave request: ${response.statusCode}',
      };
    } catch (e) {
      return {'status': 0, 'message': 'Error deleting leave request: $e'};
    }
  }
}
