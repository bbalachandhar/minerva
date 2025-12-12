import 'package:equatable/equatable.dart';

class Profile extends Equatable {
  final String firstName;
  final String lastName;
  final String admissionNo;
  final String rollNo;
  final String className; // 'class' is a reserved keyword in Dart
  final String section;
  final String session;
  final String behaviourScore;
  final String barcodeImageUrl;
  final String qrcodeImageUrl;
  final String profileImageUrl;
  final String gender;

  // Parents Details
  final String fatherName;
  final String fatherPhone;
  final String fatherOccupation;
  final String fatherImageUrl;
  final String motherName;
  final String motherPhone;
  final String motherOccupation;
  final String motherImageUrl;
  final String guardianName;
  final String guardianPhone;
  final String guardianOccupation;
  final String guardianRelation;
  final String guardianEmail;
  final String guardianAddress;
  final String guardianImageUrl;

  // Other Details
  final String previousSchool;
  final String nationalIdNo; // adhar_no
  final String localIdNo;    // samagra_id
  final String bankAccountNo;
  final String bankName;
  final String ifscCode;
  final String rte;
  final String studentHouse; // house_name
  final String pickupPoint;  // pickup_point_name
  final String vehicleRoute; // route_title
  final String vehicleNo;
  final String driverName;
  final String driverContact;
  final String hostelName;   // hostel_name
  final String roomNo;       // room_no
  final String roomType;     // room_type


  const Profile({
    required this.firstName,
    required this.lastName,
    required this.admissionNo,
    required this.rollNo,
    required this.className,
    required this.section,
    required this.session,
    required this.behaviourScore,
    required this.barcodeImageUrl,
    required this.qrcodeImageUrl,
    required this.profileImageUrl,
    required this.gender,
    // Parents Details
    required this.fatherName,
    required this.fatherPhone,
    required this.fatherOccupation,
    required this.fatherImageUrl,
    required this.motherName,
    required this.motherPhone,
    required this.motherOccupation,
    required this.motherImageUrl,
    required this.guardianName,
    required this.guardianPhone,
    required this.guardianOccupation,
    required this.guardianRelation,
    required this.guardianEmail,
    required this.guardianAddress,
    required this.guardianImageUrl,
    // Other Details
    required this.previousSchool,
    required this.nationalIdNo,
    required this.localIdNo,
    required this.bankAccountNo,
    required this.bankName,
    required this.ifscCode,
    required this.rte,
    required this.studentHouse,
    required this.pickupPoint,
    required this.vehicleRoute,
    required this.vehicleNo,
    required this.driverName,
    required this.driverContact,
    required this.hostelName,
    required this.roomNo,
    required this.roomType,
  });

  @override
  List<Object> get props => [
        firstName,
        lastName,
        admissionNo,
        rollNo,
        className,
        section,
        session,
        behaviourScore,
        barcodeImageUrl,
        qrcodeImageUrl,
        profileImageUrl,
        gender,
        fatherName,
        fatherPhone,
        fatherOccupation,
        fatherImageUrl,
        motherName,
        motherPhone,
        motherOccupation,
        motherImageUrl,
        guardianName,
        guardianPhone,
        guardianOccupation,
        guardianRelation,
        guardianEmail,
        guardianAddress,
        guardianImageUrl,
        previousSchool,
        nationalIdNo,
        localIdNo,
        bankAccountNo,
        bankName,
        ifscCode,
        rte,
        studentHouse,
        pickupPoint,
        vehicleRoute,
        vehicleNo,
        driverName,
        driverContact,
        hostelName,
        roomNo,
        roomType,
      ];
}
