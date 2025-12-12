import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/teachers_rating/data/models/teacher_subject_model.dart';
import 'package:minerva_flutter/features/teachers_rating/data/repositories/teacher_repository.dart';

class TeacherDetailsDialog extends StatefulWidget {
  final String staffId;

  const TeacherDetailsDialog({super.key, required this.staffId});

  @override
  State<TeacherDetailsDialog> createState() => _TeacherDetailsDialogState();
}

class _TeacherDetailsDialogState extends State<TeacherDetailsDialog> {
  late Future<List<TeacherSubject>> _subjectsFuture;

  @override
  void initState() {
    super.initState();
    _subjectsFuture = RepositoryProvider.of<TeacherRepository>(context)
        .getTeacherSubjects(widget.staffId);
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _buildHeader(context),
            const SizedBox(height: 16),
            _buildSubjectList(),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        const Text('Subject Details', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
        IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ],
    );
  }

  Widget _buildSubjectList() {
    return FutureBuilder<List<TeacherSubject>>(
      future: _subjectsFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        } else if (snapshot.hasError) {
          return Center(child: Text('Error: ${snapshot.error}'));
        } else if (!snapshot.hasData || snapshot.data!.isEmpty) {
          return const Center(child: Text('No subjects found.'));
        } else {
          final subjects = snapshot.data!;
          return Column(
            children: [
              _buildListHeader(),
              const Divider(),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: subjects.length,
                itemBuilder: (context, index) {
                  return _buildSubjectListItem(subjects[index]);
                },
              ),
            ],
          );
        }
      },
    );
  }

  Widget _buildListHeader() {
    return const Row(
      children: [
        Expanded(child: Text('Time', style: TextStyle(fontWeight: FontWeight.bold))),
        Expanded(child: Text('Day', style: TextStyle(fontWeight: FontWeight.bold))),
        Expanded(child: Text('Subject', style: TextStyle(fontWeight: FontWeight.bold))),
        Expanded(child: Text('Room', style: TextStyle(fontWeight: FontWeight.bold))),
      ],
    );
  }

  Widget _buildSubjectListItem(TeacherSubject subject) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        children: [
          Expanded(child: Text(subject.time)),
          Expanded(child: Text(subject.day)),
          Expanded(child: Text(subject.subjectName)),
          Expanded(child: Text(subject.roomNo)),
        ],
      ),
    );
  }
}
