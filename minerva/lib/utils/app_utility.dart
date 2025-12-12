// minerva/lib/utils/app_utility.dart

import 'package:shared_preferences/shared_preferences.dart';
import 'package:minerva_flutter/utils/app_constants.dart';

class AppUtility {
  static Future<String> getSharedPreference(String key) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString(key) ?? '';
  }

  static Future<String> getPrimaryColour() async {
    return await getSharedPreference(AppConstants.primaryColourKey);
  }

  static Future<String> getSecondaryColour() async {
    return await getSharedPreference(AppConstants.secondaryColourKey);
  }

  static Future<String> getImagesUrl() async {
    return await getSharedPreference(AppConstants.imagesUrlKey);
  }
  // You can add more utility methods here as needed for other shared preferences
}
