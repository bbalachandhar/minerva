import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/dashboard/data/repositories/dashboard_repository.dart';
import 'package:minerva_flutter/features/dashboard/domain/entities/module.dart';

part 'dashboard_event.dart';
part 'dashboard_state.dart';

class DashboardBloc extends Bloc<DashboardEvent, DashboardState> {
  final DashboardRepository dashboardRepository;

  DashboardBloc({required this.dashboardRepository}) : super(DashboardInitial()) {
    on<FetchDashboardModules>(_onFetchDashboardModules);
  }

  void _onFetchDashboardModules(
      FetchDashboardModules event, Emitter<DashboardState> emit) async {
    emit(DashboardLoading());
    try {
      final elearningModules = await dashboardRepository.getElearningModules();
      final academicModules = await dashboardRepository.getAcademicModules();
      final communicateModules = await dashboardRepository.getCommunicateModules();
      final otherModules = await dashboardRepository.getOtherModules();
      emit(DashboardLoaded(
        elearningModules: elearningModules,
        academicModules: academicModules,
        communicateModules: communicateModules,
        otherModules: otherModules,
      ));
    } catch (e) {
      emit(DashboardError(e.toString()));
    }
  }
}
