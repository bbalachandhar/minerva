import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/profile/presentation/bloc/profile_bloc.dart';
import 'package:minerva_flutter/features/profile/presentation/widgets/personal_details_tab.dart';
import 'package:minerva_flutter/features/profile/presentation/widgets/parents_details_tab.dart';
import 'package:minerva_flutter/features/profile/presentation/widgets/other_details_tab.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProfilePage extends StatefulWidget {
  const ProfilePage({Key? key}) : super(key: key);

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> with SingleTickerProviderStateMixin {
  Color primaryColour = Colors.blue; // Default color
  Color secondaryColour = Colors.blue; // Default color
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _loadColors();
    context.read<ProfileBloc>().add(FetchProfileDetails());
    _tabController = TabController(length: 3, vsync: this); // 3 tabs for Personal, Parents, Other
  }

  Future<void> _loadColors() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      primaryColour = Color(int.parse((prefs.getString('primaryColour') ?? Constants.defaultPrimaryColour).replaceAll('#', '0xff')));
      secondaryColour = Color(int.parse((prefs.getString('secondaryColour') ?? Constants.defaultSecondaryColour).replaceAll('#', '0xff')));
    });
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Profile'),
        backgroundColor: secondaryColour, // Apply secondary color to AppBar
        leading: IconButton( // Explicitly add a back button
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            context.pop(); // Use GoRouter's pop to go back
          },
        ),
      ),
      body: Container(
        decoration: const BoxDecoration(
          image: DecorationImage(
            image: AssetImage("assets/img_background_main.jpg"),
            fit: BoxFit.cover,
          ),
        ),
        child: BlocBuilder<ProfileBloc, ProfileState>(
          builder: (context, state) {
            if (state is ProfileLoading) {
              return const Center(child: CircularProgressIndicator());
            } else if (state is ProfileLoaded) {
              final profile = state.profile;
              return Column(
                children: [
                  Card( // Card for profile header details
                    margin: const EdgeInsets.all(10), // Matches android:layout_margin="10dp"
                    elevation: 3,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
                    color: Colors.white,
                    child: Padding(
                      padding: const EdgeInsets.all(10.0), // Matches android:padding="10dp"
                      child: Column( // Use Column here to ensure vertical arrangement of elements
                        children: [
                          Row( // Profile image and details
                            children: [
                              Expanded( // Using Expanded to mimic the 200dp width and align with the rest of the content
                                flex: 2,
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      '${profile.firstName} ${profile.lastName}',
                                      style: TextStyle(
                                        color: Colors.black, // Assuming textHeading is a dark color
                                        fontSize: 22,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 5),
                                    Text(
                                      '${profile.className} - ${profile.section} (${profile.session})',
                                      style: const TextStyle(
                                        color: Colors.black54, // Assuming textHeading is a dark color
                                        fontSize: 16,
                                      ),
                                    ),
                                    const SizedBox(height: 5),
                                    Text(
                                      'Admission No: ${profile.admissionNo}',
                                      style: const TextStyle(
                                        color: Colors.black54,
                                        fontSize: 16,
                                      ),
                                    ),
                                    const SizedBox(height: 5),
                                    Text(
                                      'Roll No: ${profile.rollNo}',
                                      style: const TextStyle(
                                        color: Colors.black54,
                                        fontSize: 16,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Expanded( // Mimics the LinearLayout next to profile details
                                flex: 1,
                                child: Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    CircleAvatar(
                                      radius: 50,
                                      backgroundImage: profile.profileImageUrl.startsWith('http') || profile.profileImageUrl.startsWith('https')
                                          ? NetworkImage(profile.profileImageUrl)
                                          : AssetImage(profile.profileImageUrl) as ImageProvider, // Fallback to asset
                                    ),
                                    const SizedBox(height: 5),
                                    Text(
                                      profile.behaviourScore.isNotEmpty ? 'Behaviour Score: ${profile.behaviourScore}' : '',
                                      style: const TextStyle(color: Colors.black54),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),
                          // Barcode and QR Code Images - moved into the same card for better grouping
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                            children: [
                              Expanded(
                                child: Column(
                                  children: [
                                    Image.network(profile.barcodeImageUrl, height: 80),
                                    const Text('Barcode'),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 16),
                              Expanded(
                                child: Column(
                                  children: [
                                    Image.network(profile.qrcodeImageUrl, height: 80),
                                    const Text('QR Code'),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 20), // Spacing between header card and tabs card
                  Expanded( // This Card should take the remaining space
                    child: Card( // Card for TabBar and TabBarView
                      margin: const EdgeInsets.symmetric(horizontal: 10),
                      elevation: 5,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
                      color: Colors.white,
                      child: Column(
                        children: [
                          TabBar(
                            controller: _tabController,
                            labelColor: primaryColour, // Color of the selected tab's text
                            unselectedLabelColor: Colors.grey, // Color of unselected tab's text
                            indicatorColor: primaryColour, // Color of the tab indicator
                            tabs: const [
                              Tab(text: 'Personal Details'),
                              Tab(text: 'Parents Details'),
                              Tab(text: 'Other Details'),
                            ],
                          ),
                          Expanded( // TabBarView should take the remaining space within this inner Column
                            child: TabBarView(
                              controller: _tabController,
                              children: [
                                PersonalDetailsTab(profile: profile),
                                ParentsDetailsTab(profile: profile),
                                OtherDetailsTab(profile: profile),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            } else if (state is ProfileError) {
              return Center(child: Text(state.message));
            }
            return const Center(child: Text('Profile Page Content'));
          },
        ),
      ),
    );
  }
}