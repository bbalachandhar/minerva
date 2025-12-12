// minerva/lib/features/visitor_book/presentation/bloc/visitor_state.dart

import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/visitor_book/data/models/visitor_model.dart';

abstract class VisitorState extends Equatable {
  const VisitorState();

  @override
  List<Object> get props => [];
}

class VisitorInitial extends VisitorState {}

class VisitorLoading extends VisitorState {}

class VisitorLoaded extends VisitorState {
  final List<VisitorModel> visitors;
  final String primaryColor;
  final String secondaryColor;
  final String imagesUrl;
  final String dateFormat;

  const VisitorLoaded({
    required this.visitors,
    required this.primaryColor,
    required this.secondaryColor,
    required this.imagesUrl,
    required this.dateFormat,
  });

  @override
  List<Object> get props => [visitors, primaryColor, secondaryColor, imagesUrl, dateFormat];
}

class VisitorError extends VisitorState {
  final String message;

  const VisitorError({required this.message});

  @override
  List<Object> get props => [message];
}
