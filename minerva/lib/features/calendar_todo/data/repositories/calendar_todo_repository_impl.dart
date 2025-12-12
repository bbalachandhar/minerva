import 'dart:convert';
import 'dart:developer';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'package:minerva_flutter/features/calendar_todo/domain/entities/calendar_todo_entity.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/repositories/calendar_todo_repository.dart';
import 'package:minerva_flutter/utils/constants.dart';

class CalendarTodoRepositoryImpl implements CalendarTodoRepository {
  final SharedPreferences sharedPreferences;

  CalendarTodoRepositoryImpl({required this.sharedPreferences});

  @override
  Future<List<CalendarTodoEntity>> getTasks({String? date}) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for calendar tasks.');
    }

    final url = Uri.parse('$apiUrl${Constants.getTaskUrl}');
    final Map<String, String> headers = {
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'Content-Type': 'application/json',
      'User-ID': userId,
      'Authorization': accessToken,
    };

    final Map<String, String> requestBody = {
      'user': loginType,
      'user_id': userId,
      if (date != null) 'date': date, // Optional date filter
    };

    log('Calendar Todo API URL: $url');
    log('Calendar Todo API Request Headers: ${json.encode(headers)}');
    log('Calendar Todo API Request Body: ${json.encode(requestBody)}');

    final response = await http.post(
      url,
      headers: headers,
      body: json.encode(requestBody),
    );

    log('Calendar Todo API Response Status Code: ${response.statusCode}');
    log('Calendar Todo API Response Headers: ${json.encode(response.headers)}');
    log('Calendar Todo API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      if (response.body.trim().startsWith('<')) {
        throw Exception('API returned HTML instead of JSON. Raw response: ${response.body}');
      }

      final body = json.decode(response.body);
      log('Calendar Todo API Response: $body');

      if (body['tasks'] != null && body['tasks'] is List) {
        return (body['tasks'] as List).map((item) {
          return CalendarTodoEntity(
            id: item['id']?.toString() ?? '',
            taskName: item['event_title'] ?? 'Unknown Task',
            taskDate: item['start_date'] ?? 'N/A', // Corrected to use 'start_date'
            isCompleted: item['is_active']?.toString().toLowerCase() == 'yes', // 'no' means completed
          );
        }).toList();
      } else {
        throw Exception('Failed to load calendar tasks: "tasks" not found or not a list.');
      }
    } else {
      throw Exception('Failed to load calendar tasks. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> createTask(CalendarTodoEntity task) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for creating task.');
    }

    final url = Uri.parse('$apiUrl${Constants.createTaskUrl}');
    final Map<String, String> headers = {
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'Content-Type': 'application/json',
      'User-ID': userId,
      'Authorization': accessToken,
    };

    final Map<String, String> requestBody = {
      'user': loginType,
      'user_id': userId,
      'event_title': task.taskName,
      'date': '${task.taskDate} 00:00:00', // Format date with time for API
    };

    log('Create Task API URL: $url');
    log('Create Task API Request Headers: ${json.encode(headers)}');
    log('Create Task API Request Body: ${json.encode(requestBody)}');

    final response = await http.post(
      url,
      headers: headers,
      body: json.encode(requestBody),
    );

    log('Create Task API Response Status Code: ${response.statusCode}');
    log('Create Task API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] != 1) {
        throw Exception(body['msg'] ?? 'Failed to create task.');
      }
    } else {
      throw Exception('Failed to create task. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> updateTask(CalendarTodoEntity task) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for updating task.');
    }

    final url = Uri.parse('$apiUrl${Constants.addedittimelineUrl}'); // Assuming addedittimelineUrl is used for update
    final Map<String, String> headers = {
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'Content-Type': 'application/json',
      'User-ID': userId,
      'Authorization': accessToken,
    };

    final Map<String, String> requestBody = {
      'user': loginType,
      'user_id': userId, // Changed from student_id to user_id
      'id': task.id,
      'event_title': task.taskName,
      'date': '${task.taskDate} 00:00:00', // Format date with time for API
      'is_completed': task.isCompleted ? '1' : '0',
    };

    log('Update Task API URL: $url');
    log('Update Task API Request Headers: ${json.encode(headers)}');
    log('Update Task API Request Body: ${json.encode(requestBody)}');

    final response = await http.post(
      url,
      headers: headers,
      body: json.encode(requestBody),
    );

    log('Update Task API Response Status Code: ${response.statusCode}');
    log('Update Task API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] != 1) {
        throw Exception(body['msg'] ?? 'Failed to update task.');
      }
    } else {
      throw Exception('Failed to update task. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> deleteTask(String id) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for deleting task.');
    }

    final url = Uri.parse('$apiUrl${Constants.deleteTaskUrl}');
    final Map<String, String> headers = {
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'Content-Type': 'application/json',
      'User-ID': userId,
      'Authorization': accessToken,
    };

    final Map<String, String> requestBody = {
      'user': loginType,
      'user_id': userId, // Changed from student_id to user_id
      'id': id,
    };

    log('Delete Task API URL: $url');
    log('Delete Task API Request Headers: ${json.encode(headers)}');
    log('Delete Task API Request Body: ${json.encode(requestBody)}');

    final response = await http.post(
      url,
      headers: headers,
      body: json.encode(requestBody),
    );

    log('Delete Task API Response Status Code: ${response.statusCode}');
    log('Delete Task API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] != 1) {
        throw Exception(body['msg'] ?? 'Failed to delete task.');
      }
    } else {
      throw Exception('Failed to delete task. Status code: ${response.statusCode}');
    }
  }

  @override
  Future<void> markTaskAsComplete(String id, bool isCompleted) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for marking task as complete.');
    }

    final url = Uri.parse('$apiUrl${Constants.markTaskUrl}');
    final Map<String, String> headers = {
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'Content-Type': 'application/json',
      'User-ID': userId,
      'Authorization': accessToken,
    };

    final Map<String, String> requestBody = {
      'user': loginType,
      'user_id': userId, // Changed back to user_id for consistency
      'id': id,
      'is_completed': isCompleted ? '1' : '0',
    };

    log('Mark Task As Complete API URL: $url');
    log('Mark Task As Complete API Request Headers: ${json.encode(headers)}');
    log('Mark Task As Complete API Request Body: ${json.encode(requestBody)}');

    final response = await http.post(
      url,
      headers: headers,
      body: json.encode(requestBody),
    );

    log('Mark Task As Complete API Response Status Code: ${response.statusCode}');
    log('Mark Task As Complete API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] != 1) {
        throw Exception(body['msg'] ?? 'Failed to mark task as complete.');
      }
    } else {
      throw Exception('Failed to mark task as complete. Status code: ${response.statusCode}');
    }
  }
}
