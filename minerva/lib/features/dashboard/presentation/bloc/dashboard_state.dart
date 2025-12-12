part of 'dashboard_bloc.dart';

abstract class DashboardState extends Equatable {
  const DashboardState();

  @override
  List<Object> get props => [];
}

class DashboardInitial extends DashboardState {}

class DashboardLoading extends DashboardState {}

class DashboardLoaded extends DashboardState {
  final List<Module> elearningModules;
  final List<Module> academicModules;
  final List<Module> communicateModules;
  final List<Module> otherModules;

  const DashboardLoaded({
    required this.elearningModules,
    required this.academicModules,
    required this.communicateModules,
    required this.otherModules,
  });

  @override
  List<Object> get props => [
        elearningModules,
        academicModules,
        communicateModules,
        otherModules,
      ];
}

class DashboardError extends DashboardState {
  final String message;

  const DashboardError(this.message);

  @override
  List<Object> get props => [message];
}
