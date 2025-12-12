// minerva/lib/features/transport_route/presentation/pages/transport_route_page.dart

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/transport_route/presentation/bloc/transport_route_bloc.dart';
import 'package:minerva_flutter/features/transport_route/presentation/bloc/transport_route_event.dart';
import 'package:minerva_flutter/features/transport_route/presentation/bloc/transport_route_state.dart';
import 'package:minerva_flutter/features/transport_route/presentation/widgets/pickup_point_item.dart';
import 'package:minerva_flutter/features/transport_route/presentation/widgets/transport_route_details_card.dart';
import 'package:minerva_flutter/utils/app_constants.dart';
import 'package:minerva_flutter/utils/app_utility.dart';

class TransportRoutePage extends StatefulWidget {
  const TransportRoutePage({super.key});

  @override
  State<TransportRoutePage> createState() => _TransportRoutePageState();
}

class _TransportRoutePageState extends State<TransportRoutePage> {
  String _studentId = '';

  @override
  void initState() {
    super.initState();
    _loadStudentIdAndFetchRoutes();
  }

  Future<void> _loadStudentIdAndFetchRoutes() async {
    _studentId = await AppUtility.getSharedPreference(AppConstants.studentIdKey);
    if (_studentId.isNotEmpty) {
      if (mounted) {
        context.read<TransportRouteBloc>().add(FetchTransportRoutes(studentId: _studentId));
      }
    } else {
      // Handle case where studentId is not available, maybe show an error or redirect
      if (mounted) {
        context.read<TransportRouteBloc>().emit(const TransportRouteError(message: "Student ID not found."));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Transport Route'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () {
            GoRouter.of(context).pop();
          },
        ),
      ),
      body: BlocBuilder<TransportRouteBloc, TransportRouteState>(
        builder: (context, state) {
          if (state is TransportRouteLoading) {
            return const Center(child: CircularProgressIndicator());
          } else if (state is TransportRouteLoaded) {
            return RefreshIndicator(
              onRefresh: _loadStudentIdAndFetchRoutes,
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(10.0),
                child: Column(
                  children: [
                    TransportRouteDetailsCard(
                      transportRoute: state.transportRoute,
                      primaryColor: state.primaryColor,
                      imagesUrl: state.imagesUrl,
                    ),
                    const SizedBox(height: 10),
                    ListView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: state.pickupPoints.length,
                      itemBuilder: (context, index) {
                        return PickupPointItem(
                          pickupPoint: state.pickupPoints[index],
                          isPickupName: state.pickupPoints[index].pickupPoint == state.pickupName,
                          secondaryColor: state.secondaryColor,
                        );
                      },
                    ),
                  ],
                ),
              ),
            );
          } else if (state is TransportRouteError) {
            return Center(child: Text('Error: ${state.message}'));
          }
          return const Center(child: Text('Please select a student.'));
        },
      ),
    );
  }
}
