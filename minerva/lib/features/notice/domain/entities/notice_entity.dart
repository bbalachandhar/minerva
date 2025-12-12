// minerva/lib/features/notice/domain/entities/notice_entity.dart
import 'package:equatable/equatable.dart';

class NoticeEntity extends Equatable {
  const NoticeEntity({
    required this.id,
    required this.title,
    required this.message,
    required this.date,
    required this.publishDate,
    required this.createdBy,
    required this.employeeId,
    required this.attachment,
  });

  factory NoticeEntity.fromJson(Map<String, dynamic> json) {
    return NoticeEntity(
      id: json['id'] as String,
      title: json['title'] as String,
      message: json['message'] as String,
      date: json['date'] as String,
      publishDate: json['publish_date'] as String,
      createdBy: json['created_by'] as String,
      employeeId: json['employee_id'] as String,
      attachment: json['attachment'] as String,
    );
  }

  final String id;
  final String title;
  final String message;
  final String date;
  final String publishDate;
  final String createdBy;
  final String employeeId;
  final String attachment;

  NoticeEntity copyWith({
    String? id,
    String? title,
    String? message,
    String? date,
    String? publishDate,
    String? createdBy,
    String? employeeId,
    String? attachment,
  }) {
    return NoticeEntity(
      id: id ?? this.id,
      title: title ?? this.title,
      message: message ?? this.message,
      date: date ?? this.date,
      publishDate: publishDate ?? this.publishDate,
      createdBy: createdBy ?? this.createdBy,
      employeeId: employeeId ?? this.employeeId,
      attachment: attachment ?? this.attachment,
    );
  }

  @override
  List<Object?> get props => [
        id,
        title,
        message,
        date,
        publishDate,
        createdBy,
        employeeId,
        attachment,
      ];
}
