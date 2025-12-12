import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/fees/data/repositories/fees_repository.dart';
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';

part 'processing_fees_event.dart';
part 'processing_fees_state.dart';

class ProcessingFeesBloc extends Bloc<ProcessingFeesEvent, ProcessingFeesState> {
  final FeesRepository feesRepository;

  ProcessingFeesBloc({required this.feesRepository}) : super(ProcessingFeesInitial()) {
    on<FetchProcessingFees>(_onFetchProcessingFees);
  }

  void _onFetchProcessingFees(FetchProcessingFees event, Emitter<ProcessingFeesState> emit) async {
    emit(ProcessingFeesLoading());
    try {
      final feesData = await feesRepository.getProcessingFees();
      emit(ProcessingFeesLoaded(
        grandTotal: feesData['grandTotal'],
        fees: feesData['feeItems'],
        transportFees: feesData['transportFees'],
      ));
    } catch (e) {
      emit(ProcessingFeesError(e.toString()));
    }
  }
}
