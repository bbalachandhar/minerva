// minerva/lib/features/notice/data/repositories/notice_repository_impl.dart
import 'dart:convert';
import 'dart:developer';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:intl/intl.dart';

import 'package:minerva_flutter/features/notice/domain/entities/notice_entity.dart';
import 'package:minerva_flutter/features/notice/domain/repositories/notice_repository.dart';
import 'package:minerva_flutter/utils/constants.dart';

class NoticeRepositoryImpl implements NoticeRepository {
  final SharedPreferences sharedPreferences;

  NoticeRepositoryImpl({required this.sharedPreferences});

  @override
  Future<List<NoticeEntity>> getNotices() async {
    final apiUrl = sharedPreferences.getString(Constants.apiUrl);
    final userId = sharedPreferences.getString(Constants.userId);
    final accessToken = sharedPreferences.getString(Constants.accessToken);
    final loginType = sharedPreferences.getString(Constants.loginType);
    final dateFormat = sharedPreferences.getString("dateFormat");

    if (apiUrl == null || userId == null || accessToken == null || loginType == null || dateFormat == null) {
      throw Exception('Missing required authentication or API information for notices.');
    }

    final url = Uri.parse('$apiUrl${Constants.getNotificationsUrl}');
    log('Notice API URL: $url');

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
        'type': loginType,
      }),
    );

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('Notice API Response: $body');

      if (body['success'] == 1) {
        final List<dynamic> data = body['data'];
        final List<NoticeEntity> notices = data.map((json) {
          // Parse and format dates
          final String originalDate = json['date'];
          final String originalPublishDate = json['publish_date'];

          // Assuming original format is "yyyy-MM-dd" from Android code investigation
          final DateFormat inputFormat = DateFormat('yyyy-MM-dd');
          final DateFormat outputFormat = DateFormat(dateFormat); // Use format from SharedPreferences

          DateTime parsedDate = inputFormat.parse(originalDate);
          DateTime parsedPublishDate = inputFormat.parse(originalPublishDate);

          final String formattedDate = outputFormat.format(parsedDate);
          final String formattedPublishDate = outputFormat.format(parsedPublishDate);

          return NoticeEntity(
            id: json['id'] ?? '',
            title: json['title'] ?? '',
            message: json['message'] ?? '',
            date: formattedDate,
            publishDate: formattedPublishDate,
            createdBy: json['created_by'] ?? '',
            employeeId: json['employee_id'] ?? '',
            attachment: json['attachment'] ?? '',
          );
        }).toList();
        return notices;
      } else {
        throw Exception(body['errorMsg'] ?? 'Failed to load notices with unknown error.');
      }
    } else {
      throw Exception('Failed to load notices. Status code: ${response.statusCode}');
    }
  }
}
