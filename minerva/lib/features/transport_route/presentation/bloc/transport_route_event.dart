// minerva/lib/features/transport_route/presentation/bloc/transport_route_event.dart

import 'package:equatable/equatable.dart';

abstract class TransportRouteEvent extends Equatable {
  const TransportRouteEvent();

  @override
  List<Object> get props => [];
}

class FetchTransportRoutes extends TransportRouteEvent {
  final String studentId;
  // ApiUrl, clientService, authKey, userId, accessToken will be retrieved from SharedPreferences inside the repository
  // and do not need to be passed with the event.

  const FetchTransportRoutes({
    required this.studentId,
  });

  @override
  List<Object> get props => [studentId];
}
