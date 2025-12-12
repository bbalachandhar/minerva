// minerva/lib/data/models/transport_route_model.dart

class TransportRouteModel {
  final String routeTitle;
  final String pickupPointName;
  final String vehicleNo;
  final String vehiclePhoto;
  final String driverName;
  final String driverContact;
  final String vehicleModel;
  final String driverLicence;
  final String manufactureYear;

  TransportRouteModel({
    required this.routeTitle,
    required this.pickupPointName,
    required this.vehicleNo,
    required this.vehiclePhoto,
    required this.driverName,
    required this.driverContact,
    required this.vehicleModel,
    required this.driverLicence,
    required this.manufactureYear,
  });

  factory TransportRouteModel.fromJson(Map<String, dynamic> json) {
    return TransportRouteModel(
      routeTitle: json['route_title'] ?? '',
      pickupPointName: json['pickup_point_name'] ?? '',
      vehicleNo: json['vehicle_no'] ?? '',
      vehiclePhoto: json['vehicle_photo'] ?? '',
      driverName: json['driver_name'] ?? '',
      driverContact: json['driver_contact'] ?? '',
      vehicleModel: json['vehicle_model'] ?? '',
      driverLicence: json['driver_licence'] ?? '',
      manufactureYear: json['manufacture_year'] ?? '',
    );
  }
}
