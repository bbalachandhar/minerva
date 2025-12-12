part of 'fees_bloc.dart';

abstract class FeesEvent extends Equatable {
  const FeesEvent();

  @override
  List<Object> get props => [];
}

class FetchFees extends FeesEvent {}
