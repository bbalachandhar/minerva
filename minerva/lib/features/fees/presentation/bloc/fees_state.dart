part of 'fees_bloc.dart';

abstract class FeesState extends Equatable {
  const FeesState();

  @override
  List<Object> get props => [];
}

class FeesInitial extends FeesState {}

class FeesLoading extends FeesState {}

class FeesLoaded extends FeesState {
  final FeeGrandTotal grandTotal;
  final List<Fee> fees;
  final List<TransportFee> transportFees;

  const FeesLoaded({
    required this.grandTotal,
    required this.fees,
    required this.transportFees,
  });

  @override
  List<Object> get props => [grandTotal, fees, transportFees];
}

class FeesError extends FeesState {
  final String message;

  const FeesError(this.message);

  @override
  List<Object> get props => [message];
}
