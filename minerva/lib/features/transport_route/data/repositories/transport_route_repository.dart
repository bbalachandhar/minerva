// minerva/lib/features/transport_route/data/repositories/transport_route_repository.dart

import 'dart:convert';
import 'dart:developer'; // For logging, similar to NoticeRepositoryImpl
import 'package:http/http.dart' as http; // Direct http client
import 'package:shared_preferences/shared_preferences.dart';

import 'package:minerva_flutter/features/transport_route/data/models/pickup_point_model.dart';
import 'package:minerva_flutter/features/transport_route/data/models/transport_route_model.dart';
import 'package:minerva_flutter/utils/app_constants.dart'; // For clientService, authKey, etc.

// Define the abstract interface in the same file
abstract class TransportRouteRepository {
  Future<Map<String, dynamic>> getTransportRoutes(String studentId);
}

// Implement the repository
class TransportRouteRepositoryImpl implements TransportRouteRepository {
  final SharedPreferences sharedPreferences;

  TransportRouteRepositoryImpl({required this.sharedPreferences});

  @override
  Future<Map<String, dynamic>> getTransportRoutes(String studentId) async {
    final apiUrl = sharedPreferences.getString(AppConstants.apiUrlKey);
    final userId = sharedPreferences.getString(AppConstants.userIdKey);
    final accessToken = sharedPreferences.getString(AppConstants.accessTokenKey);

    if (apiUrl == null || userId == null || accessToken == null) {
      throw Exception('Missing required API configuration for transport routes.');
    }

    final uri = Uri.parse('$apiUrl/webservice/gettransportroutes'); // Using the URL from Constants.java
    log('Transport Route API URL: $uri');

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
      final prettyString = JsonEncoder.withIndent('  ').convert(jsonResponse); // Use 2 spaces for indentation
      log('Transport Route API Response: ${prettyString}');

      // Directly parse 'route' and 'pickup_point' as per the provided API response structure
      final routeData = jsonResponse['route'];
      final pickupPointsData = jsonResponse['pickup_point'] as List;

      final transportRoute = TransportRouteModel.fromJson(routeData);
      final pickupPoints = pickupPointsData.map((item) => PickupPointModel.fromJson(item)).toList();

      return {
        'transportRoute': transportRoute,
        'pickupPoints': pickupPoints,
      };
    } else {
      throw Exception('Failed to load transport routes. Status code: ${response.statusCode}');
    }
  }
}