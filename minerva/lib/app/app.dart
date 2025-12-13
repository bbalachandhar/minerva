import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'package:minerva_flutter/features/about_school/data/repositories/about_school_repository.dart';
import 'package:minerva_flutter/features/about_school/presentation/bloc/about_school_bloc.dart';
import 'package:minerva_flutter/features/about_school/presentation/pages/about_school_page.dart';
import 'package:minerva_flutter/features/auth/data/repositories/authentication_repository.dart';
import 'package:minerva_flutter/features/auth/presentation/bloc/auth_bloc.dart';
import 'package:minerva_flutter/features/auth/presentation/pages/login_page.dart';
import 'package:minerva_flutter/features/dashboard/data/repositories/dashboard_repository.dart';
import 'package:minerva_flutter/features/dashboard/presentation/bloc/dashboard_bloc.dart';
import 'package:minerva_flutter/features/dashboard/presentation/pages/dashboard_page.dart';
import 'package:minerva_flutter/features/fees/presentation/pages/fees_screen.dart';
import 'package:minerva_flutter/features/fees/presentation/pages/processing_fees_screen.dart';
import 'package:minerva_flutter/features/fees/presentation/bloc/offline_payment_bloc.dart';
import 'package:minerva_flutter/features/fees/presentation/bloc/processing_fees_bloc.dart';
import 'package:minerva_flutter/features/profile/data/repositories/profile_repository.dart';
import 'package:minerva_flutter/features/profile/presentation/bloc/profile_bloc.dart';
import 'package:minerva_flutter/features/profile/presentation/pages/profile_page.dart';
import 'package:minerva_flutter/features/splash/presentation/pages/splash_page.dart';
import 'package:minerva_flutter/features/url/data/repositories/settings_repository.dart';
import 'package:minerva_flutter/features/url/presentation/bloc/url_bloc.dart';
import 'package:minerva_flutter/features/url/presentation/pages/url_page.dart';
import 'package:minerva_flutter/features/fees/presentation/pages/offline_payment_screen.dart';

// NEW IMPORTS FOR NOTICE FEATURE
import 'package:minerva_flutter/features/notice/data/repositories/notice_repository_impl.dart';
import 'package:minerva_flutter/features/notice/domain/repositories/notice_repository.dart';
import 'package:minerva_flutter/features/notice/domain/usecases/get_notices_usecase.dart';
import 'package:minerva_flutter/features/notice/presentation/bloc/notice_bloc.dart';
import 'package:minerva_flutter/features/notice/presentation/pages/notice_page.dart';

// Imports for Apply Leave Feature
import 'package:minerva_flutter/features/apply_leave/data/repositories/leave_repository_impl.dart';
import 'package:minerva_flutter/features/apply_leave/domain/repositories/leave_repository.dart';
import 'package:minerva_flutter/features/apply_leave/domain/usecases/get_leave_applications_usecase.dart';
import 'package:minerva_flutter/features/apply_leave/domain/usecases/delete_leave_application_usecase.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/bloc/apply_leave_bloc.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/pages/apply_leave_page.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/pages/add_leave_page.dart';
import 'package:minerva_flutter/features/apply_leave/presentation/pages/edit_leave_page.dart';
import 'package:minerva_flutter/features/apply_leave/domain/entities/leave_application_entity.dart';

import 'package:minerva_flutter/features/apply_leave/domain/usecases/edit_leave_application_usecase.dart';
import 'package:minerva_flutter/features/apply_leave/domain/usecases/submit_leave_application_usecase.dart';
import 'package:minerva_flutter/features/transport_route/data/repositories/transport_route_repository.dart';
import 'package:minerva_flutter/features/transport_route/presentation/bloc/transport_route_bloc.dart';
import 'package:minerva_flutter/features/transport_route/presentation/pages/transport_route_page.dart';
import 'package:minerva_flutter/features/visitor_book/data/repositories/visitor_repository.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/bloc/visitor_bloc.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/pages/visitor_book_page.dart';
import 'package:minerva_flutter/features/visitor_book/presentation/pages/document_viewer_page.dart';
import 'package:minerva_flutter/features/hostel/presentation/pages/hostel_rooms_page.dart';
import 'package:minerva_flutter/features/hostel/data/repositories/hostel_repository_impl.dart';
import 'package:minerva_flutter/features/hostel/domain/repositories/hostel_repository.dart';
import 'package:minerva_flutter/features/hostel/domain/usecases/get_hostel_rooms_usecase.dart';
import 'package:minerva_flutter/features/hostel/presentation/bloc/hostel_bloc.dart';
import 'package:minerva_flutter/features/calendar_todo/data/repositories/calendar_todo_repository_impl.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/repositories/calendar_todo_repository.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/create_calendar_todo_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/delete_calendar_todo_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/get_calendar_todos_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/mark_calendar_todo_as_complete_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/domain/usecases/update_calendar_todo_usecase.dart';
import 'package:minerva_flutter/features/calendar_todo/presentation/bloc/calendar_todo_bloc.dart';
import 'package:minerva_flutter/features/calendar_todo/presentation/pages/calendar_todo_page.dart';
import 'package:minerva_flutter/features/library/data/repositories/library_repository_impl.dart';
import 'package:minerva_flutter/features/library/domain/repositories/library_repository.dart';
import 'package:minerva_flutter/features/library/domain/usecases/get_available_books_usecase.dart';
import 'package:minerva_flutter/features/library/domain/usecases/get_issued_books_usecase.dart';
import 'package:minerva_flutter/features/teachers_rating/data/repositories/teacher_repository.dart';
import 'package:minerva_flutter/features/library/presentation/bloc/library_bloc.dart';
import 'package:minerva_flutter/features/library/presentation/pages/library_page.dart';
import 'package:minerva_flutter/features/teachers_rating/presentation/pages/teachers_rating_page.dart';

import 'package:minerva_flutter/features/staff_profile/data/repositories/staff_profile_repository.dart';
import 'package:minerva_flutter/features/staff_profile/presentation/bloc/staff_profile_bloc.dart';
import 'package:minerva_flutter/features/staff_profile/presentation/pages/staff_profile_page.dart';

import '../features/dashboard/presentation/pages/staff_dashboard_page.dart';
import '../features/fees/data/repositories/fees_repository.dart';
import '../features/fees/presentation/bloc/fees_bloc.dart';



class App extends StatelessWidget {
  final SharedPreferences sharedPreferences;

  App({Key? key, required this.sharedPreferences}) : super(key: key);

  late final GoRouter _router = GoRouter(
    initialLocation: sharedPreferences.getString('base_url') == null ? '/url' : '/splash',
    routes: <GoRoute>[
      GoRoute(
        path: '/url',
        builder: (BuildContext context, GoRouterState state) {
          return const UrlPage();
        },
      ),
      GoRoute(
        path: '/splash',
        builder: (BuildContext context, GoRouterState state) {
          return const SplashPage();
        },
      ),
      GoRoute(
        path: '/login',
        builder: (BuildContext context, GoRouterState state) {
          return const LoginPage();
        },
      ),
      GoRoute(
        path: '/dashboard',
        builder: (BuildContext context, GoRouterState state) {
          return const DashboardPage();
        },
      ),
      GoRoute(
        path: '/staff/dashboard',
        builder: (BuildContext context, GoRouterState state) {
          return const StaffDashboardPage();
        },
      ),
      GoRoute(
        path: '/staff/profile',
        builder: (BuildContext context, GoRouterState state) {
          return const StaffProfilePage();
        },
      ),
      GoRoute(
        path: '/profile', // New route for ProfilePage
        builder: (BuildContext context, GoRouterState state) {
          return const ProfilePage();
        },
      ),
      GoRoute(
        path: '/about_school', // New route for AboutSchoolPage
        builder: (BuildContext context, GoRouterState state) {
          return const AboutSchoolPage();
        },
      ),
      GoRoute(
        path: '/fees', // New route for FeesScreen
        builder: (BuildContext context, GoRouterState state) {
          return FeesScreen();
        },
      ),
      GoRoute(
        path: '/fees/processing',
        builder: (BuildContext context, GoRouterState state) {
          return ProcessingFeesScreen();
        },
      ),
      GoRoute(
        path: '/fees/offline',
        builder: (BuildContext context, GoRouterState state) {
          return OfflinePaymentScreen();
        },
      ),
      GoRoute(
        path: '/notice_board', // New route for NoticePage
        builder: (BuildContext context, GoRouterState state) {
          return const NoticePage();
        },
      ),
      GoRoute(
        path: '/apply_leave',
        builder: (BuildContext context, GoRouterState state) {
          return const ApplyLeavePage();
        },
        routes: [
          GoRoute(
            path: 'add',
            builder: (BuildContext context, GoRouterState state) {
              return const AddLeavePage();
            },
          ),
          GoRoute(
            path: 'edit',
            builder: (BuildContext context, GoRouterState state) {
              final leaveApplication = state.extra as LeaveApplicationEntity;
              return EditLeavePage(leaveApplication: leaveApplication);
            },
          ),
        ],
      ),
      GoRoute(
        path: '/transport_routes',
        builder: (BuildContext context, GoRouterState state) {
          return const TransportRoutePage();
        },
      ),
      GoRoute(
        path: '/visitor_book',
        builder: (BuildContext context, GoRouterState state) {
          return const VisitorBookPage();
        },
      ),
      GoRoute(
        path: '/document_viewer',
        builder: (BuildContext context, GoRouterState state) {
          final Map<String, dynamic> args = state.extra as Map<String, dynamic>;
          return DocumentViewerPage(
            documentUrl: args['documentUrl'],
            title: args['title'],
          );
        },
      ),
      GoRoute(
        path: '/hostel',
        builder: (BuildContext context, GoRouterState state) {
          return const HostelRoomsPage();
        },
      ),
      GoRoute(
        path: '/calendar_todo',
        builder: (BuildContext context, GoRouterState state) {
          return const CalendarTodoPage();
        },
      ),
      GoRoute(
        path: '/library',
        builder: (BuildContext context, GoRouterState state) {
          return const LibraryPage();
        },
      ),
      GoRoute(
        path: '/teachers_rating',
        builder: (BuildContext context, GoRouterState state) {
          return const TeachersRatingPage();
        },
      ),
    ],
  );

  @override
  Widget build(BuildContext context) {
    return MultiRepositoryProvider(
      providers: [
        RepositoryProvider<AuthenticationRepository>(
          create: (context) => AuthenticationRepository(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<SettingsRepository>(
          create: (context) => SettingsRepository(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<DashboardRepository>(
          create: (context) => DashboardRepository(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<ProfileRepository>(
          create: (context) => ProfileRepository(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<AboutSchoolRepository>(
          create: (context) => AboutSchoolRepository(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<FeesRepository>(
          create: (context) => FeesRepository(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<NoticeRepository>(
          create: (context) => NoticeRepositoryImpl(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<LeaveRepository>(
          create: (context) => LeaveRepositoryImpl(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<TransportRouteRepository>(
          create: (context) => TransportRouteRepositoryImpl(
            sharedPreferences: sharedPreferences,
          ),
        ),
        RepositoryProvider<VisitorRepository>(
          create: (context) => VisitorRepositoryImpl(
            sharedPreferences: sharedPreferences,
          ),
        ),
        RepositoryProvider<HostelRepository>(
          create: (context) => HostelRepositoryImpl(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<CalendarTodoRepository>(
          create: (context) => CalendarTodoRepositoryImpl(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<LibraryRepository>(
          create: (context) => LibraryRepositoryImpl(sharedPreferences: sharedPreferences),
        ),
        RepositoryProvider<TeacherRepository>(
          create: (context) => TeacherRepository(),
        ),
        RepositoryProvider<StaffProfileRepository>(
          create: (context) => StaffProfileRepository(sharedPreferences: sharedPreferences),
        ),
      ],
      child: MultiBlocProvider(
        providers: [
          BlocProvider(
            create: (context) => AuthBloc(
              authenticationRepository:
                  RepositoryProvider.of<AuthenticationRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => UrlBloc(
              settingsRepository:
                  RepositoryProvider.of<SettingsRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => DashboardBloc(
              dashboardRepository:
                  RepositoryProvider.of<DashboardRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => ProfileBloc(
              profileRepository:
                  RepositoryProvider.of<ProfileRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => AboutSchoolBloc(
              aboutSchoolRepository:
                  RepositoryProvider.of<AboutSchoolRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => FeesBloc(
              feesRepository:
                  RepositoryProvider.of<FeesRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => ProcessingFeesBloc(
              feesRepository:
                  RepositoryProvider.of<FeesRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => OfflinePaymentBloc(
              feesRepository:
                  RepositoryProvider.of<FeesRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => NoticeBloc(
              getNoticesUseCase: GetNoticesUseCase(
                repository: RepositoryProvider.of<NoticeRepository>(context),
              ),
            ),
          ),
          BlocProvider(
            create: (context) => ApplyLeaveBloc(
              getLeaveApplicationsUseCase: GetLeaveApplicationsUseCase(
                repository: RepositoryProvider.of<LeaveRepository>(context),
              ),
              deleteLeaveApplicationUseCase: DeleteLeaveApplicationUseCase(
                repository: RepositoryProvider.of<LeaveRepository>(context),
              ),
              submitLeaveApplicationUseCase: SubmitLeaveApplicationUseCase(
                repository: RepositoryProvider.of<LeaveRepository>(context),
              ),
              editLeaveApplicationUseCase: EditLeaveApplicationUseCase(
                repository: RepositoryProvider.of<LeaveRepository>(context),
              ),
            ),
          ),
          BlocProvider(
            create: (context) => TransportRouteBloc(
              transportRouteRepository: RepositoryProvider.of<TransportRouteRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => VisitorBloc(
              visitorRepository: RepositoryProvider.of<VisitorRepository>(context),
            ),
          ),
          BlocProvider(
            create: (context) => HostelBloc(
              getHostelRoomsUseCase: GetHostelRoomsUseCase(
                repository: RepositoryProvider.of<HostelRepository>(context),
              ),
            ),
          ),
          BlocProvider(
            create: (context) => CalendarTodoBloc(
              getCalendarTodosUseCase: GetCalendarTodosUseCase(
                repository: RepositoryProvider.of<CalendarTodoRepository>(context),
              ),
              createCalendarTodoUseCase: CreateCalendarTodoUseCase(
                repository: RepositoryProvider.of<CalendarTodoRepository>(context),
              ),
              updateCalendarTodoUseCase: UpdateCalendarTodoUseCase(
                repository: RepositoryProvider.of<CalendarTodoRepository>(context),
              ),
              deleteCalendarTodoUseCase: DeleteCalendarTodoUseCase(
                repository: RepositoryProvider.of<CalendarTodoRepository>(context),
              ),
              markCalendarTodoAsCompleteUseCase: MarkCalendarTodoAsCompleteUseCase(
                repository: RepositoryProvider.of<CalendarTodoRepository>(context),
              ),
            ),
          ),
          BlocProvider(
            create: (context) => LibraryBloc(
              getAvailableBooksUseCase: GetAvailableBooksUseCase(
                repository: RepositoryProvider.of<LibraryRepository>(context),
              ),
              getIssuedBooksUseCase: GetIssuedBooksUseCase(
                repository: RepositoryProvider.of<LibraryRepository>(context),
              ),
            ),
          ),
          BlocProvider(
            create: (context) => StaffProfileBloc(
              staffProfileRepository:
                  RepositoryProvider.of<StaffProfileRepository>(context),
            ),
          ),
        ],
        child: MaterialApp.router(
          routerConfig: _router,
          title: 'Minerva Flutter',
          theme: ThemeData(
            primarySwatch: Colors.blue,
          ),
        ),
      ),
    );
  }
}

