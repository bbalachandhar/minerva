import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/dashboard/presentation/bloc/dashboard_bloc.dart';
import 'package:minerva_flutter/features/dashboard/domain/entities/module.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:developer'; // Import for logging

class DashboardPage extends StatefulWidget {
  const DashboardPage({Key? key}) : super(key: key);

  @override
  State<DashboardPage> createState() => _DashboardPageState();
}

class _DashboardPageState extends State<DashboardPage> {
  Color primaryColour = Colors.blue; // Default color
  Color secondaryColour = Colors.blue; // Default color
  String userName = 'Guest';
  String userImage = 'assets/placeholder_user.png';
  String classSection = '';
  String gender = 'unknown';

  @override
  void initState() {
    super.initState();
    _loadDashboardData();
    context.read<DashboardBloc>().add(FetchDashboardModules());
  }

  Future<void> _loadDashboardData() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      // Safely retrieve colors, falling back to default if null
      primaryColour = Color(int.parse((prefs.getString('primaryColour') ?? Constants.defaultPrimaryColour).replaceAll('#', '0xff')));
      secondaryColour = Color(int.parse((prefs.getString('secondaryColour') ?? Constants.defaultSecondaryColour).replaceAll('#', '0xff')));

      userName = prefs.getString(Constants.userName) ?? 'Guest'; // Use userName directly
      gender = prefs.getString(Constants.gender) ?? 'unknown'; // Retrieve gender
      String? apiUserImage = prefs.getString(Constants.userImage);

      if (apiUserImage != null && (apiUserImage.startsWith('http') || apiUserImage.startsWith('https'))) {
        userImage = apiUserImage;
      } else {
        // Fallback to gender-specific local assets if API image is local path or null
        if (gender == 'female') {
          userImage = 'assets/default_female.jpg';
        } else {
          userImage = 'assets/default_image.jpg'; // Generic or male placeholder
        }
      }

      classSection = prefs.getString(Constants.classSection) ?? '';
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE7F1EE),
      drawer: Drawer(
        child: ListView(
          padding: EdgeInsets.zero,
          children: <Widget>[
            DrawerHeader(
              decoration: BoxDecoration(
                color: primaryColour, // Apply primary color to DrawerHeader
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CircleAvatar(
                    radius: 30,
                    backgroundImage: userImage.startsWith('http') || userImage.startsWith('https')
                        ? NetworkImage(userImage)
                        : AssetImage(userImage) as ImageProvider,
                  ),
                  const SizedBox(height: 10),
                  Text(
                    userName,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                    ),
                  ),
                  Text(
                    classSection,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                    ),
                  ),
                ],
              ),
            ),
            ListTile(
              leading: const Icon(Icons.home),
              title: const Text('Home'),
              onTap: () {
                Navigator.pop(context); // Close the drawer
              },
            ),
            ListTile(
              leading: const Icon(Icons.person),
              title: const Text('Profile'),
              onTap: () {
                context.push('/profile');
                Navigator.pop(context); // Close the drawer
              },
            ),
            ListTile(
              leading: const Icon(Icons.info),
              title: const Text('About'),
              onTap: () {
                context.push('/about_school');
                Navigator.pop(context); // Close the drawer
              },
            ),
            ListTile(
              leading: const Icon(Icons.settings),
              title: const Text('Setting'),
              onTap: () {
                Navigator.pop(context); // Close the drawer
              },
            ),
            ListTile(
              leading: const Icon(Icons.logout),
              title: const Text('Logout'),
              onTap: () {
                Navigator.pop(context); // Close the drawer
              },
            ),
          ],
        ),
      ),
      body: CustomScrollView(
        slivers: <Widget>[
          SliverAppBar(
            backgroundColor: const Color(0xFFE7F1EE),
            pinned: true,
            floating: false,
            expandedHeight: 250.0,
            iconTheme: const IconThemeData(color: Colors.black),
            title: Image.asset('assets/splash_logo.png', height: 45),
            centerTitle: true,
            actions: [
              IconButton(
                icon: Image.asset('assets/ic_notification.png', width: 25, height: 25),
                onPressed: () {
                  // TODO: Handle notification tap
                },
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const SizedBox(height: 56), // Space for app bar
                  CircleAvatar(
                    radius: 40,
                    backgroundImage: userImage.startsWith('http')
                        ? NetworkImage(userImage) as ImageProvider
                        : AssetImage(userImage),
                  ),
                  const SizedBox(height: 10),
                  Text(
                    userName,
                    style: const TextStyle(
                        color: Colors.black,
                        fontSize: 20,
                        fontWeight: FontWeight.bold),
                  ),
                  Text(
                    classSection,
                    style: const TextStyle(color: Colors.black, fontSize: 14),
                  ),
                ],
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Card(
              margin: const EdgeInsets.only(top: 10),
              shape: const RoundedRectangleBorder(
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(20),
                  topRight: Radius.circular(20),
                ),
              ),
              elevation: 3,
              child: BlocBuilder<DashboardBloc, DashboardState>(
                builder: (context, state) {
                  if (state is DashboardLoading) {
                    log('DashboardPage - State: DashboardLoading');
                    return const Center(child: Padding(
                      padding: EdgeInsets.all(32.0),
                      child: CircularProgressIndicator(),
                    ));
                  } else if (state is DashboardLoaded) {
                    log('DashboardPage - State: DashboardLoaded');
                    return Column(
                      children: [
                        const SizedBox(height: 10), // Add some space at the top
                        _buildModuleSection('E-Learning', state.elearningModules),
                        _buildModuleSection('Academic', state.academicModules),
                        _buildModuleSection('Communicate', state.communicateModules),
                        _buildModuleSection('Other', state.otherModules),
                      ],
                    );
                  } else if (state is DashboardError) {
                    log('DashboardPage - State: DashboardError - ${state.message}');
                    return Center(child: Text(state.message));
                  }
                  log('DashboardPage - State: Initial or unknown');
                  return const Center(child: Text('Dashboard Page Content (Initial)'));
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _onModuleTapped(Module module) {
    log('Tapped on module: ${module.name}');
    switch (module.name.toLowerCase()) {
      case 'fees':
        context.push('/fees');
        break;
      case 'notice board': // New case for Notice Board
        context.push('/notice_board');
        break;
      case 'apply leave': // New case for Apply Leave
        context.push('/apply_leave');
        break;
      case 'transport routes': // New case for Transport Routes
        context.push('/transport_routes');
        break;
      case 'visitor book': // New case for Visitor Book
        context.push('/visitor_book');
        break;
      case 'hostel rooms':
        context.push('/hostel');
        break;
      case 'calendar to do list':
        context.push('/calendar_todo');
        break;
      case 'library':
        context.push('/library');
        break;
      case 'teachers rating':
        context.push('/teachers_rating');
        break;
      default:
        // Do nothing for now
        break;
    }
  }

  Widget _buildModuleSection(String title, List<Module> modules) {
    if (modules.isEmpty) {
      return const SizedBox.shrink(); // Hide section if no modules
    }
    log('Building section "$title" with ${modules.length} modules.');

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 10.0),
      elevation: 10.0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15.0)),
      child: Padding(
        padding: const EdgeInsets.all(10.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              title,
              style: const TextStyle(
                fontSize: 16, // Approximate from @dimen/btntext
                fontWeight: FontWeight.bold,
                color: Colors.black54, // Approximate from @color/hintDark
              ),
            ),
            const SizedBox(height: 10),
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 4,
                crossAxisSpacing: 8.0,
                mainAxisSpacing: 8.0,
              ),
              itemCount: modules.length,
              itemBuilder: (context, index) {
                final module = modules[index];
                return GestureDetector(
                  onTap: () => _onModuleTapped(module),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Image.asset(module.icon, width: 30, height: 30),
                      const SizedBox(height: 8),
                      Text(
                        module.name,
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}
