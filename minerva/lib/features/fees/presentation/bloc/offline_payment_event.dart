part of 'offline_payment_bloc.dart';

abstract class OfflinePaymentEvent extends Equatable {
  const OfflinePaymentEvent();

  @override
  List<Object> get props => [];
}

class FetchOfflinePayments extends OfflinePaymentEvent {}
