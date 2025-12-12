import 'dart:convert';
import 'dart:developer';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import 'package:minerva_flutter/features/hostel/domain/entities/hostel_room_entity.dart';
import 'package:minerva_flutter/features/hostel/domain/repositories/hostel_repository.dart';
import 'package:minerva_flutter/utils/constants.dart';

class HostelRepositoryImpl implements HostelRepository {
  final SharedPreferences sharedPreferences;

  HostelRepositoryImpl({required this.sharedPreferences});

  @override
  Future<List<HostelRoomEntity>> getHostelRooms() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);

    if (apiUrl == null || userId == null || accessToken == null || loginType == null) {
      throw Exception('Missing required authentication or API information for hostel rooms.');
    }

    final url = Uri.parse('$apiUrl${Constants.getHostelListUrl}');
    final Map<String, String> headers = {
      'Client-Service': Constants.clientService,
      'Auth-Key': Constants.authKey,
      'Content-Type': 'application/json',
      'User-ID': userId,
      'Authorization': accessToken,
    };

    final Map<String, String> requestBody = {
      'user': loginType,
      'student_id': userId,
    };

    log('Hostel Rooms API URL: $url');
    log('Hostel Rooms API Request Headers: ${json.encode(headers)}');
    log('Hostel Rooms API Request Body: ${json.encode(requestBody)}');

    final response = await http.post(
      url,
      headers: headers,
      body: json.encode(requestBody),
    );

    log('Hostel Rooms API Response Status Code: ${response.statusCode}');
    log('Hostel Rooms API Response Headers: ${json.encode(response.headers)}');
    log('Hostel Rooms API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      // Check if the response body starts with '<' indicating HTML
      if (response.body.trim().startsWith('<')) {
        throw Exception('API returned HTML instead of JSON. Raw response: ${response.body}');
      }

      final body = json.decode(response.body);
      log('Hostel Rooms API Response: $body');

      if (body['hostelarray'] != null && body['hostelarray'] is List) {
        return (body['hostelarray'] as List).map((item) {
          return HostelRoomEntity(
            id: item['id']?.toString() ?? '',
            hostelName: item['hostel_name'] ?? 'Unknown Hostel',
            roomType: item['room_type'] ?? 'N/A',
            roomNumber: item['room_no'] ?? 'N/A',
            numberOfBeds: item['no_of_bed']?.toString() ?? 'N/A',
            costPerBed: item['cost_per_bed']?.toString() ?? 'N/A',
            status: item['assign'] == 1 ? 'Assigned' : 'Vacant', // Use 'assign' field for status
          );
        }).toList();
      } else {
        throw Exception('Failed to load hostel rooms: "hostelarray" not found or not a list.');
      }
    } else {
      throw Exception('Failed to load hostel rooms. Status code: ${response.statusCode}');
    }
  }
}
