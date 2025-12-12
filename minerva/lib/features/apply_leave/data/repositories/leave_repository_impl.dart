import 'dart:convert';
import 'dart:developer';
import 'dart:io'; // Import for File
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';
import 'package:minerva_flutter/features/apply_leave/domain/repositories/leave_repository.dart';
import 'package:minerva_flutter/utils/constants.dart';

class LeaveRepositoryImpl implements LeaveRepository {
  final SharedPreferences sharedPreferences;

  LeaveRepositoryImpl({required this.sharedPreferences});

  @override
  Future<List<LeaveApplicationEntity>> getLeaveApplications() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final studentId = sharedPreferences.getString(Constants.studentId);
    final dateFormat = sharedPreferences.getString("dateFormat"); // Retrieved from BaseActivity

    if (apiUrl == null || userId == null || accessToken == null || studentId == null || dateFormat == null) {
      throw Exception('Missing required authentication or API information for apply leave.');
    }

    final url = Uri.parse('$apiUrl${Constants.getApplyLeaveUrl}');
    log('Apply Leave API URL: $url');

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
        'student_id': studentId,
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('Apply Leave API Response: $body');

      // The Android code checks for "result_array"
      if (body['result_array'] != null) {
        final List<dynamic> data = body['result_array']; // Key from Android response
        final List<LeaveApplicationEntity> leaveApplications = data.map((json) {
          return LeaveApplicationEntity.fromJson(json, dateFormat);
        }).toList();
        return leaveApplications;
      } else {
        // If result_array is null or not found, return empty list or throw error depending on expected behavior
        return [];
      }
    } else {
      throw Exception('Failed to load leave applications. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> deleteLeaveApplication(String leaveId) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);

    if (apiUrl == null || userId == null || accessToken == null) {
      throw Exception('Missing required authentication or API information for deleting leave.');
    }

    final url = Uri.parse('$apiUrl${Constants.deleteLeaveUrl}');
    log('Delete Leave API URL: $url');

    final response = await http.post(
      url,
      headers: {
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'Content-Type': Constants.contentType,
        'User-ID': userId,
        'Authorization': accessToken,
      },
      body: json.encode({
        'leave_id': leaveId,
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('Delete Leave API Response: $body');
      final String status = body['status'] ?? '0'; // Assuming 'status' is returned
      if (status == '1') { // Check for success status as in Android
        // Success
      } else {
        final Map<String, dynamic>? error = body['error'];
        final String errorMessage = error != null ? error['msg'] ?? 'Failed to delete leave application.' : 'Failed to delete leave application.';
        throw Exception(errorMessage);
      }
    } else {
      throw Exception('Failed to delete leave application. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> submitLeaveApplication({
    required String fromDate,
    required String toDate,
    required String reason,
    required String applyDate,
    File? document,
  }) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final studentId = sharedPreferences.getString(Constants.studentId);

    if (apiUrl == null || userId == null || accessToken == null || studentId == null) {
      throw Exception('Missing required authentication or API information for submitting leave.');
    }

    final url = Uri.parse('$apiUrl${Constants.addleaveUrl}');
    log('Submit Leave API URL: $url');

    var request = http.MultipartRequest('POST', url);

    request.headers.addAll({
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'User-ID': userId,
      'Authorization': accessToken,
    });

    request.fields['from_date'] = fromDate;
    request.fields['to_date'] = toDate;
    request.fields['reason'] = reason;
    request.fields['apply_date'] = applyDate;
    request.fields['student_id'] = studentId;

    if (document != null) {
      request.files.add(await http.MultipartFile.fromPath(
        'file', // Field name for the file in the API
        document.path,
        filename: document.path.split('/').last,
      ));
    }

    final response = await request.send();

    if (response.statusCode == 200) {
      final responseBody = await response.stream.bytesToString();
      final body = json.decode(responseBody);
      log('Submit Leave API Response: $body');
      final String status = body['status'] ?? '0';
      if (status == '1') {
        // Success
      } else {
        final Map<String, dynamic>? error = body['error'];
        final String errorMessage = error != null ? error['reason'] ?? 'Failed to submit leave application.' : 'Failed to submit leave application.';
        throw Exception(errorMessage);
      }
    } else {
      throw Exception('Failed to submit leave application. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> editLeaveApplication({
    required String leaveId,
    required String fromDate,
    required String toDate,
    required String reason,
    required String applyDate,
    File? document,
  }) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    // Student ID is not explicitly passed in Android's edit request body, but it's used in headers.

    if (apiUrl == null || userId == null || accessToken == null) {
      throw Exception('Missing required authentication or API information for editing leave.');
    }

    final url = Uri.parse('$apiUrl${Constants.updateLeaveUrl}');
    log('Edit Leave API URL: $url');

    var request = http.MultipartRequest('POST', url);

    request.headers.addAll({
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'User-ID': userId,
      'Authorization': accessToken,
    });

    request.fields['from_date'] = fromDate;
    request.fields['to_date'] = toDate;
    request.fields['reason'] = reason;
    request.fields['apply_date'] = applyDate;
    request.fields['id'] = leaveId; // The leave ID for updating

    if (document != null) {
      request.files.add(await http.MultipartFile.fromPath(
        'file', // Field name for the file in the API
        document.path,
        filename: document.path.split('/').last,
      ));
    } else {
      // If no new document is selected, and original had one, Android sends empty file part.
      // We'll mimic this by sending an empty file field if no new document is provided.
      // Or, we could skip the 'file' part if there's no document.
      // Android code: addFormDataPart("file","") if filePath is null
      request.fields['file'] = '';
    }

    final response = await request.send();

    if (response.statusCode == 200) {
      final responseBody = await response.stream.bytesToString();
      final body = json.decode(responseBody);
      log('Edit Leave API Response: $body');
      final String status = body['status'] ?? '0';
      if (status == '1') {
        // Success
      } else {
        final Map<String, dynamic>? error = body['error'];
        final String errorMessage = error != null ? error['reason'] ?? 'Failed to update leave application.' : 'Failed to update leave application.';
        throw Exception(errorMessage);
      }
    } else {
      throw Exception('Failed to update leave application. Status code: ${response.statusCode}');
    }
  }
}
