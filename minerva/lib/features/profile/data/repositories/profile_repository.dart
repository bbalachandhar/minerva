import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:minerva_flutter/features/profile/domain/entities/profile.dart';
import 'dart:developer';

class ProfileRepository {
  final SharedPreferences sharedPreferences;

  ProfileRepository({required this.sharedPreferences});

  String _buildImageUrl(String baseUrl, String relativePath) {
    if (relativePath.isEmpty) {
      return ''; // Return empty if no relative path, handled by getImageOrDefault
    }

    String cleanedBaseUrl = baseUrl;
    // Ensure base URL ends with a single slash
    if (!cleanedBaseUrl.endsWith('/')) {
      cleanedBaseUrl += '/';
    }

    String cleanedRelativePath = relativePath;
    // Ensure relative path does not start with a slash if base URL already ends with one
    if (cleanedRelativePath.startsWith('/')) {
      cleanedRelativePath = cleanedRelativePath.substring(1);
    }
    return '$cleanedBaseUrl$cleanedRelativePath';
  }


  Future<Profile> getStudentProfile() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final studentId = sharedPreferences.getString(Constants.studentId);
    final loginType = sharedPreferences.getString(Constants.loginType);
    final imagesUrl = sharedPreferences.getString(Constants.imagesUrl); // Get imagesUrl
    final gender = sharedPreferences.getString(Constants.gender) ?? 'unknown'; // Get gender from shared preferences

    log('ProfileRepository - imagesUrl: $imagesUrl'); // Log imagesUrl

    if (apiUrl == null || userId == null || accessToken == null || studentId == null || loginType == null || imagesUrl == null) {
      throw Exception('Missing required authentication or API information.');
    }

    final url = Uri.parse('$apiUrl${Constants.getStudentProfileUrl}');
    log('Profile API URL: $url');

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
        'user_type': loginType,
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('Profile API Response: $body');

      final studentResult = body['student_result'];
      // studentFields is no longer being extracted or used for UI visibility

      // Helper to get image URL or local placeholder
      String getImageOrDefault(String? apiImagePath, String defaultGender) {
        if (apiImagePath != null && apiImagePath.isNotEmpty) {
          final imageUrl = _buildImageUrl(imagesUrl, apiImagePath);
          // Check if the constructed URL is just the base imagesUrl (meaning no specific image)
          if (imageUrl == imagesUrl) { // This handles cases where API returns base URL for no image
            return defaultGender == 'female' ? 'assets/default_female.jpg' : 'assets/default_image.jpg';
          }
          // Only return network URL if it starts with http/https
          if (imageUrl.startsWith('http') || imageUrl.startsWith('https')) {
            return imageUrl;
          }
        }
        // Fallback to local gender-specific placeholder
        return defaultGender == 'female' ? 'assets/default_female.jpg' : 'assets/default_image.jpg';
      }
      
      return Profile(
        firstName: studentResult['firstname'] ?? '',
        lastName: studentResult['lastname'] ?? '',
        admissionNo: studentResult['admission_no'] ?? '',
        rollNo: studentResult['roll_no'] ?? '',
        className: studentResult['class'] ?? '',
        section: studentResult['section'] ?? '',
        session: studentResult['session'] ?? '',
        behaviourScore: studentResult['behaviou_score'] ?? '',
        barcodeImageUrl: getImageOrDefault(studentResult['barcode'], gender),
        qrcodeImageUrl: getImageOrDefault(studentResult['qrcode'], gender),
        profileImageUrl: getImageOrDefault(studentResult['image'], gender),
        gender: studentResult['gender'] ?? gender, // Use API gender if available, else shared preferences gender

        // Parents Details
        fatherName: studentResult['father_name'] ?? '',
        fatherPhone: studentResult['father_phone'] ?? '',
        fatherOccupation: studentResult['father_occupation'] ?? '',
        fatherImageUrl: getImageOrDefault(studentResult['father_pic'], 'male'), // Assuming father is male
        motherName: studentResult['mother_name'] ?? '',
        motherPhone: studentResult['mother_phone'] ?? '',
        motherOccupation: studentResult['mother_occupation'] ?? '',
        motherImageUrl: getImageOrDefault(studentResult['mother_pic'], 'female'), // Assuming mother is female
        guardianName: studentResult['guardian_name'] ?? '',
        guardianPhone: studentResult['guardian_phone'] ?? '',
        guardianOccupation: studentResult['guardian_occupation'] ?? '',
        guardianRelation: studentResult['guardian_relation'] ?? '',
        guardianEmail: studentResult['guardian_email'] ?? '',
        guardianAddress: studentResult['guardian_address'] ?? '',
        guardianImageUrl: getImageOrDefault(studentResult['guardian_pic'], gender), // Use user's gender for guardian if not specified

        // Other Details
        previousSchool: studentResult['previous_school'] ?? '',
        nationalIdNo: studentResult['adhar_no'] ?? '',
        localIdNo: studentResult['samagra_id'] ?? '',
        bankAccountNo: studentResult['bank_account_no'] ?? '',
        bankName: studentResult['bank_name'] ?? '',
        ifscCode: studentResult['ifsc_code'] ?? '',
        rte: studentResult['rte'] ?? '',
        studentHouse: studentResult['house_name'] ?? '',
        pickupPoint: studentResult['pickup_point_name'] ?? '',
        vehicleRoute: studentResult['route_title'] ?? '',
        vehicleNo: studentResult['vehicle_no'] ?? '',
        driverName: studentResult['driver_name'] ?? '',
        driverContact: studentResult['driver_contact'] ?? '',
        hostelName: studentResult['hostel_name'] ?? '',
        roomNo: studentResult['room_no'] ?? '',
        roomType: studentResult['room_type'] ?? '',
        
        // studentFields is removed from here
      );
    } else {
      throw Exception('Failed to load profile. Status code: ${response.statusCode}');
    }
  }
}