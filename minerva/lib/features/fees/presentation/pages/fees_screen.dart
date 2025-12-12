import 'dart:convert';
import 'dart:developer';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:minerva_flutter/features/fees/domain/entities/fee.dart';
import 'package:minerva_flutter/features/fees/presentation/bloc/fees_bloc.dart';
import 'package:minerva_flutter/features/fees/presentation/widgets/fees_skeleton.dart';
import 'package:minerva_flutter/utils/constants.dart';
import 'package:shared_preferences/shared_preferences.dart';

class FeesScreen extends StatefulWidget {
  const FeesScreen({Key? key}) : super(key: key);

  @override
  _FeesScreenState createState() => _FeesScreenState();
}

class _FeesScreenState extends State<FeesScreen> {
  String _currencySymbol = '₹';

  @override
  void initState() {
    super.initState();
    context.read<FeesBloc>().add(FetchFees());
    _loadCurrencySymbol();
  }

  Future<void> _loadCurrencySymbol() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _currencySymbol = prefs.getString(Constants.currency) ?? '₹';
    });
  }

  Future<void> _refreshFees() async {
    context.read<FeesBloc>().add(FetchFees());
  }

    String _formatCurrency(double amount) {

      return '$_currencySymbol${amount.toStringAsFixed(2)}';

    }

  

    @override

    Widget build(BuildContext context) {

      return Scaffold(

        appBar: AppBar(

          title: const Text('Fees'),

        ),

        backgroundColor: Colors.grey[200],

        body: BlocBuilder<FeesBloc, FeesState>(

          builder: (context, state) {

            if (state is FeesLoading) {

              return const FeesSkeleton();

            } else if (state is FeesError) {

              return _buildErrorWidget(state.message);

            } else if (state is FeesLoaded) {

              return RefreshIndicator(

                onRefresh: _refreshFees,

                child: SingleChildScrollView(

                  child: Padding(

                    padding: const EdgeInsets.all(8.0),

                    child: Column(

                      children: [

                        _buildHeader(),

                        const SizedBox(height: 16),

                        _buildActionButtons(),

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

                                                      'Your Fees Details is here!',

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

  

    Widget _buildActionButtons() {

      return Row(

        mainAxisAlignment: MainAxisAlignment.spaceEvenly,

        children: [

          _buildActionButton('Fees', const Color(0xFFFA0000), () {}),

          _buildActionButton('Processing Fees', const Color(0xFFf39c12),

              () => context.push('/fees/processing')),

          _buildActionButton('Offline Payment', const Color(0xFF66AA18),

              () => context.push('/fees/offline')),

        ],

      );

    }

  

    Widget _buildActionButton(String text, Color color, VoidCallback onPressed) {

      return InkWell(

        onTap: onPressed,

        child: Container(

          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),

          decoration: BoxDecoration(

            color: color,

            borderRadius: BorderRadius.circular(20),

          ),

          child: Text(

            text,

            style: const TextStyle(

                color: Colors.white, fontWeight: FontWeight.bold),

          ),

        ),

      );

    }

  

    Widget _buildGrandTotalCard(FeeGrandTotal grandTotal) {

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

              _buildSummaryRow('Amount', _formatCurrency(grandTotal.amount)),

              _buildSummaryRow(

                  'Discount', _formatCurrency(grandTotal.amountDiscount)),

              _buildSummaryRow('Fine', _formatCurrency(grandTotal.amountFine)),

              _buildSummaryRow('Paid', _formatCurrency(grandTotal.amountPaid)),

              _buildSummaryRow(

                  'Balance', _formatCurrency(grandTotal.amountRemaining)),

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

  

    Widget _buildStatusBadge(String status) {
      Color color;
      String statusText;

      switch (status.toLowerCase()) {
        case 'paid':
          color = Colors.green;
          statusText = 'Paid';
          break;
        case 'partial':
          color = Colors.orange;
          statusText = 'Partial';
          break;
        case 'unpaid':
          color = Colors.red;
          statusText = 'Unpaid';
          break;
        default:
          color = Colors.grey;
          statusText = status;
      }

      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(4.0),
        ),
        child: Text(
          statusText,
          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
      );
    }

  void _showFeeDetailsBottomSheet(dynamic fee) {
    final String amountDetailString = fee.amountDetail;

    showModalBottomSheet(
      context: context,
      builder: (context) {
        if (amountDetailString == "0") {
          return const Center(
            child: Text('No details available.'),
          );
        }

        try {
          final Map<String, dynamic> amountDetails = json.decode(amountDetailString);
          return ListView.builder(
            itemCount: amountDetails.length,
            itemBuilder: (context, index) {
              final key = amountDetails.keys.elementAt(index);
              final detail = amountDetails[key];
              return Card(
                margin: const EdgeInsets.all(8.0),
                child: Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Payment ${index + 1}',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 8),
                      _buildDetailRow('Amount:', detail['amount'].toString()),
                      _buildDetailRow('Date:', detail['date']),
                      _buildDetailRow('Payment Mode:', detail['payment_mode']),
                      _buildDetailRow('Description:', detail['description']),
                      _buildDetailRow('Collected By:', detail['collected_by']),
                    ],
                  ),
                ),
              );
            },
          );
        } catch (e) {
          return Center(
            child: Text('Error parsing fee details: $e'),
          );
        }
      },
    );
  }

  Widget _buildDetailRow(String title, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title, style: const TextStyle(fontWeight: FontWeight.bold)),
          Flexible(
            child: Text(value, textAlign: TextAlign.end),
          ),
        ],
      ),
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
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  _buildStatusBadge(fee.status),
                  if (fee.status.toLowerCase() != 'unpaid')
                    TextButton(
                      onPressed: () => _showFeeDetailsBottomSheet(fee),
                      child: const Text('View'),
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
                      Text('Fine'),
                      Text('Discount'),
                      Text('Paid Amount'),
                      Text('Balance'),
                    ],
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(fee.dueDate,
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.amount),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountFine),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountDiscount),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountPaid),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountRemaining),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 8.0),
              Align(
                alignment: Alignment.bottomRight,
                child: ElevatedButton(
                  onPressed: () {},
                  child: const Text('Pay'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF66AA18),
                  ),
                ),
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
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  _buildStatusBadge(fee.status),
                  if (fee.status.toLowerCase() != 'unpaid')
                    TextButton(
                      onPressed: () => _showFeeDetailsBottomSheet(fee),
                      child: const Text('View'),
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
                      Text('Fine'),
                      Text('Discount'),
                      Text('Paid Amount'),
                      Text('Balance'),
                    ],
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(fee.dueDate,
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.fees),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountFine),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountDiscount),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountPaid),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                      Text(_formatCurrency(fee.totalAmountRemaining),
                          style: const TextStyle(fontWeight: FontWeight.bold)),
                    ],
                  ),
                ],
              ),
              const SizedBox(height: 8.0),
              Align(
                alignment: Alignment.bottomRight,
                child: ElevatedButton(
                  onPressed: () {},
                  child: const Text('Pay'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF66AA18),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }
  }