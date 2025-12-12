part of 'offline_payment_bloc.dart';

abstract class OfflinePaymentState extends Equatable {
  const OfflinePaymentState();

  @override
  List<Object> get props => [];
}

class OfflinePaymentInitial extends OfflinePaymentState {}

class OfflinePaymentLoading extends OfflinePaymentState {}

class OfflinePaymentLoaded extends OfflinePaymentState {
  final List<OfflinePayment> payments;

  const OfflinePaymentLoaded({required this.payments});

  @override
  List<Object> get props => [payments];
}

class OfflinePaymentError extends OfflinePaymentState {
  final String message;

  const OfflinePaymentError(this.message);

  @override
  List<Object> get props => [message];
}
