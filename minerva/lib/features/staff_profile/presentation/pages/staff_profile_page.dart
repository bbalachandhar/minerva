import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/staff_profile/presentation/bloc/staff_profile_bloc.dart';

class StaffProfilePage extends StatefulWidget {
  const StaffProfilePage({Key? key}) : super(key: key);

  @override
  _StaffProfilePageState createState() => _StaffProfilePageState();
}

class _StaffProfilePageState extends State<StaffProfilePage> {
  @override
  void initState() {
    super.initState();
    // Dispatch event to fetch profile data when the page is loaded
    context.read<StaffProfileBloc>().add(FetchStaffProfile());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('My Profile'),
      ),
      body: BlocBuilder<StaffProfileBloc, StaffProfileState>(
        builder: (context, state) {
          if (state is StaffProfileLoading) {
            return const Center(child: CircularProgressIndicator());
          } else if (state is StaffProfileLoaded) {
            final profile = state.profileData;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Text('Name: ${profile['name']} ${profile['surname']}', style: Theme.of(context).textTheme.titleLarge),
                  Text('Email: ${profile['email']}'),
                  Text('Role: ${profile['user_type']}'),
                  Text('Department: ${profile['department']}'),
                  Text('Designation: ${profile['designation']}'),
                  // Add more fields as needed
                ],
              ),
            );
          } else if (state is StaffProfileError) {
            return Center(child: Text('Error: ${state.error}'));
          }
          return const Center(child: Text('Please wait...'));
        },
      ),
    );
  }
}
