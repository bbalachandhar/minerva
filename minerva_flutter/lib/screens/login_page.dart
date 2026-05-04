import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_animate/flutter_animate.dart';

import '../config/app_config.dart';
import '../utils/url_manager.dart';
import 'dashboard_page.dart';
import 'forgot_password_page.dart';
import '../services/auth_service.dart';
import '../services/notification_service.dart';
import '../widgets/child_selection_dialog.dart';
import '../widgets/glass_card.dart';
import '../config/app_theme.dart';
import 'dart:convert';
import 'dart:ui';
import 'package:url_launcher/url_launcher.dart';
import 'package:provider/provider.dart';
import '../providers/app_config_provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'privacy_policy_page.dart';

class LoginPageUI extends StatefulWidget {
  const LoginPageUI({super.key});

  @override
  State<LoginPageUI> createState() => _LoginPageUIState();
}

class _LoginPageUIState extends State<LoginPageUI> {
  final TextEditingController usernameController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  static const String _rememberMeKey = 'remember_me';
  static const String _rememberedUsernameKey = 'remembered_username';
  static const String _rememberedPasswordKey = 'remembered_password';
  bool isLoading = false;
  bool _obscurePassword = true;
  bool _rememberMe = false;
  String schoolName = '';
  String schoolLogo = '';

  String _roleLabel(String role) {
    if (role.isEmpty) return 'User';
    return role[0].toUpperCase() + role.substring(1).toLowerCase();
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ScaffoldMessenger.of(context).clearSnackBars();
    });
    loadSchoolInfo();
    _clearInvalidTokens();
    _loadRememberedCredentials();
  }

  @override
  void dispose() {
    usernameController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
  }

  Future<void> _clearInvalidTokens() async {
    try {
      final isLoggedIn = await AuthService.isLoggedIn();
      if (!isLoggedIn) {
        // Logic to clear tokens if needed
      }
    } catch (e) {
      // ignore
    }
  }

  Future<void> _loadRememberedCredentials() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final remember = prefs.getBool(_rememberMeKey) ?? false;
      final rememberedUsername = prefs.getString(_rememberedUsernameKey) ?? '';
      final rememberedPassword = prefs.getString(_rememberedPasswordKey) ?? '';

      if (!mounted) return;
      setState(() {
        _rememberMe = remember;
        if (remember) {
          usernameController.text = rememberedUsername;
          passwordController.text = rememberedPassword;
        }
      });
    } catch (_) {
      // Ignore remember-me read errors.
    }
  }

  Future<void> _persistRememberMe() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_rememberMeKey, _rememberMe);

    if (_rememberMe) {
      await prefs.setString(
        _rememberedUsernameKey,
        usernameController.text.trim(),
      );
      await prefs.setString(
        _rememberedPasswordKey,
        passwordController.text.trim(),
      );
    } else {
      await prefs.remove(_rememberedUsernameKey);
      await prefs.remove(_rememberedPasswordKey);
    }
  }

  Future<void> loadSchoolInfo() async {
    try {
      final appConfigProvider = Provider.of<AppConfigProvider>(
        context,
        listen: false,
      );

      // 1. Use provider in-memory config first (fastest, already loaded)
      // If the startup load already failed with a network error, redirect immediately.
      if (appConfigProvider.isNetworkError) {
        if (kDebugMode)
          debugPrint(
            '[LoginPage] Stale URL detected — redirecting to URL page',
          );
        await AppConfig.setBaseUrl('');
        if (mounted) Navigator.pushReplacementNamed(context, '/url');
        return;
      }

      String resolvedLogo = appConfigProvider.appLogo;
      String resolvedName = appConfigProvider.schoolName;

      // 2. Fall back to SharedPrefs if provider has nothing
      if (resolvedLogo.isEmpty) {
        resolvedLogo = await AppConfig.getAppLogo();
      }
      if (resolvedName.isEmpty || resolvedName == 'minerva') {
        final saved = await AppConfig.getSchoolName();
        if (saved.isNotEmpty) resolvedName = saved;
      }

      // Evict any stale cached logo so the server's latest image is always used
      if (resolvedLogo.isNotEmpty) {
        await CachedNetworkImage.evictFromCache(resolvedLogo);
      }

      if (mounted) {
        setState(() {
          schoolLogo = resolvedLogo;
          schoolName = resolvedName.isNotEmpty ? resolvedName : 'minerva';
        });
      }

      // 3. If still empty, fetch from API once (provider not yet initialized)
      if (resolvedLogo.isEmpty) {
        await appConfigProvider.loadAppConfig();

        // If the server was unreachable, the saved URL is stale — send user
        // back to URL page to enter the correct address.
        if (appConfigProvider.isNetworkError) {
          if (kDebugMode)
            debugPrint(
              '[LoginPage] Network error — clearing stale URL and redirecting to URL page',
            );
          await AppConfig.setBaseUrl('');
          if (mounted) {
            Navigator.pushReplacementNamed(context, '/url');
          }
          return;
        }

        final freshLogo = appConfigProvider.appLogo;
        final freshName = appConfigProvider.schoolName;
        if (kDebugMode) {
          debugPrint('[LoginPage] Logo from API: $freshLogo');
        }
        if (freshLogo.isNotEmpty) {
          await CachedNetworkImage.evictFromCache(freshLogo);
        }
        if (mounted) {
          setState(() {
            if (freshLogo.isNotEmpty) schoolLogo = freshLogo;
            if (freshName.isNotEmpty) schoolName = freshName;
          });
        }
      }
    } catch (e) {
      if (kDebugMode) debugPrint('[LoginPage] loadSchoolInfo error: $e');
    }
  }

  Future<void> _redirectToUrlPage() async {
    await AppConfig.setBaseUrl('');
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text(
          'Cannot reach the server. Please check your institution URL and try again.',
        ),
        backgroundColor: Colors.orange,
        duration: Duration(seconds: 3),
      ),
    );
    await Future.delayed(const Duration(seconds: 1));
    if (mounted) Navigator.pushReplacementNamed(context, '/url');
  }

  Future<void> loginUser() async {
    if (usernameController.text.trim().isEmpty ||
        passwordController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Please enter both username and password'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() {
      isLoading = true;
    });

    // Show loading message
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Attempting login...'),
          backgroundColor: Colors.blue,
          duration: Duration(seconds: 2),
        ),
      );
    }

    try {
      // Get base URL from common URL manager
      final baseUrl = await UrlManager.getBaseUrl();

      String loginEndpoint;
      if (baseUrl.isEmpty) {
        //
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Please configure your institution URL first'),
              backgroundColor: Colors.red,
              duration: Duration(seconds: 4),
            ),
          );
        }
        return;
      } else {
        loginEndpoint = '$baseUrl/api/webservice/login';
      }

      // Try multiple login endpoints if the first one fails
      final loginEndpoints = [
        loginEndpoint,
        '$baseUrl/api/webservice/auth/login',
        '$baseUrl/api/auth/login',
        '$baseUrl/api/login',
        '$baseUrl/auth/login',
        '$baseUrl/login',
      ];

      // Track whether every failure was a network error (vs a real server response)
      bool allNetworkErrors = true;

      for (int i = 0; i < loginEndpoints.length; i++) {
        final endpoint = loginEndpoints[i];
        //

        try {
          final response = await http
              .post(
                Uri.parse(endpoint),
                headers: {
                  'Auth-Key': AppConfig.authKey,
                  'Client-Service': AppConfig.clientService,
                  'Content-Type': AppConfig.contentType,
                  'User-Agent': 'minerva-Mobile/1.0',
                  'Accept': 'application/json',
                  'Connection': 'keep-alive',
                },
                body: jsonEncode({
                  'username': usernameController.text.trim(),
                  'password': passwordController.text.trim(),
                  'deviceToken': await AppConfig.getDeviceToken(),
                }),
              )
              .timeout(AppConfig.requestTimeout);

          if (response.statusCode == 200) {
            // Check if response is HTML (error page)
            if (response.body.contains('<html>') ||
                response.body.contains('<!DOCTYPE')) {
              continue;
            }

            try {
              final responseData = jsonDecode(response.body);
              if (responseData['status'] == 1) {
                await _persistRememberMe();
                await _saveUserData(responseData);

                // Pull primary/secondary colors from /api/app right after login
                // so the dashboard theme reflects the institution's configured brand colors.
                // ignore: use_build_context_synchronously
                Provider.of<AppConfigProvider>(
                  context,
                  listen: false,
                ).loadAppConfig();

                // Explicitly register device token for notifications after login
                NotificationService.registerCurrentToken(
                  force: true,
                ).catchError((e) => {});

                if (mounted) {
                  final record = responseData['record'];
                  final String role = (responseData['role'] ?? '')
                      .toString()
                      .toLowerCase();

                  List<dynamic>? children;
                  if (record is Map && record['parent_childs'] != null) {
                    children = record['parent_childs'];
                  } else if (record is List) {
                    // In some API versions, record itself is the child list
                    children = record;
                  }

                  if (role == 'parent' &&
                      children != null &&
                      children.isNotEmpty) {
                    if (children.length > 1) {
                      // Show child selection dialog for parents with multiple children
                      showDialog(
                        context: context,
                        barrierDismissible: false,
                        builder: (context) => ChildSelectionDialog(
                          children: List<Map<String, dynamic>>.from(children!),
                          onChildSelected: (child) async {
                            try {
                              Navigator.pop(context); // Close dialog
                              await AuthService.switchChild(child);

                              if (mounted) {
                                ScaffoldMessenger.of(context).showSnackBar(
                                  const SnackBar(
                                    content: Text('Login successful!'),
                                    backgroundColor: Colors.green,
                                    duration: Duration(seconds: 1),
                                  ),
                                );

                                Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const DashboardPage(),
                                  ),
                                );
                              }
                            } catch (e) {
                              if (mounted) {
                                Navigator.pushReplacement(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => const DashboardPage(),
                                  ),
                                );
                              }
                            }
                          },
                        ),
                      );
                    } else {
                      // Auto-select the only child for parents with one child
                      final child = Map<String, dynamic>.from(children[0]);
                      await AuthService.switchChild(child);

                      if (mounted) {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Login successful!'),
                            backgroundColor: Colors.green,
                            duration: Duration(seconds: 1),
                          ),
                        );

                        Navigator.pushReplacement(
                          context,
                          MaterialPageRoute(
                            builder: (context) => const DashboardPage(),
                          ),
                        );
                      }
                    }
                  } else if (role == 'parent') {
                    // Parent login without children - technically an error state or different API version
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          'Login failed: No children found for this parent account.',
                        ),
                        backgroundColor: Colors.red,
                        duration: Duration(seconds: 4),
                      ),
                    );
                  } else {
                    // Standard login flow
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                          'Login successful as ${_roleLabel(role)}!',
                        ),
                        backgroundColor: Colors.green,
                        duration: Duration(seconds: 2),
                      ),
                    );

                    Navigator.pushReplacement(
                      context,
                      MaterialPageRoute(
                        builder: (context) => const DashboardPage(),
                      ),
                    );
                  }
                }
                return;
              } else {
                final errorMessage = responseData['message'] ?? 'Login failed';
                allNetworkErrors = false; // Server responded — URL is reachable
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(errorMessage),
                      backgroundColor: Colors.red,
                      duration: const Duration(seconds: 4),
                    ),
                  );
                }
                return;
              }
            } catch (parseError) {
              allNetworkErrors =
                  false; // Got a response, just couldn't parse it
              continue;
            }
          } else {
            allNetworkErrors =
                false; // Server responded with non-200 — URL is reachable
            continue;
          }
        } catch (e) {
          // Network-level error (socket, timeout) — keep allNetworkErrors = true
          continue;
        }
      }

      // If all endpoints failed
      if (allNetworkErrors) {
        // Server was completely unreachable — stale URL, redirect to URL page
        await _redirectToUrlPage();
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'Login failed. Please check your credentials and try again.',
            ),
            backgroundColor: Colors.red,
            duration: Duration(seconds: 4),
          ),
        );
      }
    } on http.ClientException catch (_) {
      await _redirectToUrlPage();
    } catch (e) {
      final errorStr = e.toString();
      final isNetworkFailure =
          errorStr.contains('SocketException') ||
          errorStr.contains('Connection refused') ||
          errorStr.contains('TimeoutException') ||
          errorStr.contains('Timeout') ||
          errorStr.contains('Failed host lookup') ||
          errorStr.contains('Network is unreachable');

      if (isNetworkFailure) {
        await _redirectToUrlPage();
      } else if (mounted) {
        String errorMessage = errorStr.contains('HandshakeException')
            ? 'Security error (SSL). Please check your internet.'
            : 'Login failed. Please try again.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMessage),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() {
          isLoading = false;
        });
      }
    }
  }

  Future<void> _saveUserData(Map<String, dynamic> data) async {
    final prefs = await SharedPreferences.getInstance();

    final token = data['token'] ?? '';
    final userId = data['id'] ?? '';

    // Save token to both keys for compatibility
    await prefs.setString('token', token); // 'token' - for AuthService
    await prefs.setString('auth_token', token); // 'auth_token' - for AppConfig
    await prefs.setString('user_id', userId);
    await prefs.setString('role', data['role'] ?? '');
    await prefs.setBool('isLoggedIn', true);

    // CRITICAL: Save full login_data JSON for AuthService.switchChild and DashboardPage
    await prefs.setString('login_data', jsonEncode(data));

    if (data['record'] != null && data['record'] is Map) {
      final record = data['record'];

      final firstName = (record['firstname'] ?? '').toString().trim();
      final middleName = (record['middlename'] ?? '').toString().trim();
      final lastName = (record['lastname'] ?? '').toString().trim();
      final joinedName = [
        firstName,
        middleName,
        lastName,
      ].where((part) => part.isNotEmpty).join(' ').trim();
      final displayName = joinedName.isNotEmpty
          ? joinedName
          : ((record['name'] ?? record['username'] ?? '').toString().trim());

      await prefs.setString('student_name', displayName);
      await prefs.setString('email', record['email'] ?? '');
      await prefs.setString('class', record['class'] ?? '');
      await prefs.setString('image', record['image'] ?? '');
      await prefs.setString('admission_no', record['admission_no'] ?? '');
      await prefs.setString('section', record['section'] ?? '');
      await prefs.setString(
        'student_id',
        record['student_id']?.toString() ?? '',
      );
      await prefs.setString(
        'student_session_id',
        record['student_session_id']?.toString() ?? '',
      );

      // Save parent children if available as legacy key
      if (record['parent_childs'] != null) {
        await prefs.setString(
          'parent_childs',
          jsonEncode(record['parent_childs']),
        );
      }
    } else if (data['record'] is List) {
      // Save parent children list directly if record is a list
      await prefs.setString('parent_childs', jsonEncode(data['record']));
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final primaryColor = Provider.of<AppConfigProvider>(
      context,
    ).primaryColorObj;

    return Scaffold(
      resizeToAvoidBottomInset: true,
      backgroundColor: context.bgColor,
      body: Stack(
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
                      color: primaryColor.withValues(alpha: isDark ? 0.2 : 0.3),
                    ),
                  ),
                ),
              )
              .animate()
              .fade(duration: 1000.ms)
              .scale(begin: const Offset(0.8, 0.8)),

          Positioned(
                bottom: size.height * 0.1,
                right: size.width * -0.1,
                child: ImageFiltered(
                  imageFilter: ImageFilter.blur(sigmaX: 80, sigmaY: 80),
                  child: Container(
                    width: size.width * 0.6,
                    height: size.width * 0.6,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: primaryColor.withValues(
                        alpha: isDark ? 0.15 : 0.25,
                      ),
                    ),
                  ),
                ),
              )
              .animate()
              .fade(duration: 1000.ms, delay: 200.ms)
              .scale(begin: const Offset(0.8, 0.8)),

          // Small blob behind "Remember Me" area
          Positioned(
                top: size.height * 0.62,
                left: size.width * 0.08,
                child: ImageFiltered(
                  imageFilter: ImageFilter.blur(sigmaX: 40, sigmaY: 40),
                  child: Container(
                    width: size.width * 0.3,
                    height: size.width * 0.3,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: primaryColor.withValues(
                        alpha: isDark ? 0.12 : 0.18,
                      ),
                    ),
                  ),
                ),
              )
              .animate()
              .fade(duration: 1200.ms, delay: 400.ms)
              .scale(begin: const Offset(0.5, 0.5)),

          SafeArea(
            child: LayoutBuilder(
              builder: (context, constraints) {
                final screenWidth = constraints.maxWidth;
                final screenHeight = constraints.maxHeight;
                final isLandscape = screenWidth > screenHeight;
                final isTablet = screenWidth >= 600;
                final isTabletOrLandscape = isTablet || isLandscape;

                final spacingSmall = (screenHeight * 0.015).clamp(8.0, 16.0);
                final spacingMedium = (screenHeight * 0.03).clamp(16.0, 32.0);
                final horizontalPadding = isTabletOrLandscape
                    ? screenWidth * 0.15
                    : screenWidth * 0.06;
                final formMaxWidth = isTabletOrLandscape
                    ? 450.0
                    : screenWidth * 0.9;

                return Center(
                  child: SingleChildScrollView(
                    physics: const BouncingScrollPhysics(),
                    padding: EdgeInsets.symmetric(
                      horizontal: horizontalPadding,
                      vertical: spacingSmall,
                    ),
                    child: ConstrainedBox(
                      constraints: BoxConstraints(maxWidth: formMaxWidth),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          _buildBrandHeader(isTabletOrLandscape, screenHeight),
                          SizedBox(height: spacingMedium),
                          GlassCard(
                            padding: const EdgeInsets.symmetric(
                              vertical: 24,
                              horizontal: 0,
                            ),
                            isDarkTheme: isDark,
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                _buildLogoSection(
                                      constraints,
                                      isTabletOrLandscape,
                                      screenHeight,
                                    )
                                    .animate()
                                    .fade(duration: 600.ms)
                                    .scale(curve: Curves.easeOutBack),
                                SizedBox(height: spacingMedium),
                                _buildForm(
                                      constraints,
                                      isTabletOrLandscape,
                                      screenHeight,
                                    )
                                    .animate()
                                    .fade(duration: 600.ms, delay: 200.ms)
                                    .slideY(begin: 0.1),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),

          _buildBottomNav().animate().fade(duration: 800.ms, delay: 600.ms),
        ],
      ),
    );
  }

  Widget _buildBrandHeader(bool isTablet, double screenHeight) {
    final minervaLogoSize = isTablet
        ? 65.0
        : (screenHeight * 0.08).clamp(45.0, 65.0);

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          width: minervaLogoSize,
          height: minervaLogoSize,
          decoration: BoxDecoration(
            color: Theme.of(context).brightness == Brightness.dark
                ? Colors.white.withValues(alpha: 0.1)
                : Colors.white.withValues(alpha: 0.8),
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Theme.of(context).primaryColor.withValues(alpha: 0.15),
                blurRadius: 15,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          padding: const EdgeInsets.all(8),
          child: Image.asset(
            'assets/images/minerva_logo.png',
            fit: BoxFit.contain,
          ),
        ),
        const SizedBox(width: 14),
        Text(
          'MINERVA',
          style: TextStyle(
            fontSize: isTablet ? 28 : 24,
            fontWeight: FontWeight.w800,
            letterSpacing: 2.0,
            color: Theme.of(context).primaryColor,
          ),
        ),
      ],
    ).animate().fade(duration: 800.ms).slideY(begin: -0.2);
  }

  Widget _buildLogoSection(
    BoxConstraints constraints,
    bool isTablet,
    double screenHeight,
  ) {
    final clientLogoSize = isTablet
        ? 120.0
        : (screenHeight * 0.12).clamp(90.0, 130.0);

    // Use local state first; fall back to provider's in-memory logo so we
    // automatically show the logo once AppConfigProvider finishes loading
    // (the widget already rebuilds on provider notifications via Provider.of
    // in build()).
    final effectiveLogo = schoolLogo.isNotEmpty
        ? schoolLogo
        : Provider.of<AppConfigProvider>(context, listen: false).appLogo;

    return Center(
      child: Container(
        height: clientLogoSize,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: effectiveLogo.isNotEmpty
            ? CachedNetworkImage(
                imageUrl: effectiveLogo,
                fit: BoxFit.contain,
                placeholder: (context, url) => const SizedBox(),
                errorWidget: (context, url, error) {
                  if (kDebugMode) {
                    debugPrint(
                      '[LoginPage] Failed to load logo: $url, error: $error',
                    );
                  }
                  CachedNetworkImage.evictFromCache(url);
                  return Image.asset(
                    'assets/images/minerva_logo.png',
                    fit: BoxFit.contain,
                  );
                },
              )
            : Image.asset(
                'assets/images/minerva_logo.png',
                fit: BoxFit.contain,
              ),
      ),
    );
  }

  Widget _buildForm(
    BoxConstraints constraints,
    bool isTablet,
    double screenHeight,
  ) {
    final fieldSpacing = isTablet
        ? 24.0
        : (screenHeight * 0.02).clamp(12.0, 20.0);
    final buttonSpacing = (screenHeight * 0.025).clamp(12.0, 24.0);

    return Column(
      children: [
        _buildRoleHint(isTablet),
        SizedBox(height: fieldSpacing),
        _buildInputField(
          icon: Icons.person_outline,
          hint: 'Username',
          controller: usernameController,
          constraints: constraints,
          isTablet: isTablet,
        ),
        SizedBox(height: fieldSpacing),
        _buildInputField(
          icon: Icons.lock_outline,
          hint: 'Password',
          controller: passwordController,
          constraints: constraints,
          obscureText: _obscurePassword,
          isTablet: isTablet,
          suffix: IconButton(
            icon: Icon(
              _obscurePassword ? Icons.visibility_off : Icons.visibility,
              color: const Color(0xFF666666),
              size: isTablet ? 28 : 22.0,
            ),
            onPressed: () =>
                setState(() => _obscurePassword = !_obscurePassword),
          ),
        ),
        SizedBox(height: fieldSpacing),
        Row(
          children: [
            Expanded(child: _buildRememberMe(isTablet)),
            _buildForgotPassword(constraints, isTablet),
          ],
        ),
        SizedBox(height: buttonSpacing),
        Align(
          alignment: Alignment.centerRight,
          child: _buildLoginButton(constraints, isTablet),
        ),
      ],
    );
  }

  Widget _buildRoleHint(bool isTablet) {
    final fontSize = isTablet ? 14.0 : 13.0;
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: context.isDark
            ? Colors.white.withValues(alpha: 0.04)
            : Theme.of(context).primaryColor.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: Theme.of(context).primaryColor.withValues(alpha: 0.1),
        ),
      ),
      child: Text(
        'Use your account credentials. Role is auto-detected: Student, Parent, or Staff.',
        style: TextStyle(
          fontFamily: 'Inter',
          color: context.primaryText.withValues(alpha: 0.8),
          fontSize: fontSize,
          fontWeight: FontWeight.w500,
        ),
      ),
    );
  }

  Widget _buildRememberMe(bool isTablet) {
    final fontSize = isTablet ? 16.0 : 14.0;
    return InkWell(
      onTap: () => setState(() => _rememberMe = !_rememberMe),
      borderRadius: BorderRadius.circular(8),
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 4),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            SizedBox(
              height: 24,
              width: 24,
              child: Checkbox(
                value: _rememberMe,
                onChanged: (value) {
                  setState(() => _rememberMe = value ?? false);
                },
              ),
            ),
            const SizedBox(width: 8),
            Text(
              'Remember Me',
              style: TextStyle(
                color: Colors.black87,
                fontSize: fontSize,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInputField({
    required IconData icon,
    required String hint,
    required TextEditingController controller,
    required BoxConstraints constraints,
    bool obscureText = false,
    Widget? suffix,
    bool isTablet = false,
  }) {
    final fieldHeight = isTablet ? 60.0 : 54.0;
    final iconSize = isTablet ? 24.0 : 22.0;
    final fontSize = isTablet ? 16.0 : 15.0;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      height: fieldHeight,
      padding: const EdgeInsets.symmetric(horizontal: 16),
      decoration: BoxDecoration(
        color: isDark
            ? Colors.white.withValues(alpha: 0.05)
            : Colors.white.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark
              ? Colors.white.withValues(alpha: 0.1)
              : Colors.white.withValues(alpha: 0.5),
        ),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            color: context.primaryText.withValues(alpha: 0.5),
            size: iconSize,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: TextField(
              controller: controller,
              obscureText: obscureText,
              cursorColor: Theme.of(context).primaryColor,
              decoration: InputDecoration(
                hintText: hint,
                border: InputBorder.none,
                filled: false,
                hintStyle: TextStyle(
                  fontFamily: 'Inter',
                  color: context.primaryText.withValues(alpha: 0.3),
                  fontSize: fontSize,
                  fontWeight: FontWeight.w500,
                ),
                contentPadding: const EdgeInsets.symmetric(vertical: 4),
              ),
              style: TextStyle(
                fontFamily: 'Inter',
                fontSize: fontSize,
                color: context.primaryText,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          if (suffix != null) suffix,
        ],
      ),
    );
  }

  Widget _buildForgotPassword(BoxConstraints constraints, bool isTablet) {
    final fontSize = isTablet ? 17.0 : 14.0;
    final iconSize = isTablet ? 20.0 : 16.0;

    return GestureDetector(
      onTap: _showForgotPasswordDialog,
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            Icons.search_rounded,
            color: const Color(0xFF666666),
            size: iconSize,
          ),
          const SizedBox(width: 6),
          Text(
            'Forgot Password?',
            style: TextStyle(
              color: Colors.black87,
              fontSize: fontSize,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoginButton(BoxConstraints constraints, bool isTablet) {
    final fontSize = isTablet ? 18.0 : 16.0;
    final iconSize = isTablet ? 22.0 : 20.0;
    final horizontalPadding = isTablet ? 40.0 : 24.0;
    final verticalPadding = isTablet ? 16.0 : 14.0;

    return Container(
      decoration: BoxDecoration(
        color: Theme.of(context).primaryColor,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Theme.of(context).primaryColor.withValues(alpha: 0.4),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(16),
          onTap: isLoading ? null : loginUser,
          child: Padding(
            padding: EdgeInsets.symmetric(
              horizontal: horizontalPadding,
              vertical: verticalPadding,
            ),
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
                        'Login',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          color: Colors.white,
                          fontSize: fontSize,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 0.5,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Icon(
                        Icons.arrow_forward_rounded,
                        color: Colors.white,
                        size: iconSize,
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }

  Widget _buildBottomNav() {
    return Positioned(
      bottom: 20,
      left: 20,
      right: 20,
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          // Privacy Policy - Bottom Left, No Background
          GestureDetector(
            onTap: _launchPrivacyPolicy,
            child: const Text(
              'Privacy Policy',
              style: TextStyle(
                color: Colors.black87,
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),

          // Action Icons - Bottom Right
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (AppConfig.adminEnabled) ...[
                _buildAdminIcon(),
                const SizedBox(width: 12),
              ],
              // Change URL entry point is intentionally hidden for single-tenant flow.
              // _buildUrlIcon(),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAdminIcon() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.indigo,
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.3),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(25),
          onTap: _launchAdminUrl,
          child: const Padding(
            padding: EdgeInsets.all(15),
            child: Icon(
              Icons.admin_panel_settings,
              color: Colors.white,
              size: 30,
            ),
          ),
        ),
      ),
    );
  }

  void _showForgotPasswordDialog() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const ForgotPasswordPage()),
    );
  }

  void _launchPrivacyPolicy() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (context) => const PrivacyPolicyPage()),
    );
  }

  Future<void> _launchAdminUrl() async {
    String baseUrl = AppConfig.adminUrl;

    // If adminUrl is empty, fallback to the current school base URL
    if (baseUrl.isEmpty) {
      baseUrl = await AppConfig.getBaseUrl();
    }

    if (baseUrl.isEmpty) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Admin URL not configured')),
        );
      }
      return;
    }

    try {
      // Clean base URL (remove trailing slashes)
      String cleanBase = baseUrl.trim();
      while (cleanBase.endsWith('/')) {
        cleanBase = cleanBase.substring(0, cleanBase.length - 1);
      }

      // Ensure it doesn't already have /site/login
      String finalUrl = cleanBase;
      if (!finalUrl.endsWith('/site/login')) {
        finalUrl = '$finalUrl/site/login';
      }

      final Uri url = Uri.parse(finalUrl);
      if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Could not launch Admin URL')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }
}
