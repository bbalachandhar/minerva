import 'package:flutter/material.dart';
import 'package:flutter_rating_bar/flutter_rating_bar.dart';
import 'package:minerva_flutter/features/teachers_rating/data/models/teacher_model.dart';
import 'package:minerva_flutter/features/teachers_rating/presentation/widgets/add_rating_dialog.dart';
import 'package:minerva_flutter/features/teachers_rating/presentation/widgets/teacher_details_dialog.dart';

class TeacherListItem extends StatelessWidget {
  final Teacher teacher;

  const TeacherListItem({super.key, required this.teacher});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      elevation: 5,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15.0),
      ),
      child: Column(
        children: [
          _buildHeader(context),
          _buildBody(context),
        ],
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16.0),
      decoration: BoxDecoration(
        color: const Color(0xFFE7F1EE),
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(15.0),
          topRight: Radius.circular(15.0),
        ),
      ),
      child: Row(
        children: [
          Image.asset('assets/ic_teacher.png', width: 25, height: 25),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              teacher.name,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
          ),
          if (teacher.isClassTeacher)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
              decoration: BoxDecoration(
                color: Colors.green,
                borderRadius: BorderRadius.circular(4),
              ),
              child: const Text(
                'Class Teacher',
                style: TextStyle(color: Colors.white, fontSize: 12),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildBody(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(16.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                flex: 2,
                child: Column(
                  children: [
                    _buildContactInfo(
                        'assets/ic_phone.png', teacher.contact),
                    const SizedBox(height: 8),
                    _buildContactInfo('assets/ic_email.png', teacher.email),
                  ],
                ),
              ),
              Expanded(
                flex: 1,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    _buildViewButton(context),
                    const SizedBox(height: 8),
                    _buildRatingSection(context),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Text(
            'Comment',
            style: TextStyle(fontWeight: FontWeight.bold, color: Colors.blue),
          ),
          const SizedBox(height: 4),
          Text(teacher.comment.isEmpty ? 'No comments yet.' : teacher.comment),
        ],
      ),
    );
  }

  Widget _buildContactInfo(String iconPath, String text) {
    return Row(
      children: [
        Image.asset(iconPath, width: 20, height: 20),
        const SizedBox(width: 8),
        Expanded(child: Text(text)),
      ],
    );
  }

  Widget _buildViewButton(BuildContext context) {
    return TextButton.icon(
      onPressed: () {
        showModalBottomSheet(
          context: context,
          builder: (context) => TeacherDetailsDialog(staffId: teacher.staffId),
        );
      },
      icon: Image.asset('assets/ic_view.png', width: 20, height: 20),
      label: const Text('View', style: TextStyle(color: Colors.blue)),
    );
  }

  Widget _buildRatingSection(BuildContext context) {
    final double currentRating = double.tryParse(teacher.rating) ?? 0.0;
    if (currentRating > 0) {
      return RatingBarIndicator(
        rating: currentRating,
        itemBuilder: (context, index) => const Icon(
          Icons.star,
          color: Colors.amber,
        ),
        itemCount: 5,
        itemSize: 20.0,
        direction: Axis.horizontal,
      );
    } else {
      return InkWell(
        onTap: () {
          showDialog(
            context: context,
            builder: (BuildContext context) {
              return const AddRatingDialog();
            },
          );
        },
        child: const Text('Add Rating', style: TextStyle(color: Colors.blue)),
      );
    }
  }
}
