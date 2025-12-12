import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';
import 'package:minerva_flutter/features/fees/presentation/bloc/processing_fees_bloc.dart';
import 'package:minerva_flutter/features/fees/presentation/widgets/fees_skeleton.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ProcessingFeesScreen extends StatefulWidget {
  const ProcessingFeesScreen({Key? key}) : super(key: key);

  @override
  _ProcessingFeesScreenState createState() => _ProcessingFeesScreenState();
}

class _ProcessingFeesScreenState extends State<ProcessingFeesScreen> {
  String _currencySymbol = '₹';

  @override
  void initState() {
    super.initState();
    context.read<ProcessingFeesBloc>().add(FetchProcessingFees());
    _loadCurrencySymbol();
  }

  Future<void> _loadCurrencySymbol() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _currencySymbol = prefs.getString(Constants.currency) ?? '₹';
    });
  }

  Future<void> _refreshFees() async {
    context.read<ProcessingFeesBloc>().add(FetchProcessingFees());
  }

  String _formatCurrency(double amount) {
    return '$_currencySymbol${amount.toStringAsFixed(2)}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Processing Fees'),
      ),
      backgroundColor: Colors.grey[200],
      body: BlocBuilder<ProcessingFeesBloc, ProcessingFeesState>(
        builder: (context, state) {
          if (state is ProcessingFeesLoading) {
            return const FeesSkeleton();
          } else if (state is ProcessingFeesError) {
            return _buildErrorWidget(state.message);
          } else if (state is ProcessingFeesLoaded) {
            return RefreshIndicator(
              onRefresh: _refreshFees,
              child: SingleChildScrollView(
                child: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Column(
                    children: [
                      _buildHeader(), // Added header
                      const SizedBox(height: 16),
                      _buildGrandTotalCard(state.grandTotal),
                      const SizedBox(height: 16),
                      _buildFeesList(state.fees, state.transportFees),
                    ],
                  ),
                ),
              ),
            );
          } else {
            return const Center(child: Text('No data available'));
          }
        },
      ),
    );
  }

  Widget _buildErrorWidget(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, color: Colors.red, size: 60),
          Padding(
            padding: const EdgeInsets.only(top: 16),
            child: Text('Error: $message'),
          ),
          ElevatedButton(
            onPressed: _refreshFees,
            child: const Text('Retry'),
          )
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Card(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(20.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Expanded(
                                      child: const Text(
                                        'Your Processing Fees Details is here!',
                                        style: TextStyle(
                                            fontSize: 22.0,
                                            fontWeight: FontWeight.bold,
                                            color: Colors.black),
                                        maxLines: 2, // Allow text to wrap to 2 lines
                                      ),
                                    ),
                                    SizedBox(
                                      height: 100,
                                      width: 100,
                                      child: Image.asset('assets/fees/feespage.jpeg'),
                                    ),
                                  ],
                                ),      ),
    );
  }

  Widget _buildGrandTotalCard(ProcessingFeeGrandTotal grandTotal) {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Grand Total',
              style: TextStyle(fontSize: 15.0, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            _buildSummaryRow('Total Paid', _formatCurrency(grandTotal.totalPaid)),
            _buildSummaryRow('Discount', _formatCurrency(grandTotal.feeDiscount)),
            _buildSummaryRow('Fine', _formatCurrency(grandTotal.feeFine)),
            _buildSummaryRow('Paid', _formatCurrency(grandTotal.feePaid)),
          ],
        ),
      ),
    );
  }

  Widget _buildSummaryRow(String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(fontSize: 12.0)),
          Text(value,
              style:
                  const TextStyle(fontSize: 12.0, fontWeight: FontWeight.bold)),
        ],
      ),
    );
  }

  Widget _buildFeesList(
      List<Fee> feeItems, List<TransportFee> transportFees) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: feeItems.length,
          itemBuilder: (context, index) {
            return _buildFeeListItem(feeItems[index]);
          },
        ),
        ListView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: transportFees.length,
          itemBuilder: (context, index) {
            return _buildTransportFeeListItem(transportFees[index]);
          },
        ),
      ],
    );
  }

  Widget _buildFeeListItem(Fee fee) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8.0),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    fee.name,
                    style: const TextStyle(
                        fontSize: 15.0, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8.0),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('Due Date'),
                    Text('Amount'),
                    Text('Status'),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(fee.dueDate,
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                    Text(_formatCurrency(fee.amount),
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                    Text(fee.status,
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTransportFeeListItem(TransportFee fee) {
    return Card(
      margin: const EdgeInsets.symmetric(vertical: 8.0),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(15.0),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    'Transport Fees (${fee.month})', // Hardcoded header with month
                    style: const TextStyle(
                        fontSize: 15.0, fontWeight: FontWeight.bold),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8.0),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: const [
                    Text('Due Date'),
                    Text('Amount'),
                    Text('Status'),
                  ],
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(fee.dueDate,
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                    Text(_formatCurrency(fee.fees),
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                    Text(fee.status,
                        style: const TextStyle(fontWeight: FontWeight.bold)),
                  ],
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
