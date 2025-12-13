import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:minerva_flutter/features/staff_profile/domain/entities/staff_profile.dart'; // Import StaffProfile
import 'dart:developer';

class StaffProfileRepository {
  final SharedPreferences sharedPreferences;

  StaffProfileRepository({required this.sharedPreferences});

  Future<StaffProfile> getStaffProfile() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final token = sharedPreferences.getString(Constants.accessToken);
    final userId = sharedPreferences.getString(Constants.userId);

    if (apiUrl == null) {
      throw Exception('API URL not set.');
    }
    if (token == null || userId == null) {
      throw Exception('Authentication details not found.');
    }

    final url = Uri.parse('$apiUrl/staff/profile');
    log('Get Staff Profile URL: $url');

    final response = await http.get(
      url,
      headers: {
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'Content-Type': 'application/json',
        'User-ID': userId,
        'Authorization': token,
      },
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] == 200) {
        // Pass the entire body to fromJson, including can_edit_profile
        final Map<String, dynamic> profileDataWithFlag = {
          ...?body['data'], // Spread the 'data' map
          'can_edit_profile': body['can_edit_profile'] ?? false,
        };
        return StaffProfile.fromJson(profileDataWithFlag);
      } else {
        throw Exception(body['message'] ?? 'Failed to load profile');
      }
    } else {
      throw Exception('Failed to connect to the server: ${response.statusCode}');
    }
  }
}
