import 'package:equatable/equatable.dart';

class Module extends Equatable {
  final String name;
  final String icon;

  const Module({required this.name, required this.icon});

  @override
  List<Object> get props => [name, icon];
}
