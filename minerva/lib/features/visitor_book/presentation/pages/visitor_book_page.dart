// minerva/lib/features/visitor_book/presentation/pages/visitor_book_page.dart

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/bloc/visitor_bloc.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/bloc/visitor_event.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/bloc/visitor_state.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/widgets/visitor_item.dart';
import 'package:minerva_flutter/utils/app_constants.dart';
import 'package:minerva_flutter/utils/app_utility.dart';

class VisitorBookPage extends StatefulWidget {
  const VisitorBookPage({super.key});

  @override
  State<VisitorBookPage> createState() => _VisitorBookPageState();
}

class _VisitorBookPageState extends State<VisitorBookPage> {
  String _studentId = '';

  @override
  void initState() {
    super.initState();
    _loadStudentIdAndFetchVisitors();
  }

  Future<void> _loadStudentIdAndFetchVisitors() async {
    _studentId = await AppUtility.getSharedPreference(AppConstants.studentIdKey);
    if (_studentId.isNotEmpty) {
      if (mounted) {
        context.read<VisitorBloc>().add(FetchVisitors(studentId: _studentId));
      }
    } else {
      // Handle case where studentId is not available, maybe show an error or redirect
      if (mounted) {
        context.read<VisitorBloc>().add(const VisitorErrorOccurred(message: "Student ID not found."));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Visitor Book'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            GoRouter.of(context).pop();
          },
        ),
      ),
      body: BlocBuilder<VisitorBloc, VisitorState>(
        builder: (context, state) {
          if (state is VisitorLoading) {
            return _buildContent(context, state, const Center(child: CircularProgressIndicator()));
          } else if (state is VisitorLoaded) {
            if (state.visitors.isEmpty) {
              return _buildContent(context, state, Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Image.asset('assets/no_data.png', height: 150),
                    const SizedBox(height: 10),
                    const Text('No Visitor Data Available', style: TextStyle(fontSize: 18)),
                  ],
                ),
              ));
            }
            return _buildContent(context, state, RefreshIndicator(
              onRefresh: _loadStudentIdAndFetchVisitors,
              child: ListView.builder(
                padding: const EdgeInsets.all(10.0),
                itemCount: state.visitors.length,
                itemBuilder: (context, index) {
                  return VisitorItem(
                    visitor: state.visitors[index],
                    primaryColor: state.primaryColor,
                    secondaryColor: state.secondaryColor,
                    imagesUrl: state.imagesUrl,
                    dateFormat: state.dateFormat,
                  );
                },
              ),
            ));
          } else if (state is VisitorError) {
            return _buildContent(context, state, Center(child: Text('Error: ${state.message}')));
          }
          return _buildContent(context, state, const Center(child: Text('Fetch visitor data.')));
        },
      ),
    );
  }

  Widget _buildContent(BuildContext context, VisitorState state, Widget mainContent) {
    Color cardBackgroundColor = Colors.white; // Default color
    if (state is VisitorLoaded) {
      try {
        cardBackgroundColor = Color(int.parse(state.primaryColor.replaceFirst('#', '0xFF')));
      } catch (e) {
        cardBackgroundColor = Colors.blue;
      }
    } else if (state is VisitorError) {
      cardBackgroundColor = Colors.blue;
    }

    // Get primaryColor for the text color if available from state
    Color textHeadingColor = Colors.grey[800]!;
    if (state is VisitorLoaded) {
      try {
        textHeadingColor = Color(int.parse(state.primaryColor.replaceFirst('#', '0xFF')));
      } catch (e) {
        textHeadingColor = Colors.grey[800]!;
      }
    }


    return Card( // Inner Card
      elevation: 3,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      color: Colors.white,
      child: Padding(
        padding: const EdgeInsets.all(1.0),
        child: Column(
          children: [
            // Header section
            Padding(
              padding: const EdgeInsets.only(top: 10.0, left: 10.0, right: 10.0),
              child: Row(
                children: [
                  Expanded(
                    flex: 1,
                    child: Padding(
                      padding: const EdgeInsets.only(left: 10.0),
                      child: Align(
                        alignment: Alignment.center,
                        child: Text(
                          'Your Visitor Book Is Here!',
                          style: TextStyle(
                            color: textHeadingColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 22,
                          ),
                        ),
                      ),
                    ),
                  ),
                  Expanded(
                    flex: 1,
                    child: Image.asset(
                      'assets/noticepage.png',
                      height: 110,
                      fit: BoxFit.contain,
                    ),
                  ),
                ],
              ),
            ),
            Expanded( // Allowing the main content to take remaining space
              child: mainContent,
            ),
          ],
        ),
      ),
    );
  }
}
