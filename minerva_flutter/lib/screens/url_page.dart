import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'dart:ui';
import 'dart:io' show Platform;
import 'package:http/http.dart' as http;
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:flutter_animate/flutter_animate.dart';
import 'package:provider/provider.dart';

import '../config/app_config.dart';
import '../utils/session_manager.dart';
import '../services/api_service.dart';
import '../widgets/glass_card.dart';
import '../config/app_theme.dart';
import '../providers/app_config_provider.dart';
import 'login_page.dart';

class UrlPage extends StatefulWidget {
  const UrlPage({super.key});

  @override
  State<UrlPage> createState() => _UrlPageState();
}

class _UrlPageState extends State<UrlPage> {
  final TextEditingController urlController = TextEditingController();
  bool isLoading = false;
  bool isValidUrl = false;
  String validationMessage = '';

  bool get _isMobile => !kIsWeb && (Platform.isAndroid || Platform.isIOS);

  @override
  void initState() {
    super.initState();
    _loadExistingUrl();
  }

  Future<void> _loadExistingUrl() async {
    try {
      final isSessionActive = await SessionManager.isSessionActive();
      if (isSessionActive) {
        final existingUrl = await SessionManager.getSchoolUrl();
        if (existingUrl.isNotEmpty) {
          urlController.text = existingUrl;
          _validateUrlInput(existingUrl);
          await SessionManager.getSessionInfo();
        }
      }
    } catch (e) {}
  }

  void _validateUrlInput(String input) {
    String enteredUrl = input.trim();

    if (enteredUrl.isEmpty) {
      setState(() {
        isValidUrl = false;
        validationMessage = '';
      });
      return;
    }

    if (!enteredUrl.startsWith("http://") &&
        !enteredUrl.startsWith("https://")) {
      setState(() {
        isValidUrl = false;
        validationMessage = 'URL must start with http:// or https://';
      });
      return;
    }

    bool isValid = _isValidUrl(enteredUrl);
    setState(() {
      isValidUrl = isValid;
      if (isValid) {
        validationMessage = '✓ Valid URL format';
      } else {
        validationMessage =
            'Please enter a valid URL format (e.g., https://your-institution.com)';
      }
    });
  }

  Future<void> handleSubmit() async {
    String enteredUrl = urlController.text.trim();

    if (enteredUrl.isEmpty) {
      showError("Please enter your institution URL");
      return;
    }

    if (!enteredUrl.startsWith("http://") &&
        !enteredUrl.startsWith("https://")) {
      showError(
        "URL must start with http:// or https://. Example: https://your-school.com",
      );
      return;
    }

    if (!_isValidUrl(enteredUrl)) {
      showError(
        "Please enter a valid URL format. Example: https://your-school.com",
      );
      return;
    }

    setState(() {
      isLoading = true;
    });

    try {
      bool isUrlValid = await _testUrlConnectivity(enteredUrl);
      if (!isUrlValid) {
        showError(
          "Warning: Could not verify URL connectivity. You can still proceed, but please ensure the URL is correct.",
        );
      }

      final verificationResult = await ApiService.verifySchoolRegistration(
        enteredUrl,
      );
      final isVerified = verificationResult['isVerified'] == true;
      final isNetworkError = verificationResult['isNetworkError'] == true;
      final bootstrapData =
          verificationResult['bootstrapData'] is Map<String, dynamic>
          ? verificationResult['bootstrapData'] as Map<String, dynamic>
          : null;

      if (!isVerified && !isNetworkError) {
        final verificationMessage =
            verificationResult['message'] ??
            'You are not a registered member. Please register your institution to continue.';

        showError(verificationMessage);
        setState(() {
          isLoading = false;
        });
        return;
      }

      if (!isVerified && isNetworkError) {
        // Network error during verification — save the URL and proceed;
        // the user will see a proper error at login if the URL is wrong.
        showError('Unable to connect at the moment, please try again later');
      }

      String urlToSave =
          bootstrapData?['site_url']?.toString().trim().isNotEmpty == true
          ? bootstrapData!['site_url'].toString().trim()
          : enteredUrl;

      if (urlToSave.endsWith('/')) {
        urlToSave = urlToSave.substring(0, urlToSave.length - 1);
      }

      final urlSaved = await AppConfig.setBaseUrl(urlToSave);
      if (!urlSaved) {
        showError("Failed to save URL. Please try again.");
        return;
      }

      if (bootstrapData != null) {
        await AppConfig.saveAppConfiguration(bootstrapData);
      }

      final sessionInitialized = await SessionManager.initializeSession(
        urlToSave,
      );

      try {
        final appConfig = await ApiService.getAppConfiguration();
        await AppConfig.saveAppConfiguration(appConfig);
        await AppConfig.getAppLogo();

        // Update provider colors immediately so the login screen and theme
        // reflect the institution's brand colors without waiting for a reload.
        if (mounted) {
          Provider.of<AppConfigProvider>(
            context,
            listen: false,
          ).refreshColors();
        }

        showSuccess("URL configured successfully! App settings loaded.");
        _navigateToLogin();
      } catch (configError) {
        showSuccess(
          "URL configured successfully! You can now proceed to login.",
        );
        _navigateToLogin();
      }
    } catch (e) {
      showError("Connection error. Please check the URL and try again.");
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  void _navigateToLogin() {
    Navigator.pushReplacement(
      context,
      PageRouteBuilder(
        pageBuilder: (_, __, ___) => const LoginPageUI(),
        transitionsBuilder: (_, a, __, c) =>
            FadeTransition(opacity: a, child: c),
        transitionDuration: const Duration(milliseconds: 600),
      ),
    );
  }

  bool _isValidUrl(String url) {
    try {
      final uri = Uri.parse(url);
      if (!uri.hasScheme || (uri.scheme != 'http' && uri.scheme != 'https'))
        return false;
      if (uri.host.isEmpty) return false;
      if (!uri.host.contains('.')) return false;
      if (uri.host.endsWith('.')) return false;
      if (uri.host.startsWith('.')) return false;
      final domainPattern = RegExp(
        r'^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$',
      );
      if (!domainPattern.hasMatch(uri.host)) return false;
      if (uri.port != 0 && (uri.port < 1 || uri.port > 65535)) return false;
      return true;
    } catch (e) {
      return false;
    }
  }

  Future<bool> _testUrlConnectivity(String url) async {
    try {
      final testEndpoints = [
        '$url/api/test',
        '$url/api/webservice/test',
        '$url/app',
        '$url/api/webservice/getlessonplan',
        url,
      ];

      for (String endpoint in testEndpoints) {
        try {
          final uri = Uri.parse(endpoint);
          var response = await http
              .get(
                uri,
                headers: {
                  'Content-Type': 'application/json',
                  'Auth-Key': AppConfig.authKey,
                  'Client-Service': AppConfig.clientService,
                },
              )
              .timeout(const Duration(seconds: 10));

          if (response.statusCode >= 200 && response.statusCode < 600)
            return true;

          if (response.statusCode == 405) {
            response = await http
                .post(
                  uri,
                  headers: {
                    'Content-Type': 'application/json',
                    'Auth-Key': AppConfig.authKey,
                    'Client-Service': AppConfig.clientService,
                  },
                  body: '{}',
                )
                .timeout(const Duration(seconds: 10));

            if (response.statusCode >= 200 && response.statusCode < 600)
              return true;
          }
        } catch (e) {
          continue;
        }
      }
      try {
        final uri = Uri.parse(url);
        final response = await http
            .get(uri)
            .timeout(const Duration(seconds: 5));
        if (response.statusCode >= 200 && response.statusCode < 600)
          return true;
      } catch (e) {}
      return false;
    } catch (e) {
      return false;
    }
  }

  void showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.redAccent.shade400,
      ),
    );
  }

  void showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: Colors.teal.shade500),
    );
  }

  Future<void> _scanQrCode() async {
    if (!mounted) return;
    final String? scannedUrl = await Navigator.push<String>(
      context,
      MaterialPageRoute(
        fullscreenDialog: true,
        builder: (context) => const _QrScannerScreen(),
      ),
    );
    if (scannedUrl != null && scannedUrl.isNotEmpty && mounted) {
      urlController.text = scannedUrl;
      _validateUrlInput(scannedUrl);
      await Future.delayed(const Duration(milliseconds: 600));
      if (mounted) await handleSubmit();
    }
  }

  @override
  void dispose() {
    urlController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final size = MediaQuery.of(context).size;
    final isTablet = screenWidth >= 600;
    final hPad = isTablet ? screenWidth * 0.12 : 4.0;

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      resizeToAvoidBottomInset: true,
      body: Container(
        decoration: BoxDecoration(color: context.bgColor),
        child: Stack(
          children: [
            // Background Blobs
            Positioned(
                  top: size.height * -0.05,
                  left: size.width * -0.2,
                  child: ImageFiltered(
                    imageFilter: ImageFilter.blur(sigmaX: 70, sigmaY: 70),
                    child: Container(
                      width: size.width * 0.7,
                      height: size.width * 0.7,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: Theme.of(
                          context,
                        ).primaryColor.withValues(alpha: isDark ? 0.2 : 0.3),
                      ),
                    ),
                  ),
                )
                .animate()
                .fade(duration: 1000.ms)
                .scale(begin: const Offset(0.8, 0.8)),

            Positioned(
                  bottom: size.height * -0.1,
                  right: size.width * -0.1,
                  child: ImageFiltered(
                    imageFilter: ImageFilter.blur(sigmaX: 80, sigmaY: 80),
                    child: Container(
                      width: size.width * 0.6,
                      height: size.width * 0.6,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: const Color(
                          0xFF00BFA5,
                        ).withValues(alpha: isDark ? 0.15 : 0.25),
                      ),
                    ),
                  ),
                )
                .animate()
                .fade(duration: 1000.ms, delay: 200.ms)
                .scale(begin: const Offset(0.8, 0.8)),

            SafeArea(
              child: Column(
                children: [
                  _buildHeader(
                    isTablet,
                  ).animate().fade(duration: 600.ms).slideY(begin: -0.2),
                  Expanded(
                    child: SingleChildScrollView(
                      physics: const BouncingScrollPhysics(),
                      child: Center(
                        child: ConstrainedBox(
                          constraints: BoxConstraints(
                            maxWidth: isTablet ? 520.0 : double.infinity,
                          ),
                          child: GlassCard(
                            margin: EdgeInsets.symmetric(horizontal: hPad),
                            padding: const EdgeInsets.all(24),
                            isDarkTheme: isDark,
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.stretch,
                              children: [
                                _buildHeroCard(isTablet)
                                    .animate()
                                    .fade(duration: 700.ms, delay: 200.ms)
                                    .scale(curve: Curves.easeOutBack),
                                const SizedBox(height: 32),

                                _buildSectionLabel('INSTITUTION URL')
                                    .animate()
                                    .fade(duration: 500.ms, delay: 400.ms)
                                    .slideX(begin: -0.1),
                                const SizedBox(height: 10),

                                _buildUrlField(isTablet)
                                    .animate()
                                    .fade(duration: 500.ms, delay: 500.ms)
                                    .slideY(begin: 0.1),
                                const SizedBox(height: 16),

                                if (_isMobile) ...[
                                  _buildScanQrButton(isTablet)
                                      .animate()
                                      .fade(duration: 500.ms, delay: 600.ms)
                                      .slideY(begin: 0.1),
                                  const SizedBox(height: 24),
                                ],

                                _buildConnectButton(isTablet)
                                    .animate()
                                    .fade(duration: 500.ms, delay: 700.ms)
                                    .slideY(begin: 0.2),
                                const SizedBox(height: 24),

                                _buildInfoCard(isTablet).animate().fade(
                                  duration: 600.ms,
                                  delay: 800.ms,
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                  _buildFooter(
                    isTablet,
                  ).animate().fade(duration: 800.ms, delay: 900.ms),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── components ─────────────────────────────────────────────────────────────

  Widget _buildHeader(bool isTablet) {
    return Padding(
      padding: EdgeInsets.fromLTRB(
        isTablet ? 28.0 : 20.0,
        14,
        isTablet ? 28.0 : 20.0,
        10,
      ),
      child: Center(
        child: Text(
          'MINERVA',
          style: TextStyle(
            fontFamily: 'Inter',
            color: context.primaryText,
            fontSize: isTablet ? 26.0 : 22.0,
            fontWeight: FontWeight.bold,
            letterSpacing: 3.0,
          ),
        ),
      ),
    );
  }

  Widget _buildHeroCard(bool isTablet) {
    final logoSize = isTablet ? 140.0 : 120.0;
    return Center(
      child:
          Container(
                width: logoSize,
                height: logoSize,
                decoration: BoxDecoration(
                  color: context.isDark
                      ? Colors.white.withValues(alpha: 0.1)
                      : Colors.white.withValues(alpha: 0.8),
                  borderRadius: BorderRadius.circular(28),
                  boxShadow: [
                    BoxShadow(
                      color: Theme.of(
                        context,
                      ).primaryColor.withValues(alpha: 0.15),
                      blurRadius: 30,
                      offset: const Offset(0, 15),
                    ),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Image.asset(
                    'assets/images/minerva_logo.png',
                    fit: BoxFit.contain,
                    errorBuilder: (context, error, stackTrace) => Icon(
                      Icons.menu_book_rounded,
                      size: isTablet ? 54.0 : 44.0,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                ),
              )
              .animate(onPlay: (controller) => controller.repeat(reverse: true))
              .scale(
                begin: const Offset(1.0, 1.0),
                end: const Offset(1.05, 1.05),
                duration: 2.seconds,
                curve: Curves.easeInOut,
              ),
    );
  }

  Widget _buildSectionLabel(String label) {
    return Text(
      label,
      style: TextStyle(
        fontFamily: 'Inter',
        color: Theme.of(context).primaryColor,
        fontSize: 12,
        fontWeight: FontWeight.bold,
        letterSpacing: 1.5,
      ),
    );
  }

  Widget _buildUrlField(bool isTablet) {
    final height = isTablet ? 56.0 : 54.0;
    final fontSize = isTablet ? 15.5 : 15.0;

    return Container(
      height: height,
      decoration: BoxDecoration(
        color: context.isDark
            ? Colors.white.withValues(alpha: 0.05)
            : Colors.white.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: context.isDark
              ? Colors.white.withValues(alpha: 0.1)
              : Colors.white.withValues(alpha: 0.5),
        ),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Icon(
            Icons.language_rounded,
            color: context.primaryText.withValues(alpha: 0.5),
            size: 22,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: TextField(
              controller: urlController,
              keyboardType: TextInputType.url,
              textInputAction: TextInputAction.done,
              onChanged: _validateUrlInput,
              cursorColor: Theme.of(context).primaryColor,
              style: TextStyle(
                fontFamily: 'Inter',
                fontSize: fontSize,
                color: context.primaryText,
                fontWeight: FontWeight.w500,
              ),
              decoration: InputDecoration(
                hintText: 'https://yourdomain/api/',
                border: InputBorder.none,
                filled: false,
                hintStyle: TextStyle(
                  fontFamily: 'Inter',
                  color: context.primaryText.withValues(alpha: 0.3),
                  fontSize: fontSize,
                ),
                contentPadding: EdgeInsets.zero,
              ),
            ),
          ),
          if (isValidUrl)
            Icon(
              Icons.check_circle_rounded,
              color: Colors.teal.shade400,
              size: 20,
            ).animate().scale(curve: Curves.elasticOut),
        ],
      ),
    );
  }

  Widget _buildScanQrButton(bool isTablet) {
    final height = isTablet ? 52.0 : 50.0;
    final fontSize = isTablet ? 15.5 : 14.5;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: isLoading ? null : _scanQrCode,
        child: Container(
          height: height,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: Theme.of(context).primaryColor.withValues(alpha: 0.3),
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.qr_code_scanner_rounded,
                color: Theme.of(context).primaryColor,
                size: 20,
              ),
              const SizedBox(width: 10),
              Text(
                'Scan QR Code',
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontSize: fontSize,
                  fontWeight: FontWeight.w600,
                  color: Theme.of(context).primaryColor,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildConnectButton(bool isTablet) {
    final height = isTablet ? 56.0 : 54.0;
    final fontSize = isTablet ? 16.0 : 16.0;
    final isEnabled = !isLoading && isValidUrl;

    return Container(
      height: height,
      decoration: BoxDecoration(
        color: isEnabled
            ? Theme.of(context).primaryColor
            : context.primaryText.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(16),
        boxShadow: isEnabled
            ? [
                BoxShadow(
                  color: Theme.of(context).primaryColor.withValues(alpha: 0.4),
                  blurRadius: 16,
                  offset: const Offset(0, 8),
                ),
              ]
            : null,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: isEnabled ? handleSubmit : null,
          child: Center(
            child: isLoading
                ? const SizedBox(
                    height: 24,
                    width: 24,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                  )
                : Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Connect to Institution',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          color: isEnabled
                              ? Colors.white
                              : context.primaryText.withValues(alpha: 0.4),
                          fontSize: fontSize,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Icon(
                        Icons.arrow_forward_rounded,
                        color: isEnabled
                            ? Colors.white
                            : context.primaryText.withValues(alpha: 0.4),
                        size: 20,
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard(bool isTablet) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: context.isDark
            ? Colors.white.withValues(alpha: 0.04)
            : Theme.of(context).primaryColor.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.info_outline_rounded,
            color: Theme.of(context).primaryColor,
            size: 20,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'We verify and store the URL securely for future sessions. Ensure it starts with https://',
              style: TextStyle(
                fontFamily: 'Inter',
                color: context.primaryText.withValues(alpha: 0.7),
                fontSize: 13,
                height: 1.5,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFooter(bool isTablet) {
    final year = DateTime.now().year;
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Center(
        child: Text(
          '© All Rights Reserved Beebasoft - $year',
          style: TextStyle(
            fontFamily: 'Inter',
            color: context.primaryText.withValues(alpha: 0.3),
            fontSize: 10,
            fontWeight: FontWeight.bold,
            letterSpacing: 2.0,
          ),
        ),
      ),
    );
  }
}

// ---------------------------------------------------------------------------
// QR Scanner screen — opened as a fullscreen dialog from UrlPage
// ---------------------------------------------------------------------------

class _QrScannerScreen extends StatefulWidget {
  const _QrScannerScreen();

  @override
  State<_QrScannerScreen> createState() => _QrScannerScreenState();
}

class _QrScannerScreenState extends State<_QrScannerScreen> {
  bool _hasScanned = false;

  void _onDetect(BarcodeCapture capture) {
    if (_hasScanned) return;
    for (final barcode in capture.barcodes) {
      final raw = (barcode.rawValue ?? '').trim();
      if (raw.startsWith('http://') || raw.startsWith('https://')) {
        _hasScanned = true;
        Navigator.of(context).pop(raw);
        return;
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    const frameSize = 250.0;
    final verticalPad = (size.height - frameSize) / 2;

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        title: Text(
          'Scan Institution QR Code',
          style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w600),
        ),
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.white,
        leading: const BackButton(color: Colors.white),
      ),
      body: Stack(
        fit: StackFit.expand,
        children: [
          MobileScanner(onDetect: _onDetect),
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: verticalPad,
            child: const ColoredBox(color: Color(0x88000000)),
          ),
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            height: verticalPad,
            child: const ColoredBox(color: Color(0x88000000)),
          ),
          Positioned(
            top: verticalPad,
            left: 0,
            width: (size.width - frameSize) / 2,
            height: frameSize,
            child: const ColoredBox(color: Color(0x88000000)),
          ),
          Positioned(
            top: verticalPad,
            right: 0,
            width: (size.width - frameSize) / 2,
            height: frameSize,
            child: const ColoredBox(color: Color(0x88000000)),
          ),
          Center(
            child: Container(
              width: frameSize,
              height: frameSize,
              decoration: BoxDecoration(
                border: Border.all(color: Colors.tealAccent, width: 3),
                borderRadius: BorderRadius.circular(24),
              ),
            ),
          ).animate().scale(curve: Curves.easeOutBack, duration: 600.ms),
          Positioned(
            bottom: 60,
            left: 0,
            right: 0,
            child: Column(
              children: [
                const Icon(Icons.qr_code_scanner, color: Colors.white, size: 32)
                    .animate(onPlay: (c) => c.repeat())
                    .shimmer(duration: 2.seconds),
                const SizedBox(height: 16),
                Text(
                  'Align the QR code within the frame',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontFamily: 'Inter',
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'URL will be captured and verified automatically',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontFamily: 'Inter',
                    color: Colors.white70,
                    fontSize: 13,
                  ),
                ),
              ],
            ).animate().fade(delay: 400.ms).slideY(begin: 0.2),
          ),
        ],
      ),
    );
  }
}
