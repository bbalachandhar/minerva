import 'package:flutter/material.dart';
import 'package:minerva_flutter/app/app.dart';
import 'package:shared_preferences/shared_preferences.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final sharedPreferences = await SharedPreferences.getInstance();
  runApp(App(sharedPreferences: sharedPreferences));
}
