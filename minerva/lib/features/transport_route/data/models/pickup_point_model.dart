// minerva/lib/features/transport_route/data/models/pickup_point_model.dart

class PickupPointModel {
  final String pickupPoint;
  final double destinationDistance;
  final String pickupTime; // HH:mm:ss format

  PickupPointModel({
    required this.pickupPoint,
    required this.destinationDistance,
    required this.pickupTime,
  });

  factory PickupPointModel.fromJson(Map<String, dynamic> json) {
    return PickupPointModel(
      pickupPoint: json['pickup_point'] ?? '',
      destinationDistance: double.tryParse(json['destination_distance'] ?? '0.0') ?? 0.0,
      pickupTime: json['pickup_time'] ?? '',
    );
  }
}
