import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/fees/data/repositories/fees_repository.dart';
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';

part 'offline_payment_event.dart';
part 'offline_payment_state.dart';

class OfflinePaymentBloc extends Bloc<OfflinePaymentEvent, OfflinePaymentState> {
  final FeesRepository feesRepository;

  OfflinePaymentBloc({required this.feesRepository}) : super(OfflinePaymentInitial()) {
    on<FetchOfflinePayments>(_onFetchOfflinePayments);
  }

  void _onFetchOfflinePayments(FetchOfflinePayments event, Emitter<OfflinePaymentState> emit) async {
    emit(OfflinePaymentLoading());
    try {
      final payments = await feesRepository.getOfflinePayments();
      emit(OfflinePaymentLoaded(payments: payments));
    } catch (e) {
      emit(OfflinePaymentError(e.toString()));
    }
  }
}
