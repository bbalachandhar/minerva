import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/transport_route/data/models/transport_route_model.dart';
import 'package:minerva_flutter/utils/app_constants.dart';
import 'package:minerva_flutter/utils/app_utility.dart';
import 'package:minerva_flutter/features/transport_route/data/models/pickup_point_model.dart'; // Corrected import
import '../../data/repositories/transport_route_repository.dart';
import 'transport_route_event.dart';
import 'transport_route_state.dart';

class TransportRouteBloc extends Bloc<TransportRouteEvent, TransportRouteState> {
  final TransportRouteRepository transportRouteRepository;

  TransportRouteBloc({required this.transportRouteRepository}) : super(TransportRouteInitial()) {
    on<FetchTransportRoutes>(_onFetchTransportRoutes);
  }

  void _onFetchTransportRoutes(
    FetchTransportRoutes event,
    Emitter<TransportRouteState> emit,
  ) async {
    emit(TransportRouteLoading());
    try {
      final primaryColor = await AppUtility.getSharedPreference(AppConstants.primaryColourKey);
      final secondaryColor = await AppUtility.getSharedPreference(AppConstants.secondaryColourKey);
      final imagesUrl = await AppUtility.getSharedPreference(AppConstants.imagesUrlKey);

      final data = await transportRouteRepository.getTransportRoutes(
        event.studentId,
      );
      final TransportRouteModel transportRoute = data['transportRoute'];
      final List pickupPoints = data['pickupPoints'];
      final String pickupName = transportRoute.pickupPointName; // Assuming pickupName comes from the main route data

      emit(TransportRouteLoaded(
        transportRoute: transportRoute,
        pickupPoints: List<dynamic>.from(pickupPoints).cast<PickupPointModel>(),
        primaryColor: primaryColor,
        secondaryColor: secondaryColor,
        imagesUrl: imagesUrl,
        pickupName: pickupName,
      ));
    } catch (e) {
      emit(TransportRouteError(message: e.toString()));
    }
  }
}
