import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';
import 'package:minerva_flutter/features/fees/presentation/bloc/offline_payment_bloc.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class OfflinePaymentScreen extends StatefulWidget {
  const OfflinePaymentScreen({Key? key}) : super(key: key);

  @override
  _OfflinePaymentScreenState createState() => _OfflinePaymentScreenState();
}

class _OfflinePaymentScreenState extends State<OfflinePaymentScreen> {
  String _currencySymbol = '₹';

  @override
  void initState() {
    super.initState();
    context.read<OfflinePaymentBloc>().add(FetchOfflinePayments());
    _loadCurrencySymbol();
  }

  Future<void> _loadCurrencySymbol() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _currencySymbol = prefs.getString(Constants.currency) ?? '₹';
    });
  }

  Future<void> _refreshPayments() async {
    context.read<OfflinePaymentBloc>().add(FetchOfflinePayments());
  }

  String _formatCurrency(double amount) {
    return '$_currencySymbol${amount.toStringAsFixed(2)}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Offline Payments'),
      ),
      backgroundColor: Colors.grey[200],
      body: BlocBuilder<OfflinePaymentBloc, OfflinePaymentState>(
        builder: (context, state) {
          if (state is OfflinePaymentLoading) {
            return const Center(child: CircularProgressIndicator());
          } else if (state is OfflinePaymentError) {
            return _buildErrorWidget(state.message);
          } else if (state is OfflinePaymentLoaded) {
            return RefreshIndicator(
              onRefresh: _refreshPayments,
              child: SingleChildScrollView(
                child: Padding(
                  padding: const EdgeInsets.all(8.0),
                  child: Column(
                    children: [
                      _buildHeader(), // Added header
                      const SizedBox(height: 16),
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: state.payments.length,
                        itemBuilder: (context, index) {
                          return _buildPaymentListItem(state.payments[index]);
                        },
                      ),
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
            onPressed: _refreshPayments,
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
                'Your Offline Payments Details is here!',
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
        ),
      ),
    );
  }

  Widget _buildPaymentListItem(OfflinePayment payment) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Invoice ID: ${payment.invoiceId}',
              style: const TextStyle(fontSize: 16.0, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 8.0),
            _buildInfoRow('Amount:', _formatCurrency(payment.amount)),
            _buildInfoRow('Payment Date:', payment.paymentDate),
            _buildInfoRow('Submit Date:', payment.submitDate),
            _buildInfoRow('Approve Date:', payment.approveDate),
            _buildInfoRow('Status:', payment.isActive),
            _buildInfoRow('Payment Method:', payment.bankFrom),
            _buildInfoRow('Reference:', payment.reference),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
          Text(value),
        ],
      ),
    );
  }
}
