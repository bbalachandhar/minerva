// minerva/lib/features/apply_leave/presentation/pages/apply_leave_page.dart
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/bloc/apply_leave_bloc.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/bloc/apply_leave_event.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/bloc/apply_leave_state.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/widgets/apply_leave_list_item_widget.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApplyLeavePage extends StatefulWidget {
  const ApplyLeavePage({Key? key}) : super(key: key);

  @override
  State<ApplyLeavePage> createState() => _ApplyLeavePageState();
}

class _ApplyLeavePageState extends State<ApplyLeavePage> {
  Color primaryColour = Colors.blue;
  Color secondaryColour = Colors.blue;
  String imagesUrl = '';
  // Assuming superadminRestriction affects some display here, similar to Notice Board
  bool isSuperAdminRestricted = false;

  @override
  void initState() {
    super.initState();
    _loadConfig();
    _fetchLeaveApplications();
  }

  void _fetchLeaveApplications() {
    context.read<ApplyLeaveBloc>().add(const FetchLeaveApplications());
  }

  Future<void> _loadConfig() async {
    final prefs = await SharedPreferences.getInstance();
    if (mounted) {
      setState(() {
        primaryColour = Color(int.parse((prefs.getString('primaryColour') ?? Constants.defaultPrimaryColour).replaceAll('#', '0xff')));
        secondaryColour = Color(int.parse((prefs.getString('secondaryColour') ?? Constants.defaultSecondaryColour).replaceAll('#', '0xff')));
        imagesUrl = prefs.getString(Constants.imagesUrl) ?? '';
        isSuperAdminRestricted = prefs.getString(Constants.superadminRestriction) == 'enabled';
      });
    }
  }

  void _onDeleteLeave(String leaveId) {
    // Show confirmation dialog before dispatching delete event
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AlertDialog(
          title: const Text('Confirm Delete'),
          content: const Text('Are you sure you want to delete this leave application?'),
          actions: <Widget>[
            TextButton(
              child: const Text('No'),
              onPressed: () {
                Navigator.of(context).pop();
              },
            ),
            TextButton(
              child: const Text('Yes'),
              onPressed: () {
                context.read<ApplyLeaveBloc>().add(DeleteLeaveApplication(leaveId: leaveId));
                Navigator.of(context).pop();
              },
            ),
          ],
        );
      },
    );
  }

  void _onEditLeave(LeaveApplicationEntity leaveApplication) {
    // Navigate to edit page, passing the leave application details
    // This route will be defined later
    context.push('/apply_leave/edit', extra: leaveApplication);
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Applied Leave'),
        backgroundColor: secondaryColour,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            context.pop();
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
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.all(16.0), // Padding around the header
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      'Applied Leave', // @string/appliedleaveheading
                      style: TextStyle(
                        fontSize: 22.0,
                        fontWeight: FontWeight.bold,
                        color: primaryColour, // Use primaryColour for textHeading
                      ),
                    ),
                  ),
                  Image.asset(
                    'assets/leavepage.jpg', // @drawable/leavepage
                    width: 110,
                    height: 110,
                    fit: BoxFit.contain,
                  ),
                ],
              ),
            ),
              Expanded( // The BlocConsumer should take the remaining space
                child: BlocConsumer<ApplyLeaveBloc, ApplyLeaveState>(
                  listener: (context, state) {
                    if (state is ApplyLeaveOperationSuccess) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(state.message)),
                      );
                    } else if (state is ApplyLeaveError) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(state.message)),
                      );
                    }
                  },
                  builder: (context, state) {
                    if (state is ApplyLeaveLoading) {
                      return const Center(child: CircularProgressIndicator());
                    } else if (state is LeaveApplicationsLoaded) {
                      if (state.leaveApplications.isEmpty) {
                        return _buildNoDataView();
                      }
                      return RefreshIndicator(
                        onRefresh: () async {
                          _fetchLeaveApplications();
                        },
                        child: ListView.builder(
                          shrinkWrap: true, // Important for ListView inside SingleChildScrollView
                          physics: const NeverScrollableScrollPhysics(), // Important for ListView inside SingleChildScrollView
                          itemCount: state.leaveApplications.length,
                          itemBuilder: (context, index) {
                            final leave = state.leaveApplications[index];
                            return ApplyLeaveListItemWidget(
                              leaveApplication: leave,
                              primaryColour: primaryColour,
                              secondaryColour: secondaryColour,
                              imagesUrl: imagesUrl,
                              onDelete: _onDeleteLeave,
                              onEdit: _onEditLeave,
                            );
                          },
                        ),
                      );
                    } else if (state is ApplyLeaveError) {
                      return _buildNoDataView(message: state.message);
                    }
                    return _buildNoDataView();
                  },
                ),
              ),
          ],
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          context.push('/apply_leave/add'); // Navigate to add leave page
        },
        backgroundColor: primaryColour, // Dynamic color
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildNoDataView({String? message}) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Image.asset('assets/no_data.png', width: 150, height: 150), // @drawable/no_data
          const SizedBox(height: 10),
          Text(
            message ?? 'No Leave Applications Found!', // @string/noData
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Theme.of(context).colorScheme.error, // Using theme error color or a specific red
            ),
          ),
        ],
      ),
    );
  }
}
