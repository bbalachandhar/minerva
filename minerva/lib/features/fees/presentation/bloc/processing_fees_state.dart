part of 'processing_fees_bloc.dart';

abstract class ProcessingFeesState extends Equatable {
  const ProcessingFeesState();

  @override
  List<Object> get props => [];
}

class ProcessingFeesInitial extends ProcessingFeesState {}

class ProcessingFeesLoading extends ProcessingFeesState {}

class ProcessingFeesLoaded extends ProcessingFeesState {
  final ProcessingFeeGrandTotal grandTotal;
  final List<Fee> fees;
  final List<TransportFee> transportFees;

  const ProcessingFeesLoaded({
    required this.grandTotal,
    required this.fees,
    required this.transportFees,
  });

  @override
  List<Object> get props => [grandTotal, fees, transportFees];
}

class ProcessingFeesError extends ProcessingFeesState {
  final String message;

  const ProcessingFeesError(this.message);

  @override
  List<Object> get props => [message];
}
