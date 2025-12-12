import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:minerva_flutter/features/about_school/domain/entities/about_school.dart';
import 'dart:developer';

class AboutSchoolRepository {
  final SharedPreferences sharedPreferences;

  AboutSchoolRepository({required this.sharedPreferences});

  String _buildImageUrl(String baseUrl, String relativePath) {
    if (relativePath.isEmpty) {
      return ''; // Return empty if no relative path
    }

    String cleanedBaseUrl = baseUrl;
    if (!cleanedBaseUrl.endsWith('/')) {
      cleanedBaseUrl += '/';
    }

    String cleanedRelativePath = relativePath;
    if (cleanedRelativePath.startsWith('/')) {
      cleanedRelativePath = cleanedRelativePath.substring(1);
    }
    return '$cleanedBaseUrl$cleanedRelativePath';
  }

  Future<AboutSchoolDetails> getSchoolDetails() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final imagesUrl = sharedPreferences.getString(Constants.imagesUrl); // Get imagesUrl

    if (apiUrl == null || userId == null || accessToken == null || imagesUrl == null) {
      throw Exception('Missing required authentication or API information for About School.');
    }

    final url = Uri.parse('$apiUrl${Constants.getSchoolDetailsUrl}');
    log('About School API URL: $url');

    final response = await http.post(
      url,
      headers: {
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'Content-Type': 'application/json',
        'User-ID': userId,
        'Authorization': accessToken,
      },
      body: json.encode({ /* empty body as per native app */ }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('About School API Response: $body');

      // The response is directly the school details object, not nested
      return AboutSchoolDetails(
        name: body['name'] ?? '',
        email: body['email'] ?? '',
        phone: body['phone'] ?? '',
        address: body['address'] ?? '',
        diseCode: body['dise_code'] ?? '',
        session: body['session'] ?? '',
        startMonthName: body['start_month_name'] ?? '',
        appLogo: _buildImageUrl(imagesUrl, "uploads/school_content/logo/app_logo/${body['app_logo'] ?? ''}"),
      );
    } else {
      throw Exception('Failed to load school details. Status code: ${response.statusCode}');
    }
  }
}
