import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../bloc/teachers_rating_bloc.dart';
import '../../bloc/teachers_rating_event.dart';
import 'package:minerva_flutter/features/teachers_rating/presentation/widgets/teacher_list_item.dart';
import '../../bloc/teachers_rating_state.dart';
import '../../data/repositories/teacher_repository.dart';

class TeachersRatingPage extends StatelessWidget {
  const TeachersRatingPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (context) => TeachersRatingBloc(
        teacherRepository: RepositoryProvider.of<TeacherRepository>(context),
      )..add(FetchTeachers()),
      child: Scaffold(
        body: NestedScrollView(
          headerSliverBuilder: (BuildContext context, bool innerBoxIsScrolled) {
            return <Widget>[
              SliverAppBar(
                expandedHeight: 200.0,
                floating: false,
                pinned: true,
                flexibleSpace: FlexibleSpaceBar(
                  title: const Text('Teachers'),
                  background: Image.asset(
                    'assets/teacherreviewpage.jpg',
                    fit: BoxFit.cover,
                  ),
                ),
              ),
            ];
          },
          body: BlocBuilder<TeachersRatingBloc, TeachersRatingState>(
            builder: (context, state) {
              if (state is TeachersRatingLoading) {
                return const Center(child: CircularProgressIndicator());
              } else if (state is TeachersRatingLoaded) {
                return ListView.builder(
                  itemCount: state.teachers.length,
                  itemBuilder: (context, index) {
                    final teacher = state.teachers[index];
                    return TeacherListItem(teacher: teacher);
                  },
                );
              } else if (state is TeachersRatingError) {
                return Center(child: Text(state.message));
              }
              return const Center(child: Text('Please wait...'));
            },
          ),
        ),
      ),
    );
  }
}
