import 'package:equatable/equatable.dart';

class HostelRoomEntity extends Equatable {
  const HostelRoomEntity({
    required this.id,
    required this.hostelName,
    required this.roomType,
    required this.roomNumber,
    required this.numberOfBeds,
    required this.costPerBed,
    required this.status, // This could be 'assigned', 'vacant', etc.
  });

  final String id; // Assuming an ID for unique identification
  final String hostelName;
  final String roomType;
  final String roomNumber;
  final String numberOfBeds;
  final String costPerBed;
  final String status;

  @override
  List<Object?> get props => [
        id,
        hostelName,
        roomType,
        roomNumber,
        numberOfBeds,
        costPerBed,
        status,
      ];
}
