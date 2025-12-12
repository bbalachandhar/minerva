import 'package:minerva_flutter/features/dashboard/domain/entities/module.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:developer';

class DashboardRepository {
  final SharedPreferences sharedPreferences;

  DashboardRepository({required this.sharedPreferences});

  Map<String, String> _getModuleIconMapping() {
    return {
      // Elearning Modules based on NewDashboard.java covers array and API short_codes
      'homework': 'assets/ic_dashboard_homework.png',
      'daily_assignment': 'assets/ic_assignment.png',
      'lesson_plan': 'assets/ic_lessonplan.png',
      'online_examination': 'assets/ic_onlineexam.png', // API provides 'online_examination'
      'download_center': 'assets/ic_downloadcenter.png',
      'online_course': 'assets/ic_onlinecourse.png',
      'live_classes': 'assets/ic_videocam.png', // Assuming videocam for live classes
      'gmeet_live_classes': 'assets/ic_videocam.png', // Assuming videocam for GMeet live classes

      // Academic Modules based on NewDashboard.java covers array and API short_codes
      'class_timetable': 'assets/ic_calender_cross.png', // API provides 'class_timetable'
      'syllabus_status': 'assets/ic_lessonplan.png', // API provides 'syllabus_status'
      'attendance': 'assets/ic_nav_attendance.png',
      'examinations': 'assets/ic_nav_reportcard.png', // API provides 'examinations'
      'student_timeline': 'assets/ic_nav_timeline.png', // API provides 'student_timeline'
      'mydocuments': 'assets/ic_documents_certificate.png', // API provides 'mydocuments'
      'behaviour_records': 'assets/ic_dashboard_homework.png', // API provides 'behaviour_records'
      'cbseexam': 'assets/ic_nav_reportcard.png', // API provides 'cbseexam'

      // Communicate Modules based on NewDashboard.java covers array and API short_codes
      'notice_board': 'assets/ic_notice.png', // API provides 'notice_board'
      // 'notification' short_code is not in API response for Communicate, but icon exists.
      // If API starts returning 'notification', this mapping should be added.

      // Other Modules based on NewDashboard.java covers array and API short_codes
      'fees': 'assets/ic_nav_fees.png',
      'apply_leave': 'assets/ic_leave.png',
      'visitor_book': 'assets/ic_visitors.png', // API provides 'visitor_book'
      'transport_routes': 'assets/ic_nav_transport.png',
      'hostel_rooms': 'assets/ic_nav_hostel.png', // API provides 'hostel_rooms'
      'calendar_to_do_list': 'assets/ic_dashboard_pandingtask.png', // API provides 'calendar_to_do_list'
      'library': 'assets/ic_library.png',
      'teachers_rating': 'assets/ic_teacher.png', // API provides 'teachers_rating'
    };
  }

  String _getIconPathForModule(String shortCode) {
    final iconPath = _getModuleIconMapping()[shortCode.toLowerCase()];
    // log('Module shortCode: $shortCode, Resolved Icon Path: $iconPath'); // Removed debugging log
    return iconPath ?? 'assets/placeholder_user.png'; // Fallback to a generic placeholder
  }

  Future<List<Module>> _fetchModules(String endpoint) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for dashboard modules.');
    }

    final url = Uri.parse('$apiUrl$endpoint');
    log('Dashboard Modules API URL: $url');

    final response = await http.post(
      url,
      headers: {
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'Content-Type': 'application/json',
        'User-ID': userId,
        'Authorization': accessToken,
      },
      body: json.encode({
        'user': loginType,
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('Dashboard Modules API Response for $endpoint: $body');

      if (body['module_list'] != null && body['module_list'] is List) {
        return (body['module_list'] as List)
            .where((item) {
              // log('Filtering module: ${item['short_code']}, Status: ${item['status']}, Type: ${item['status'].runtimeType}'); // Removed debugging log
              return item['status'] == '1'; // Corrected: Compare with string '1'
            })
            .map((item) {
              final module = Module(
                  name: item['name'] ?? 'Unknown',
                  icon: _getIconPathForModule(item['short_code'] ?? ''),
                );
              // log('Created Module: Name: ${module.name}, Icon: ${module.icon}'); // Removed debugging log
              return module;
            })
            .toList();
      } else {
        return []; // Return empty list if 'module_list' is missing or not a list
      }
    } else {
      throw Exception('Failed to load modules from $endpoint. Status code: ${response.statusCode}');
    }
  }

  Future<List<Module>> getElearningModules() async {
    return _fetchModules(Constants.getELearningUrl);
  }

  Future<List<Module>> getAcademicModules() async {
    return _fetchModules(Constants.getAcademicsUrl);
  }

  Future<List<Module>> getCommunicateModules() async {
    return _fetchModules(Constants.getCommunicateUrl);
  }

  Future<List<Module>> getOtherModules() async {
    return _fetchModules(Constants.getOthersUrl);
  }
}