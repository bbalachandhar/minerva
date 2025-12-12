import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'dart:developer';

class AuthenticationRepository {
  final SharedPreferences sharedPreferences;

  AuthenticationRepository({required this.sharedPreferences});

  Future<void> login({
    required String username,
    required String password,
    String? deviceToken, // Make deviceToken optional for now
  }) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    if (apiUrl == null) {
      throw Exception('API URL not set. Please go back and set the URL.');
    }

    final url = Uri.parse('$apiUrl${Constants.loginUrl}');
    log('Login URL: $url');

    final response = await http.post(
      url,
      headers: {
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'username': username,
        'password': password,
        'deviceToken': deviceToken ?? 'some_device_token', // Placeholder for device token
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] == 1) { // Changed '1' to 1 (integer comparison)
        final record = body['record'];
        await sharedPreferences.setString(Constants.loginType, body['role']);
        await sharedPreferences.setString(Constants.userId, body['id']);
        await sharedPreferences.setString(Constants.accessToken, body['token']); // Using Constants.accessToken
        await sharedPreferences.setString('schoolName', record['sch_name']);
        await sharedPreferences.setString(Constants.currency, record['currency_symbol']);
        await sharedPreferences.setString(Constants.currencyShortName, record['currency_short_name']); // Using Constants.currencyShortName
        await sharedPreferences.setString('startWeek', record['start_week']);
        await sharedPreferences.setString(Constants.superadminRestriction, record['superadmin_restriction']);
        await sharedPreferences.setString(Constants.studentSessionId, record['student_session_id']);

        // Date format handling
        String dateFormat = record['date_format'];
        dateFormat = dateFormat.replaceAll('m', 'MM').replaceAll('d', 'dd').replaceAll('Y', 'yyyy');
        await sharedPreferences.setString('dateFormat', dateFormat);

        String datetimeFormat = dateFormat + " hh:mm aa";
        await sharedPreferences.setString('datetimeFormat', datetimeFormat);

        await sharedPreferences.setString(Constants.langCode, record['language']['short_code']);
        await sharedPreferences.setString(Constants.currentLocale, record['language']['short_code']);

        String imageFromApi = record['image'] ?? '';
        String imgUrl;
        String gender = record['gender'] ?? 'unknown'; // Get gender from API, default to 'unknown'
        await sharedPreferences.setString(Constants.gender, gender); // Store gender

        if (imageFromApi.isNotEmpty) {
            imgUrl = (sharedPreferences.getString(Constants.imagesUrl) ?? '') + imageFromApi;
            if (!imgUrl.startsWith('http') && !imgUrl.startsWith('https')) {
                final imagesBaseUrl = sharedPreferences.getString(Constants.imagesUrl) ?? '';
                imgUrl = '$imagesBaseUrl$imgUrl';
            }
        } else {
            // Use gender-specific placeholder
            if (gender == 'female') {
                imgUrl = 'assets/default_female.jpg';
            } else {
                imgUrl = 'assets/default_image.jpg'; // Generic or male placeholder
            }
        }
        await sharedPreferences.setString(Constants.userImage, imgUrl);
        await sharedPreferences.setString(Constants.userName, record['username']);
        
        // Populate specific fields based on role
        if (body['role'] == 'student') {
          await sharedPreferences.setString(Constants.studentId, record['student_id']);
          String classData = record['class'] ?? '';
          String sectionData = record['section'] ?? '';
          if (classData.isNotEmpty && sectionData.isNotEmpty) {
            await sharedPreferences.setString(Constants.classSection, '$classData ($sectionData)');
          } else if (classData.isNotEmpty) {
            await sharedPreferences.setString(Constants.classSection, classData);
          } else {
            await sharedPreferences.setString(Constants.classSection, '');
          }
        } else if (body['role'] == 'parent') {
          await sharedPreferences.setString(Constants.parentsId, body['id']);
          // For parents with multiple children, studentId is set after child selection.
          // For single child, it's set here based on the first child in parent_childs array.
          // This requires parsing parent_childs, which is a more complex task for now.
          // For simplicity, we'll assume a studentId will be available if a single child.
          // If multiple children, this will need a dedicated selection mechanism.
        }
        
        // Check if 'app_ver' exists before saving
        if (body['app_ver'] != null) {
          await sharedPreferences.setString(Constants.appVer, body['app_ver']);
        }


        // Handle parent/student roles and navigation
        // This part needs more complex logic, for now, just success.
      } else {
        throw Exception(body['message'] ?? 'Login failed');
      }
    } else {
      throw Exception('Failed to connect to the login server: ${response.statusCode}');
    }
  }

  Future<void> staffLogIn({
    required String email,
    required String password,
    String? deviceToken,
  }) async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    if (apiUrl == null) {
      throw Exception('API URL not set. Please go back and set the URL.');
    }

    final url = Uri.parse('$apiUrl${Constants.staffLoginUrl}');
    log('Staff Login URL: $url');

    final response = await http.post(
      url,
      headers: {
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'Content-Type': 'application/json',
      },
      body: json.encode({
        'email': email,
        'password': password,
        'deviceToken': deviceToken ?? 'some_device_token',
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      if (body['status'] == 1) {
        final record = body['record'];
        await sharedPreferences.setString(Constants.loginType, body['role']);
        await sharedPreferences.setString(Constants.userId, body['id']);
        await sharedPreferences.setString(Constants.accessToken, body['token']);
        await sharedPreferences.setString('schoolName', record['sch_name']);
        await sharedPreferences.setString(Constants.currency, record['currency_symbol']);
        await sharedPreferences.setString('startWeek', record['start_week']);

        String dateFormat = record['date_format'];
        dateFormat = dateFormat.replaceAll('m', 'MM').replaceAll('d', 'dd').replaceAll('Y', 'yyyy');
        await sharedPreferences.setString('dateFormat', dateFormat);

        String datetimeFormat = dateFormat + " hh:mm aa";
        await sharedPreferences.setString('datetimeFormat', datetimeFormat);

        await sharedPreferences.setString(Constants.langCode, record['language']['short_code']);
        await sharedPreferences.setString(Constants.currentLocale, record['language']['short_code']);

        String imageFromApi = record['image'] ?? '';
        String imgUrl;
        if (imageFromApi.isNotEmpty) {
          imgUrl = (sharedPreferences.getString(Constants.imagesUrl) ?? '') + imageFromApi;
        } else {
          imgUrl = 'assets/default_image.jpg';
        }
        await sharedPreferences.setString(Constants.userImage, imgUrl);
        await sharedPreferences.setString(Constants.userName, record['username']);
      } else {
        throw Exception(body['message'] ?? 'Login failed');
      }
    } else {
      throw Exception('Failed to connect to the login server: ${response.statusCode}');
    }
  }

  Future<void> staffLogOut() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final token = sharedPreferences.getString(Constants.accessToken);
    final userId = sharedPreferences.getString(Constants.userId);

    if (apiUrl != null && token != null && userId != null) {
      final url = Uri.parse('$apiUrl${Constants.staffLogoutUrl}');
      await http.post(
        url,
        headers: {
          'Client-Service': Constants.clientService,
          'Auth-Key': Constants.authKey,
          'Content-Type': 'application/json',
          'User-ID': userId,
          'Authorization': token,
        },
        body: json.encode({'deviceToken': 'some_device_token'})
      );
    }

    await sharedPreferences.remove(Constants.accessToken);
    await sharedPreferences.remove(Constants.userId);
    await sharedPreferences.remove(Constants.loginType);
    await sharedPreferences.remove(Constants.userName);
    await sharedPreferences.remove(Constants.userImage);
  }
}
