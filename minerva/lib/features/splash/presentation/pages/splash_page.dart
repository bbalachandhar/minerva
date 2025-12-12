import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/url/data/repositories/settings_repository.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/utils/constants.dart';

class SplashPage extends StatefulWidget {
  const SplashPage({Key? key}) : super(key: key);

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage> {
  String? appLogoUrl;

  @override
  void initState() {
    super.initState();
    _initializeApp();
  }

  Future<void> _initializeApp() async {
    final sharedPreferences = await SharedPreferences.getInstance();
    final settingsRepository = RepositoryProvider.of<SettingsRepository>(context);

    await Future.delayed(const Duration(seconds: 1)); // Simulate initial loading

    final storedBaseUrl = sharedPreferences.getString('base_url');

    if (storedBaseUrl == null) {
      context.go('/url');
    } else {
      try {
        await settingsRepository.validateUrl(storedBaseUrl);
        // After successful validation, update logo and navigate
        setState(() {
          appLogoUrl = settingsRepository.getAppLogo();
        });
        context.go('/login');
      } catch (e) {
        // If validation fails for a stored URL, navigate to URL page to re-enter
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Stored URL invalid: ${e.toString()}. Please re-enter.')),
        );
        context.go('/url');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            if (appLogoUrl != null && appLogoUrl!.isNotEmpty)
              Image.network(
                appLogoUrl!,
                height: 150, // Adjust height as needed
              )
            else
              Image.asset(
                'assets/splash_logo.png', // Fallback to local asset
                height: 150,
              ),
            const SizedBox(height: 20),
            const CircularProgressIndicator(),
          ],
        ),
      ),
    );
  }
}
