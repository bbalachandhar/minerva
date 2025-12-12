// minerva/lib/features/visitor_book/data/models/visitor_model.dart

class VisitorModel {
  final String id;
  final String purpose;
  final String date;
  final String name;
  final String contact;
  final String idProof;
  final String numberOfPeople;
  final String note;
  final String inTime;
  final String outTime;
  final String image; // Filename for the image/document

  VisitorModel({
    required this.id,
    required this.purpose,
    required this.date,
    required this.name,
    required this.contact,
    required this.idProof,
    required this.numberOfPeople,
    required this.note,
    required this.inTime,
    required this.outTime,
    required this.image,
  });

  factory VisitorModel.fromJson(Map<String, dynamic> json) {
    return VisitorModel(
      id: json['id'] ?? '',
      purpose: json['purpose'] ?? '',
      date: json['date'] ?? '',
      name: json['name'] ?? '',
      contact: json['contact'] ?? '',
      idProof: json['id_proof'] ?? '',
      numberOfPeople: json['no_of_people'] ?? '',
      note: json['note'] ?? '',
      inTime: json['in_time'] ?? '',
      outTime: json['out_time'] ?? '',
      image: json['image'] ?? '',
    );
  }
}
