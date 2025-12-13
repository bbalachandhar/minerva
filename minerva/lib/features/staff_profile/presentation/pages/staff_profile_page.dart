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
                  Text('Name: ${profile['name'] ?? 'N/A'} ${profile['surname'] ?? ''}', style: Theme.of(context).textTheme.titleLarge),
                  const SizedBox(height: 8),
                  Text('Employee ID: ${profile['employee_id'] ?? 'N/A'}'),
                  Text('Email: ${profile['email'] ?? 'N/A'}'),
                  Text('Contact No: ${profile['contact_no'] ?? 'N/A'}'),
                  Text('Gender: ${profile['gender'] ?? 'N/A'}'),
                  Text('Date of Birth: ${profile['dob'] ?? 'N/A'}'),
                  Text('Marital Status: ${profile['marital_status'] ?? 'N/A'}'),
                  Text('Date of Joining: ${profile['date_of_joining'] ?? 'N/A'}'),
                  const SizedBox(height: 16),
                  Text('Qualifications:', style: Theme.of(context).textTheme.titleMedium),
                  Text('UG Qualification: ${profile['ug_qualification'] ?? 'N/A'}'),
                  Text('PG Qualification: ${profile['pg_qualification'] ?? 'N/A'}'),
                  Text('Higher Qualification: ${profile['higher_qualification'] ?? 'N/A'}'),
                  Text('Qualified Exam: ${profile['qualified_exam'] ?? 'N/A'}'),
                  Text('Subject Specialization: ${profile['subject_specialization'] ?? 'N/A'}'),
                  Text('Additional Qualification: ${profile['additional_qualification'] ?? 'N/A'}'),
                  const SizedBox(height: 16),
                  Text('Work Experience: ${profile['work_exp'] ?? 'N/A'} years'),
                  const SizedBox(height: 16),
                  Text('Address:', style: Theme.of(context).textTheme.titleMedium),
                  Text('Local Address: ${profile['local_address'] ?? 'N/A'}'),
                  Text('Permanent Address: ${profile['permanent_address'] ?? 'N/A'}'),
                  const SizedBox(height: 16),
                  Text('Bank Details:', style: Theme.of(context).textTheme.titleMedium),
                  Text('Account Title: ${profile['account_title'] ?? 'N/A'}'),
                  Text('Bank Account No: ${profile['bank_account_no'] ?? 'N/A'}'),
                  Text('Bank Name: ${profile['bank_name'] ?? 'N/A'}'),
                  Text('IFSC Code: ${profile['ifsc_code'] ?? 'N/A'}'),
                  Text('Bank Branch: ${profile['bank_branch'] ?? 'N/A'}'),
                  const SizedBox(height: 16),
                  Text('Social Media:', style: Theme.of(context).textTheme.titleMedium),
                  Text('Facebook: ${profile['facebook'] ?? 'N/A'}'),
                  Text('Twitter: ${profile['twitter'] ?? 'N/A'}'),
                  Text('LinkedIn: ${profile['linkedin'] ?? 'N/A'}'),
                  Text('Instagram: ${profile['instagram'] ?? 'N/A'}'),
                  const SizedBox(height: 16),
                  Text('Note: ${profile['note'] ?? 'N/A'}'),
                  Text('Status: ${profile['is_active'] == '1' ? 'Active' : 'Inactive'}'),
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
