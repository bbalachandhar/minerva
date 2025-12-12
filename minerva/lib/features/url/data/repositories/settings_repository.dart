import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'dart:developer';

class SettingsRepository {
  final SharedPreferences sharedPreferences;

  SettingsRepository({required this.sharedPreferences});

  Future<void> validateUrl(String baseUrl) async {
    if (baseUrl.isEmpty) {
      throw Exception('URL cannot be empty');
    }
    
    if (!baseUrl.endsWith('/')) {
      baseUrl += '/';
    }
    final url = Uri.parse('${baseUrl}app');
    log('Validation URL: $url');

    final response = await http.post(url);

    if (response.statusCode == 200) {
      final body = json.decode(response.body);
      log('API Response: $body'); // Log the response body
      
      String responseApiUrl = body['url'] ?? '';
      String responseSiteUrl = body['site_url'] ?? '';

      // Removed debugging logs for raw URLs
      // log('API returned url (raw): $responseApiUrl');
      // log('API returned site_url (raw): $responseSiteUrl');

      // Ensure API URL ends with a single slash
      if (responseApiUrl.isNotEmpty && !responseApiUrl.endsWith('/')) {
        responseApiUrl += '/';
      }
      // Ensure Site URL ends with a single slash
      if (responseSiteUrl.isNotEmpty && !responseSiteUrl.endsWith('/')) {
        responseSiteUrl += '/';
      }
      // Removed debugging log
      // log('responseSiteUrl (after adding trailing slash): $responseSiteUrl');


      // Reinstated: Remove '/api/' from responseSiteUrl if it exists, as uploads are outside api directory
      if (responseSiteUrl.endsWith('/api/')) {
        responseSiteUrl = responseSiteUrl.substring(0, responseSiteUrl.length - 5);
        if (!responseSiteUrl.endsWith('/')) { // Ensure it still ends with a slash after removing /api/
          responseSiteUrl += '/';
        }
      }
      // Removed debugging log
      // log('responseSiteUrl (after /api/ removal): $responseSiteUrl');


      await sharedPreferences.setString(Constants.apiUrl, responseApiUrl);
      await sharedPreferences.setString(Constants.imagesUrl, responseSiteUrl); // Set correctly for images
      await sharedPreferences.setString(Constants.appVer, body['app_ver']);

      final appLogo = body['app_logo'];
      log('app_logo from API: $appLogo');
      if (appLogo != null && appLogo.isNotEmpty) {
        // Construct appLogo URL using the correctly formatted imagesUrl
        await sharedPreferences.setString(Constants.appLogo, responseSiteUrl + "uploads/school_content/logo/app_logo/" + appLogo);
      } else {
        await sharedPreferences.setString(Constants.appLogo, ''); // Set to empty if no logo
      }

      final secColour = body['app_secondary_color_code'];
      final primaryColour = body['app_primary_color_code'];

      if(secColour != null && primaryColour != null && secColour.length == 7 && primaryColour.length == 7 ) {
        await sharedPreferences.setString('secondaryColour', secColour);
        await sharedPreferences.setString('primaryColour', primaryColour);
      } else {
        await sharedPreferences.setString('secondaryColour', Constants.defaultSecondaryColour);
        await sharedPreferences.setString('primaryColour', Constants.defaultPrimaryColour);
      }
      return;
    } else {
      throw Exception('Failed to connect to the server. Status code: ${response.statusCode}');
    }
  }

  String getAppLogo() {
    return sharedPreferences.getString(Constants.appLogo) ?? '';
  }
}
