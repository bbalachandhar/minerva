import 'package:minerva_flutter/features/teachers_rating/data/models/teacher_subject_model.dart';
import '../models/teacher_model.dart';

class TeacherRepository {
  Future<List<Teacher>> getTeachers() async {
    // Simulate a network delay
    await Future.delayed(const Duration(seconds: 1));

    // Dummy data based on native app analysis
    return [
      Teacher(
        name: 'John Doe (T001)',
        email: 'john.doe@example.com',
        contact: '+1234567890',
        rating: '4.5',
        comment: 'Great teacher, very helpful!',
        isClassTeacher: true,
        staffId: '1',
      ),
      Teacher(
        name: 'Jane Smith (T002)',
        email: 'jane.smith@example.com',
        contact: '+1987654321',
        rating: '0', // This will show the "Add Rating" button
        comment: '',
        isClassTeacher: false,
        staffId: '2',
      ),
      Teacher(
        name: 'Peter Jones (T003)',
        email: 'peter.jones@example.com',
        contact: '+1122334455',
        rating: '3.0',
        comment: 'Good, but could be more engaging.',
        isClassTeacher: false,
        staffId: '3',
      ),
    ];
  }

  Future<List<TeacherSubject>> getTeacherSubjects(String staffId) async {
    // Simulate a network delay
    await Future.delayed(const Duration(seconds: 1));

    // Dummy data
    return [
      TeacherSubject(
        day: 'Monday',
        time: '09:00 - 10:00',
        subjectName: 'Mathematics',
        roomNo: '101',
      ),
      TeacherSubject(
        day: 'Tuesday',
        time: '11:00 - 12:00',
        subjectName: 'Physics',
        roomNo: '203',
      ),
      TeacherSubject(
        day: 'Wednesday',
        time: '14:00 - 15:00',
        subjectName: 'Mathematics',
        roomNo: '101',
      ),
    ];
  }
}
