import 'package:bloc/bloc.dart';
import 'package:equatable/equatable.dart';
import 'package:minerva_flutter/features/fees/data/repositories/fees_repository.dart';
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';

part 'fees_event.dart';
part 'fees_state.dart';

class FeesBloc extends Bloc<FeesEvent, FeesState> {
  final FeesRepository feesRepository;

  FeesBloc({required this.feesRepository}) : super(FeesInitial()) {
    on<FetchFees>(_onFetchFees);
  }

  void _onFetchFees(FetchFees event, Emitter<FeesState> emit) async {
    emit(FeesLoading());
    try {
      final feesData = await feesRepository.getFees();
      emit(FeesLoaded(
        grandTotal: feesData['grandTotal'],
        fees: feesData['feeItems'],
        transportFees: feesData['transportFees'],
      ));
    } catch (e) {
      emit(FeesError(e.toString()));
    }
  }
}
