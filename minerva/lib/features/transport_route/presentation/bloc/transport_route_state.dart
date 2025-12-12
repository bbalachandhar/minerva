// minerva/lib/features/transport_route/presentation/bloc/transport_route_state.dart

import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/transport_route/data/models/pickup_point_model.dart';
import 'package:minerva_flutter/features/transport_route/data/models/transport_route_model.dart';

abstract class TransportRouteState extends Equatable {
  const TransportRouteState();

  @override
  List<Object> get props => [];
}

class TransportRouteInitial extends TransportRouteState {}

class TransportRouteLoading extends TransportRouteState {}

class TransportRouteLoaded extends TransportRouteState {
  final TransportRouteModel transportRoute;
  final List<PickupPointModel> pickupPoints;
  final String primaryColor;
  final String secondaryColor;
  final String imagesUrl;
  final String pickupName;

  const TransportRouteLoaded({
    required this.transportRoute,
    required this.pickupPoints,
    required this.primaryColor,
    required this.secondaryColor,
    required this.imagesUrl,
    required this.pickupName,
  });

  @override
  List<Object> get props => [transportRoute, pickupPoints, primaryColor, secondaryColor, imagesUrl, pickupName];
}

class TransportRouteError extends TransportRouteState {
  final String message;

  const TransportRouteError({required this.message});

  @override
  List<Object> get props => [message];
}
