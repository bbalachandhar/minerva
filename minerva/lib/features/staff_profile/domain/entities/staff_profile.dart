import 'package:equatable/equatable.dart';

class StaffProfile extends Equatable {
  final String id;
  final String? prefix;
  final String? ugQualification;
  final String? pgQualification;
  final String? higherQualification;
  final String? qualifiedExam;
  final String? subjectSpecialization;
  final String? additionalQualification;
  final String? employeeId;
  final String? biometricId;
  final String? langId;
  final String? currencyId;
  final String? department;
  final String? designation;
  final String? qualification;
  final String? workExp;
  final String? name;
  final String? surname;
  final String? fatherName;
  final String? motherName;
  final String? contactNo;
  final String? emergencyContactNo;
  final String? email;
  final String? dob;
  final String? maritalStatus;
  final String? dateOfJoining;
  final String? dateOfLeaving;
  final String? localAddress;
  final String? permanentAddress;
  final String? note;
  final String? image;
  final String? password;
  final String? gender;
  final String? accountTitle;
  final String? bankAccountNo;
  final String? bankName;
  final String? ifscCode;
  final String? bankBranch;
  final String? payscale;
  final String? basicSalary;
  final String? epfNo;
  final String? contractType;
  final String? shift;
  final String? location;
  final String? facebook;
  final String? twitter;
  final String? linkedin;
  final String? instagram;
  final String? resume;
  final String? joiningLetter;
  final String? resignationLetter;
  final String? otherDocumentName;
  final String? otherDocumentFile;
  final String? userId;
  final String? isActive;
  final String? verificationCode;
  final String? disableAt;
  final String? createdAt;
  final String? updatedAt;
  final String? appKey;
  final String? roleId;
  final String? userType;
  final bool canEditProfile; // New field

  const StaffProfile({
    required this.id,
    this.prefix,
    this.ugQualification,
    this.pgQualification,
    this.higherQualification,
    this.qualifiedExam,
    this.subjectSpecialization,
    this.additionalQualification,
    this.employeeId,
    this.biometricId,
    this.langId,
    this.currencyId,
    this.department,
    this.designation,
    this.qualification,
    this.workExp,
    this.name,
    this.surname,
    this.fatherName,
    this.motherName,
    this.contactNo,
    this.emergencyContactNo,
    this.email,
    this.dob,
    this.maritalStatus,
    this.dateOfJoining,
    this.dateOfLeaving,
    this.localAddress,
    this.permanentAddress,
    this.note,
    this.image,
    this.password,
    this.gender,
    this.accountTitle,
    this.bankAccountNo,
    this.bankName,
    this.ifscCode,
    this.bankBranch,
    this.payscale,
    this.basicSalary,
    this.epfNo,
    this.contractType,
    this.shift,
    this.location,
    this.facebook,
    this.twitter,
    this.linkedin,
    this.instagram,
    this.resume,
    this.joiningLetter,
    this.resignationLetter,
    this.otherDocumentName,
    this.otherDocumentFile,
    this.userId,
    this.isActive,
    this.verificationCode,
    this.disableAt,
    this.createdAt,
    this.updatedAt,
    this.appKey,
    this.roleId,
    this.userType,
    this.canEditProfile = false, // Default to false
  });

  factory StaffProfile.fromJson(Map<String, dynamic> json) {
    return StaffProfile(
      id: json['id'],
      prefix: json['prefix'],
      ugQualification: json['ug_qualification'],
      pgQualification: json['pg_qualification'],
      higherQualification: json['higher_qualification'],
      qualifiedExam: json['qualified_exam'],
      subjectSpecialization: json['subject_specialization'],
      additionalQualification: json['additional_qualification'],
      employeeId: json['employee_id'],
      biometricId: json['biometric_id'],
      langId: json['lang_id'],
      currencyId: json['currency_id'],
      department: json['department'],
      designation: json['designation'],
      qualification: json['qualification'],
      workExp: json['work_exp'],
      name: json['name'],
      surname: json['surname'],
      fatherName: json['father_name'],
      motherName: json['mother_name'],
      contactNo: json['contact_no'],
      emergencyContactNo: json['emergency_contact_no'],
      email: json['email'],
      dob: json['dob'],
      maritalStatus: json['marital_status'],
      dateOfJoining: json['date_of_joining'],
      dateOfLeaving: json['date_of_leaving'],
      localAddress: json['local_address'],
      permanentAddress: json['permanent_address'],
      note: json['note'],
      image: json['image'],
      password: json['password'],
      gender: json['gender'],
      accountTitle: json['account_title'],
      bankAccountNo: json['bank_account_no'],
      bankName: json['bank_name'],
      ifscCode: json['ifsc_code'],
      bankBranch: json['bank_branch'],
      payscale: json['payscale'],
      basicSalary: json['basic_salary'],
      epfNo: json['epf_no'],
      contractType: json['contract_type'],
      shift: json['shift'],
      location: json['location'],
      facebook: json['facebook'],
      twitter: json['twitter'],
      linkedin: json['linkedin'],
      instagram: json['instagram'],
      resume: json['resume'],
      joiningLetter: json['joining_letter'],
      resignationLetter: json['resignation_letter'],
      otherDocumentName: json['other_document_name'],
      otherDocumentFile: json['other_document_file'],
      userId: json['user_id'],
      isActive: json['is_active'],
      verificationCode: json['verification_code'],
      disableAt: json['disable_at'],
      createdAt: json['created_at'],
      updatedAt: json['updated_at'],
      appKey: json['app_key'],
      roleId: json['role_id'],
      userType: json['user_type'],
      canEditProfile: json['can_edit_profile'] ?? false,
    );
  }

  @override
  List<Object?> get props => [
        id, prefix, ugQualification, pgQualification, higherQualification,
        qualifiedExam, subjectSpecialization, additionalQualification, employeeId,
        biometricId, langId, currencyId, department, designation, qualification,
        workExp, name, surname, fatherName, motherName, contactNo,
        emergencyContactNo, email, dob, maritalStatus, dateOfJoining,
        dateOfLeaving, localAddress, permanentAddress, note, image, password,
        gender, accountTitle, bankAccountNo, bankName, ifscCode, bankBranch,
        payscale, basicSalary, epfNo, contractType, shift, location, facebook,
        twitter, linkedin, instagram, resume, joiningLetter, resignationLetter,
        otherDocumentName, otherDocumentFile, userId, isActive, verificationCode,
        disableAt, createdAt, updatedAt, appKey, roleId, userType, canEditProfile,
      ];
}