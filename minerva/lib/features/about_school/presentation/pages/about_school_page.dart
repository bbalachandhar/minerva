import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/about_school/presentation/bloc/about_school_bloc.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AboutSchoolPage extends StatefulWidget {
  const AboutSchoolPage({Key? key}) : super(key: key);

  @override
  State<AboutSchoolPage> createState() => _AboutSchoolPageState();
}

class _AboutSchoolPageState extends State<AboutSchoolPage> {
  Color primaryColour = Colors.blue; // Default color
  Color secondaryColour = Colors.blue; // Default color

  @override
  void initState() {
    super.initState();
    _loadColors();
    context.read<AboutSchoolBloc>().add(FetchAboutSchoolDetails());
  }

  Future<void> _loadColors() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      primaryColour = Color(int.parse((prefs.getString('primaryColour') ?? Constants.defaultPrimaryColour).replaceAll('#', '0xff')));
      secondaryColour = Color(int.parse((prefs.getString('secondaryColour') ?? Constants.defaultSecondaryColour).replaceAll('#', '0xff')));
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('About School'),
        backgroundColor: secondaryColour,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            context.pop();
          },
        ),
      ),
      body: BlocBuilder<AboutSchoolBloc, AboutSchoolState>(
        builder: (context, state) {
          if (state is AboutSchoolLoading) {
            return const Center(child: CircularProgressIndicator());
          } else if (state is AboutSchoolLoaded) {
            final details = state.details;
            return SingleChildScrollView(
              padding: const EdgeInsets.all(16.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  if (details.appLogo.isNotEmpty)
                    Image.network(
                      details.appLogo,
                      height: 100,
                    )
                  else
                    Image.asset(
                      'assets/logo.png', // Fallback to local asset
                      height: 100,
                    ),
                  const SizedBox(height: 20),
                  Text(
                    details.name,
                    style: TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: primaryColour,
                    ),
                  ),
                  const SizedBox(height: 10),
                  _buildDetailRow('Address', details.address),
                  _buildDetailRow('Email', details.email),
                  _buildDetailRow('Phone', details.phone),
                  _buildDetailRow('School Code', details.diseCode),
                  _buildDetailRow('Current Session', details.session),
                  _buildDetailRow('Session Start Month', details.startMonthName),
                ],
              ),
            );
          } else if (state is AboutSchoolError) {
            return Center(child: Text(state.message));
          }
          return const Center(child: Text('About School Page Content'));
        },
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    if (value.isEmpty) {
      return const SizedBox.shrink();
    }
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            '$label:',
            style: const TextStyle(fontWeight: FontWeight.bold),
          ),
          Flexible(child: Text(value)),
        ],
      ),
    );
  }
}