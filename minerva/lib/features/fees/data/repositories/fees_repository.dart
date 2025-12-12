import 'dart:convert';
import 'dart:developer';
import 'package:http/http.dart' as http;
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FeesRepository {
  final SharedPreferences sharedPreferences;

  FeesRepository({required this.sharedPreferences});

  Future<Map<String, dynamic>> getFees() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final studentId = sharedPreferences.getString(Constants.studentId);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);

    if (apiUrl == null || studentId == null || userId == null ||
        accessToken == null) {
      throw Exception('Required data not found in SharedPreferences');
    }

    final response = await http.post(
      Uri.parse('$apiUrl${Constants.getFeesUrl}'),
      headers: <String, String>{
        'Content-Type': 'application/json; charset=UTF-8',
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'User-ID': userId,
        'Authorization': accessToken,
      },
      body: jsonEncode(<String, String>{
        'student_id': studentId,
      }),
    );

    if (response.statusCode == 200) {
      log('Fees API Response: ${response.body}');
      try {
        final jsonResponse = json.decode(response.body);

        final grandTotal = FeeGrandTotal.fromJson(jsonResponse['grand_fee']);

        final List<Fee> feeItems = [];
        final studentDueFee = jsonResponse['student_due_fee'] as List;
        for (var feeGroup in studentDueFee) {
          final fees = feeGroup['fees'] as List;
          for (var fee in fees) {
            feeItems.add(Fee.fromJson(fee));
          }
        }

        final List<TransportFee> transportFees = [];
        final transportFeesJson = jsonResponse['transport_fees'] as List;
        for (var fee in transportFeesJson) {
          transportFees.add(TransportFee.fromJson(fee));
        }

        return {
          'grandTotal': grandTotal,
          'feeItems': feeItems,
          'transportFees': transportFees,
        };
      } on FormatException catch (e) {
        throw Exception('Failed to parse fees response: $e');
      }
    } else {
      throw Exception('Failed to load fees: ${response.statusCode}');
    }
  }


  Future<Map<String, dynamic>> getProcessingFees() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final studentId = sharedPreferences.getString(Constants.studentId);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);

    if (apiUrl == null || studentId == null || userId == null ||
        accessToken == null) {
      throw Exception('Required data not found in SharedPreferences');
    }

    final response = await http.post(
      Uri.parse('$apiUrl${Constants.getProcessingfeesUrl}'),
      headers: <String, String>{
        'Content-Type': 'application/json; charset=UTF-8',
        'Client-Service': Constants.clientService,
        'Auth-Key': Constants.authKey,
        'User-ID': userId,
        'Authorization': accessToken,
      },
      body: jsonEncode(<String, String>{
        'student_id': studentId,
      }),
    );

    if (response.statusCode == 200) {
      log('Processing Fees API Response: ${response.body}');
      try {
        final jsonResponse = json.decode(response.body);

        final grandTotal = ProcessingFeeGrandTotal.fromJson(
            jsonResponse['grand_fee']);

        final List<Fee> feeItems = [];
        final studentFee = jsonResponse['student_fee'] as List;
        for (var fee in studentFee) {
          feeItems.add(Fee.fromJson(fee));
        }

        final List<TransportFee> transportFees = [];
        final transportFeesJson = jsonResponse['transport_fees'] as List;
        for (var fee in transportFeesJson) {
          transportFees.add(TransportFee.fromJson(fee));
        }

        return {
          'grandTotal': grandTotal,
          'feeItems': feeItems,
          'transportFees': transportFees,
        };
      } on FormatException catch (e) {
        throw Exception('Failed to parse processing fees response: $e');
      }
    } else {
      throw Exception(
          'Failed to load processing fees: ${response.statusCode}');
    }
  }

    Future<List<OfflinePayment>> getOfflinePayments() async {
      final apiUrl = sharedPreferences.getString(Constants.apiUrl);
      final studentId = sharedPreferences.getString(Constants.studentId);
      final userId = sharedPreferences.getString(Constants.userId);
      final accessToken = sharedPreferences.getString(Constants.accessToken);

      if (apiUrl == null || studentId == null || userId == null ||
          accessToken == null) {
        throw Exception('Required data not found in SharedPreferences');
      }

      final response = await http.post(
        Uri.parse('$apiUrl${Constants.getOfflineBankPayments}'),
        headers: <String, String>{
          'Content-Type': 'application/json; charset=UTF-8',
          'Client-Service': Constants.clientService,
          'Auth-Key': Constants.authKey,
          'User-ID': userId,
          'Authorization': accessToken,
        },
        body: jsonEncode(<String, String>{
          'student_id': studentId,
        }),
      );

      if (response.statusCode == 200) {
        log('Offline Payments API Response: ${response.body}');
        try {
          final jsonResponse = json.decode(response.body);
          final resultArray = jsonResponse['result_array'] as List;
          return resultArray.map((e) => OfflinePayment.fromJson(e)).toList();
        } on FormatException catch (e) {
          throw Exception('Failed to parse offline payments response: $e');
        }
      } else {
        throw Exception(
            'Failed to load offline payments: ${response.statusCode}');
      }
    }
  }

