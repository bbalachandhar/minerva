// minerva/lib/features/visitor_book/data/repositories/visitor_repository.dart

import 'dart:convert';
import 'dart:developer';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'package:minerva_flutter/features/visitor_book/data/models/visitor_model.dart';
import 'package:minerva_flutter/utils/app_constants.dart';

// Abstract interface for Visitor Repository
abstract class VisitorRepository {
  Future<List<VisitorModel>> getVisitors(String studentId);
}

// Implementation of Visitor Repository
class VisitorRepositoryImpl implements VisitorRepository {
  final SharedPreferences sharedPreferences;

  VisitorRepositoryImpl({required this.sharedPreferences});

  @override
  Future<List<VisitorModel>> getVisitors(String studentId) async {
    final apiUrl = sharedPreferences.getString(AppConstants.apiUrlKey);
    final userId = sharedPreferences.getString(AppConstants.userIdKey);
    final accessToken = sharedPreferences.getString(AppConstants.accessTokenKey);

    if (apiUrl == null || userId == null || accessToken == null) {
      throw Exception('Missing required API configuration for visitor book.');
    }

    final uri = Uri.parse('$apiUrl${AppConstants.getVisitorsUrl}'); // Assuming getVisitorsUrl is defined in AppConstants
    log('Visitor Book API URL: $uri');

    final body = jsonEncode({
      'student_id': studentId,
    });

    final response = await http.post(
      uri,
      headers: {
        'Client-Service': AppConstants.clientService,
        'Auth-Key': AppConstants.authKey,
        'Content-Type': AppConstants.contentType,
        'User-ID': userId,
        'Authorization': accessToken,
      },
      body: body,
    );

    if (response.statusCode == 200) {
      final jsonResponse = json.decode(response.body);
      log('Visitor Book API Response: $jsonResponse');

      final List<dynamic> visitorData = jsonResponse['result']; // Corrected key to 'result'
      final List<VisitorModel> visitors = visitorData.map((item) => VisitorModel.fromJson(item)).toList();
      return visitors;
    } else {
      throw Exception('Failed to load visitor data. Status code: ${response.statusCode}');
    }
  }
}
