import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../../utils/constants.dart';

enum LoginType { studentParent, staff }

class LoginPage extends StatefulWidget {
  const LoginPage({Key? key}) : super(key: key);

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  final ValueNotifier<bool> _isPasswordVisible = ValueNotifier<bool>(false);
  String? appLogoUrl;
  LoginType _selectedLoginType = LoginType.studentParent;

  @override
  void initState() {
    super.initState();
    _loadAppLogo();
    // Prefill username and password for testing
    _usernameController.text = 'std60';
    _passwordController.text = 'ou11za';
  }

  Future<void> _loadAppLogo() async {
    final sharedPreferences = await SharedPreferences.getInstance();
    setState(() {
      appLogoUrl = sharedPreferences.getString(Constants.appLogo);
    });
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    _isPasswordVisible.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: BlocConsumer<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is AuthSuccess) {
            if (state.role == 'staff') {
              context.go('/staff/dashboard');
            } else {
              context.go('/dashboard');
            }
          } else if (state is AuthFailure) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(content: Text(state.error)),
            );
          }
        },
        builder: (context, state) {
          return Container(
            decoration: const BoxDecoration(
              image: DecorationImage(
                image: AssetImage("assets/img_login_background.png"),
                fit: BoxFit.cover,
              ),
            ),
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: <Widget>[
                    if (appLogoUrl != null && appLogoUrl!.isNotEmpty)
                      Image.network(
                        appLogoUrl!,
                        height: 100,
                      )
                    else
                      Image.asset(
                        'assets/logo.png',
                        height: 100,
                      ),
                    const SizedBox(height: 30),
                    ToggleButtons(
                      isSelected: [_selectedLoginType == LoginType.studentParent, _selectedLoginType == LoginType.staff],
                      onPressed: (int index) {
                        setState(() {
                          _selectedLoginType = index == 0 ? LoginType.studentParent : LoginType.staff;
                          if (_selectedLoginType == LoginType.staff) {
                            _usernameController.text = 'skarusamy81@gmail.com';
                            _passwordController.text = 'Testpass1!';
                          } else {
                            // Revert to existing student/parent default
                            _usernameController.text = 'std60'; 
                            _passwordController.text = 'ou11za';
                          }
                        });
                      },
                      borderRadius: BorderRadius.circular(8.0),
                      selectedColor: Colors.white,
                      color: Colors.black,
                      fillColor: Theme.of(context).primaryColor,
                      children: const <Widget>[
                        Padding(padding: EdgeInsets.symmetric(horizontal: 16.0), child: Text('Student/Parent')),
                        Padding(padding: EdgeInsets.symmetric(horizontal: 16.0), child: Text('Staff')),
                      ],
                    ),
                    const SizedBox(height: 20),
                    TextField(
                      controller: _usernameController,
                      decoration: InputDecoration(
                        labelText: _selectedLoginType == LoginType.staff ? 'Email' : 'Username',
                        border: const OutlineInputBorder(),
                        filled: true,
                        fillColor: Colors.white,
                        prefixIcon: const ImageIcon(AssetImage('assets/ic_user.png')),
                      ),
                    ),
                    const SizedBox(height: 20),
                    ValueListenableBuilder<bool>(
                      valueListenable: _isPasswordVisible,
                      builder: (context, isVisible, child) {
                        return TextField(
                          controller: _passwordController,
                          obscureText: !isVisible,
                          decoration: InputDecoration(
                            labelText: 'Password',
                            border: const OutlineInputBorder(),
                            filled: true,
                            fillColor: Colors.white,
                            prefixIcon: const ImageIcon(AssetImage('assets/ic_lock_filled.png')),
                            suffixIcon: IconButton(
                              icon: Image.asset(
                                isVisible ? 'assets/eye_black.png' : 'assets/eyehide.png',
                                height: 24,
                                width: 24,
                              ),
                              onPressed: () {
                                _isPasswordVisible.value = !isVisible;
                              },
                            ),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 20),
                    ElevatedButton(
                      onPressed: state is AuthLoading
                          ? null
                          : () {
                              if (_selectedLoginType == LoginType.staff) {
                                context.read<AuthBloc>().add(
                                      StaffLoginRequested(
                                        email: _usernameController.text,
                                        password: _passwordController.text,
                                      ),
                                    );
                              } else {
                                context.read<AuthBloc>().add(
                                      LoginRequested(
                                        username: _usernameController.text,
                                        password: _passwordController.text,
                                      ),
                                    );
                              }
                            },
                      style: ElevatedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 50),
                      ),
                      child: state is AuthLoading
                          ? const CircularProgressIndicator(color: Colors.white)
                          : const Text('Login'),
                    ),
                    const SizedBox(height: 20),
                    TextButton(
                      onPressed: () {},
                      child: const Text('Forgot Password?'),
                    ),
                    const SizedBox(height: 10),
                    TextButton(
                      onPressed: () {},
                      child: const Text('Change URL'),
                    ),
                    const SizedBox(height: 10),
                    TextButton(
                      onPressed: () {},
                      child: const Text('Privacy Policy'),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}