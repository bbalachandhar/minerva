import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../../config/app_config.dart';
import '../../utils/dynamic_api_headers.dart';
import '../auth_service.dart';

class ProfileApi {
  static Future<String> get baseUrl async => await AppConfig.getBaseUrl();

  static String _normalizeRootUrl(String url) {
    var normalized = url.trim();
    while (normalized.endsWith('/')) {
      normalized = normalized.substring(0, normalized.length - 1);
    }

    if (normalized.endsWith('/index.php')) {
      normalized = normalized.substring(0, normalized.length - 10);
    }
    while (normalized.endsWith('/')) {
      normalized = normalized.substring(0, normalized.length - 1);
    }

    if (normalized.endsWith('/api')) {
      normalized = normalized.substring(0, normalized.length - 4);
    }
    while (normalized.endsWith('/')) {
      normalized = normalized.substring(0, normalized.length - 1);
    }

    return normalized;
  }

  static Future<Map<String, dynamic>?> _buildProfileFromLoginData() async {
    final prefs = await SharedPreferences.getInstance();
    final loginDataRaw = prefs.getString('login_data');
    if (loginDataRaw == null || loginDataRaw.isEmpty) {
      return null;
    }

    final decoded = jsonDecode(loginDataRaw);
    if (decoded is! Map<String, dynamic>) {
      return null;
    }

    final recordRaw = decoded['record'];
    if (recordRaw is! Map) {
      return null;
    }

    final record = Map<String, dynamic>.from(recordRaw);

    // Normalize key fields used by Student model and profile widgets.
    record['id'] = (record['id'] ?? record['student_id'] ?? '').toString();
    record['student_id'] = (record['student_id'] ?? record['id'] ?? '')
        .toString();
    record['firstname'] =
        (record['firstname'] ?? record['name'] ?? record['username'] ?? '')
            .toString();
    record['lastname'] = (record['lastname'] ?? '').toString();
    record['class'] = (record['class'] ?? '').toString();
    record['section'] = (record['section'] ?? '').toString();
    record['student_session_id'] = (record['student_session_id'] ?? '')
        .toString();
    record['admission_no'] = (record['admission_no'] ?? '').toString();
    record['image'] = (record['image'] ?? '').toString();
    record['mobileno'] = (record['mobileno'] ?? '').toString();
    record['email'] = (record['email'] ?? '').toString();

    return {
      'status': 1,
      'message': 'Profile loaded from login data',
      'student_result': record,
      'student_fields': <dynamic>[],
      'custom_fields': <String, dynamic>{},
    };
  }

  static Future<Map<String, dynamic>> getStaffProfile() async {
    try {
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final endpoint = await AppConfig.getApiEndpoint('getStaffProfile');
      final userProfile = await AuthService.getUserProfile();
      final staffId =
          (userProfile['student_id'] ?? userProfile['user_id'] ?? '')
              .toString();

      final response = await http.post(
        Uri.parse(endpoint),
        headers: headers,
        body: jsonEncode({'staff_id': staffId}),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }

      throw Exception('Failed to load staff profile: ${response.statusCode}');
    } catch (e) {
      rethrow;
    }
  }

  // Get student profile data using getStudentProfile API
  static Future<Map<String, dynamic>> getUserProfile() async {
    try {
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final base = _normalizeRootUrl(await baseUrl);
      final apiBase = '${_normalizeRootUrl(await AppConfig.getApiUrl())}/api';

      // Get student ID from authentication
      var studentId = await AuthService.getStudentId();
      if (studentId.isEmpty) {
        final cachedProfile = await AuthService.getUserProfile();
        studentId =
            (cachedProfile['student_id'] ?? cachedProfile['user_id'] ?? '')
                .toString();
      }

      if (studentId.isEmpty) {
        throw Exception('Student ID not found. Please login again.');
      }

      final payload = jsonEncode({
        'student_id': studentId,
        'user_type': 'student',
      });

      final endpointCandidates = <String>{
        '$base/api/webservice/getStudentProfile',
        '$base/api/webservice/getstudentprofile',
        '$base/api/index.php/webservice/getStudentProfile',
        '$base/api/index.php/webservice/getstudentprofile',
        '$apiBase/webservice/getStudentProfile',
        '$apiBase/webservice/getstudentprofile',
      }.toList();

      http.Response? lastResponse;
      for (final url in endpointCandidates) {
        final response = await http.post(
          Uri.parse(url),
          headers: headers,
          body: payload,
        );

        lastResponse = response;
        if (response.statusCode == 200) {
          return jsonDecode(response.body);
        }

        if (response.statusCode == 404) {
          try {
            final decoded = jsonDecode(response.body);
            final message = decoded is Map<String, dynamic>
                ? (decoded['message'] ?? '').toString().toLowerCase()
                : '';
            if (message.contains('student not found')) {
              final fallbackProfile = await _buildProfileFromLoginData();
              if (fallbackProfile != null) {
                return fallbackProfile;
              }
            }
          } catch (_) {
            // Ignore parsing failures and continue endpoint fallbacks.
          }
        }

        // Retry on route mismatch only; other statuses are likely functional responses.
        if (response.statusCode != 404) {
          throw Exception('Failed to load profile: ${response.statusCode}');
        }
      }

      if (lastResponse != null) {
        throw Exception('Failed to load profile: ${lastResponse.statusCode}');
      }

      throw Exception('Failed to load profile: no response');
    } catch (e) {
      rethrow;
    }
  }

  // Update user profile
  static Future<Map<String, dynamic>> updateProfile(
    Map<String, dynamic> profileData,
  ) async {
    try {
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final base = await baseUrl;

      // Try multiple potential endpoints to resolve 404 issues
      final endpoints = [
        '$base/api/webservice/updateProfile',
        '$base/api/webservice/saveStudentProfile',
        '$base/api/webservice/editProfile',
        '$base/api/webservice/updateStudentProfile',
        '$base/api/webservice/setStudentProfile',
        '$base/api/webservice/editStudentProfile',
        // Add lowercase variations
        '$base/api/webservice/update_student_profile',
        '$base/api/webservice/update_profile',
        '$base/api/webservice/save_student_profile',
        '$base/api/webservice/edit_student_profile',
      ];

      http.Response? lastResponse;

      for (String url in endpoints) {
        try {
          final response = await http.post(
            Uri.parse(url),
            headers: headers,
            body: jsonEncode(profileData),
          );

          lastResponse = response;

          if (response.statusCode == 200) {
            return jsonDecode(response.body);
          }
        } catch (e) {
          // Continue trying alternate endpoints.
        }
      }

      if (lastResponse != null) {
        throw Exception(
          'Failed to update profile after trying multiple endpoints. Last status: ${lastResponse.statusCode}',
        );
      } else {
        throw Exception('Failed to reach any profile update endpoints.');
      }
    } catch (e) {
      rethrow;
    }
  }

  // Change password
  static Future<Map<String, dynamic>> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse('${await baseUrl}/api/webservice/changePassword'),
        headers: headers,
        body: jsonEncode({
          'current_password': currentPassword,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      } else {
        throw Exception('Failed to change password: ${response.statusCode}');
      }
    } catch (e) {
      rethrow;
    }
  }

  // Forgot password
  static Future<Map<String, dynamic>> forgotPassword(String email) async {
    try {
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final baseUrlStr = await baseUrl;

      // Use the correct endpoint for forgot password
      final url = Uri.parse('$baseUrlStr/api/forgot_password');

      final body = {
        'email': email,
        'usertype': 'student', // Default to student as per requirement
        'site_url': baseUrlStr, // Pass base URL as site_url
      };

      final response = await http.post(
        url,
        headers: headers,
        body: jsonEncode(body),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      } else {
        // Handle 404 or other errors gracefully
        if (response.statusCode == 404) {
          throw Exception('Forgot password service not found (404)');
        }
        throw Exception('Failed to send reset email: ${response.statusCode}');
      }
    } catch (e) {
      rethrow;
    }
  }

  // Reset password with token
  static Future<Map<String, dynamic>> resetPassword({
    required String token,
    required String newPassword,
    required String confirmPassword,
  }) async {
    try {
      final headers = await DynamicApiHeaders.getCompleteHeaders();
      final response = await http.post(
        Uri.parse('${await baseUrl}/api/webservice/resetPassword'),
        headers: headers,
        body: jsonEncode({
          'token': token,
          'new_password': newPassword,
          'confirm_password': confirmPassword,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      } else {
        throw Exception('Failed to reset password: ${response.statusCode}');
      }
    } catch (e) {
      rethrow;
    }
  }
}
