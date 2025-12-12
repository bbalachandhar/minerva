import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/auth/presentation/bloc/auth_bloc.dart';

class StaffDashboardPage extends StatelessWidget {
  const StaffDashboardPage({Key? key}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Staff Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () {
              context.read<AuthBloc>().add(StaffLogoutRequested());
            },
          ),
        ],
      ),
      body: BlocListener<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is AuthInitial) {
            context.go('/login');
          }
        },
        child: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('Welcome, Staff Member!'),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: () {
                  context.go('/staff/profile');
                },
                child: const Text('View Profile'),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
