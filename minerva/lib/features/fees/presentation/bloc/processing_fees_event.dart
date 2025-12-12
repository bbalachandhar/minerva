part of 'processing_fees_bloc.dart';

abstract class ProcessingFeesEvent extends Equatable {
  const ProcessingFeesEvent();

  @override
  List<Object> get props => [];
}

class FetchProcessingFees extends ProcessingFeesEvent {}
