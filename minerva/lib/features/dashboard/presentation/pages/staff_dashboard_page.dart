import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:minerva_flutter/features/dashboard/domain/entities/module.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'dart:developer';

import 'package:shared_preferences/shared_preferences.dart';

class StaffDashboardPage extends StatefulWidget {
  const StaffDashboardPage({Key? key}) : super(key: key);

  @override
  _StaffDashboardPageState createState() => _StaffDashboardPageState();
}

class _StaffDashboardPageState extends State<StaffDashboardPage> {
  String userName = 'Staff Member';
  String userImage = 'assets/placeholder_user.png';
  String userEmail = '';
  String employeeId = '';
  String mobileNo = '';

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  Future<void> _loadUserData() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      userName = prefs.getString(Constants.userName) ?? 'Staff Member';
      userEmail = prefs.getString(Constants.email) ?? '';
      employeeId = prefs.getString(Constants.employeeId) ?? '';
      mobileNo = prefs.getString(Constants.mobileNo) ?? '';
      String gender = prefs.getString(Constants.gender) ?? 'unknown';
      String? apiUserImage = prefs.getString(Constants.userImage);

      if (apiUserImage != null && apiUserImage.isNotEmpty) {
        userImage = (prefs.getString(Constants.imagesUrl) ?? '') + apiUserImage;
      } else {
        if (gender.toLowerCase() == 'female') {
          userImage = 'assets/default_female.png';
        } else {
          userImage = 'assets/default_male.png';
        }
      }
    });
  }

  // Hardcoded list of staff modules for now
  static final List<Module> _academicModules = [
    const Module(name: 'Apply Leave', icon: 'assets/ic_leave.png'),
    const Module(name: 'Attendance', icon: 'assets/ic_nav_attendance.png'),
    const Module(name: 'Payroll', icon: 'assets/ic_nav_fees.png'),
  ];

  static final List<Module> _otherModules = [
    const Module(name: 'Notice Board', icon: 'assets/ic_notice.png'),
    const Module(name: 'Documents', icon: 'assets/ic_documents_certificate.png'),
    const Module(name: 'Timeline', icon: 'assets/ic_nav_timeline.png'),
  ];

  @override
  Widget build(BuildContext context) {
    final bool isNetworkImage = userImage.startsWith('http');
    final ImageProvider displayImage = isNetworkImage
        ? NetworkImage(userImage)
        : AssetImage(userImage) as ImageProvider;

    return Scaffold(
      backgroundColor: const Color(0xFFE7F1EE),
      drawer: _buildDrawer(displayImage),
      body: BlocListener<AuthBloc, AuthState>(
        listener: (context, state) {
          if (state is AuthInitial) {
            context.go('/login');
          }
        },
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: <Widget>[
            SliverAppBar(
              backgroundColor: const Color(0xFFE7F1EE),
              pinned: true,
              floating: true,
              expandedHeight: 280.0,
              iconTheme: const IconThemeData(color: Colors.black),
              actions: [
                IconButton(
                  icon: Image.asset('assets/ic_notification.png', width: 25, height: 25),
                  onPressed: () {},
                ),
              ],
              flexibleSpace: FlexibleSpaceBar(
                // title: Text(userName, style: const TextStyle(color: Colors.black, fontSize: 16.0)),
                centerTitle: true,
                background: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 56),
                    CircleAvatar(radius: 40, backgroundImage: displayImage),
                    const SizedBox(height: 10),
                    Text(userName, style: const TextStyle(color: Colors.black, fontSize: 20, fontWeight: FontWeight.bold)),
                    Text('Emp ID: $employeeId', style: const TextStyle(color: Colors.black, fontSize: 14)),
                    Text(userEmail, style: const TextStyle(color: Colors.black, fontSize: 14)),
                    Text('Mob No: $mobileNo', style: const TextStyle(color: Colors.black, fontSize: 14)),
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
                elevation: 0,
                color: Colors.transparent,
                child: Column(
                  children: [
                    const SizedBox(height: 10),
                    _buildModuleSection('Academics', _academicModules, context),
                    _buildModuleSection('Others', _otherModules, context),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Drawer _buildDrawer(ImageProvider displayImage) {
    return Drawer(
      child: ListView(
        padding: EdgeInsets.zero,
        children: <Widget>[
          UserAccountsDrawerHeader(
            accountName: Text(userName),
            accountEmail: Text(userEmail),
            currentAccountPicture: CircleAvatar(
              backgroundImage: displayImage,
            ),
            decoration: const BoxDecoration(
              color: Colors.blue,
            ),
          ),
          ListTile(
            leading: const Icon(Icons.dashboard),
            title: const Text('Dashboard'),
            onTap: () => Navigator.pop(context),
          ),
          ListTile(
            leading: const Icon(Icons.person),
            title: const Text('My Profile'),
            onTap: () {
              Navigator.pop(context);
              context.push('/staff/profile');
            },
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.logout),
            title: const Text('Logout'),
            onTap: () {
              context.read<AuthBloc>().add(StaffLogoutRequested());
            },
          ),
        ],
      ),
    );
  }

  void _onModuleTapped(BuildContext context, Module module) {
    log('Tapped on staff module: ${module.name}');
  }

  Widget _buildModuleSection(String title, List<Module> modules, BuildContext context) {
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
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Colors.black54,
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
                  onTap: () => _onModuleTapped(context, module),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Image.asset(module.icon, width: 30, height: 30),
                      const SizedBox(height: 8),
                      Text(
                        module.name,
                        textAlign: TextAlign.center,
                        style: const TextStyle(fontSize: 12),
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
