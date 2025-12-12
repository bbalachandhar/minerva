import 'package:equatable/equatable.dart';

class AboutSchoolDetails extends Equatable {
  final String name;
  final String email;
  final String phone;
  final String address;
  final String diseCode;
  final String session;
  final String startMonthName;
  final String appLogo;

  const AboutSchoolDetails({
    required this.name,
    required this.email,
    required this.phone,
    required this.address,
    required this.diseCode,
    required this.session,
    required this.startMonthName,
    required this.appLogo,
  });

  @override
  List<Object> get props => [
        name,
        email,
        phone,
        address,
        diseCode,
        session,
        startMonthName,
        appLogo,
      ];
}
