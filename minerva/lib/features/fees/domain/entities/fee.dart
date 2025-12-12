import 'package:equatable/equatable.dart';

class FeeGrandTotal extends Equatable {
  final double amount;
  final double feeFine;
  final double amountDiscount;
  final double amountFine;
  final double amountPaid;
  final double amountRemaining;

  const FeeGrandTotal({
    required this.amount,
    required this.feeFine,
    required this.amountDiscount,
    required this.amountFine,
    required this.amountPaid,
    required this.amountRemaining,
  });

  factory FeeGrandTotal.fromJson(Map<String, dynamic> json) {
    return FeeGrandTotal(
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      feeFine: double.tryParse(json['fee_fine'].toString()) ?? 0.0,
      amountDiscount: double.tryParse(json['amount_discount'].toString()) ?? 0.0,
      amountFine: double.tryParse(json['amount_fine'].toString()) ?? 0.0,
      amountPaid: double.tryParse(json['amount_paid'].toString()) ?? 0.0,
      amountRemaining: double.tryParse(json['amount_remaining'].toString()) ?? 0.0,
    );
  }

  @override
  List<Object?> get props => [
        amount,
        feeFine,
        amountDiscount,
        amountFine,
        amountPaid,
        amountRemaining,
      ];
}

class ProcessingFeeGrandTotal extends Equatable {
  final double totalPaid;
  final double feeDiscount;
  final double feeFine;
  final double feePaid;

  const ProcessingFeeGrandTotal({
    required this.totalPaid,
    required this.feeDiscount,
    required this.feeFine,
    required this.feePaid,
  });

  factory ProcessingFeeGrandTotal.fromJson(Map<String, dynamic> json) {
    return ProcessingFeeGrandTotal(
      totalPaid: double.tryParse(json['total_paid'].toString()) ?? 0.0,
      feeDiscount: double.tryParse(json['fee_discount'].toString()) ?? 0.0,
      feeFine: double.tryParse(json['fee_fine'].toString()) ?? 0.0,
      feePaid: double.tryParse(json['fee_paid'].toString()) ?? 0.0,
    );
  }

  @override
  List<Object?> get props => [
        totalPaid,
        feeDiscount,
        feeFine,
        feePaid,
      ];
}

class Fee extends Equatable {
  final String id;
  final String name;
  final String type;
  final String code;
  final String dueDate;
  final double amount;
  final double feesFineAmount;
  final double totalAmountPaid;
  final double totalAmountDiscount;
  final double totalAmountFine;
  final double totalAmountRemaining;
  final String studentFeesDepositeId;
  final String studentSessionId;
  final String feeSessionGroupId;
  final String feeGroupsFeetypeId;
  final String status;
  final dynamic amountDetail;

  const Fee({
    required this.id,
    required this.name,
    required this.type,
    required this.code,
    required this.dueDate,
    required this.amount,
    required this.feesFineAmount,
    required this.totalAmountPaid,
    required this.totalAmountDiscount,
    required this.totalAmountFine,
    required this.totalAmountRemaining,
    required this.studentFeesDepositeId,
    required this.studentSessionId,
    required this.feeSessionGroupId,
    required this.feeGroupsFeetypeId,
    required this.status,
    required this.amountDetail,
  });

  factory Fee.fromJson(Map<String, dynamic> json) {
    return Fee(
      id: json['id'].toString(),
      name: json['name'],
      type: json['type'],
      code: json['code'],
      dueDate: json['due_date'],
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      feesFineAmount: double.tryParse(json['fees_fine_amount'].toString()) ?? 0.0,
      totalAmountPaid: double.tryParse(json['total_amount_paid'].toString()) ?? 0.0,
      totalAmountDiscount: double.tryParse(json['total_amount_discount'].toString()) ?? 0.0,
      totalAmountFine: double.tryParse(json['total_amount_fine'].toString()) ?? 0.0,
      totalAmountRemaining: double.tryParse(json['total_amount_remaining'].toString()) ?? 0.0,
      studentFeesDepositeId: json['student_fees_deposite_id'].toString(),
      studentSessionId: json['student_session_id'].toString(),
      feeSessionGroupId: json['fee_session_group_id'].toString(),
      feeGroupsFeetypeId: json['fee_groups_feetype_id'].toString(),
      status: json['status'],
      amountDetail: json['amount_detail'],
    );
  }

  @override
  List<Object?> get props => [
        id,
        name,
        type,
        code,
        dueDate,
        amount,
        feesFineAmount,
        totalAmountPaid,
        totalAmountDiscount,
        totalAmountFine,
        totalAmountRemaining,
        studentFeesDepositeId,
        studentSessionId,
        feeSessionGroupId,
        feeGroupsFeetypeId,
        status,
        amountDetail,
      ];
}

class TransportFee extends Equatable {
  final String id;
  final String month;
  final String studentSessionId;
  final String dueDate;
  final double fees;
  final double feesFineAmount;
  final double totalAmountPaid;
  final double totalAmountDiscount;
  final double totalAmountFine;
  final double totalAmountRemaining;
  final String studentFeesDepositeId;
  final String status;
  final dynamic amountDetail;

  const TransportFee({
    required this.id,
    required this.month,
    required this.studentSessionId,
    required this.dueDate,
    required this.fees,
    required this.feesFineAmount,
    required this.totalAmountPaid,
    required this.totalAmountDiscount,
    required this.totalAmountFine,
    required this.totalAmountRemaining,
    required this.studentFeesDepositeId,
    required this.status,
    required this.amountDetail,
  });

  factory TransportFee.fromJson(Map<String, dynamic> json) {
    return TransportFee(
      id: json['id'].toString(),
      month: json['month'],
      studentSessionId: json['student_session_id'].toString(),
      dueDate: json['due_date'],
      fees: double.tryParse(json['fees'].toString()) ?? 0.0,
      feesFineAmount: double.tryParse(json['fees_fine_amount'].toString()) ?? 0.0,
      totalAmountPaid: double.tryParse(json['total_amount_paid'].toString()) ?? 0.0,
      totalAmountDiscount: double.tryParse(json['total_amount_discount'].toString()) ?? 0.0,
      totalAmountFine: double.tryParse(json['total_amount_fine'].toString()) ?? 0.0,
      totalAmountRemaining: double.tryParse(json['total_amount_remaining'].toString()) ?? 0.0,
      studentFeesDepositeId: json['student_fees_deposite_id'].toString(),
      status: json['status'],
      amountDetail: json['amount_detail'],
    );
  }

  @override
  List<Object?> get props => [
        id,
        month,
        studentSessionId,
        dueDate,
        fees,
        feesFineAmount,
        totalAmountPaid,
        totalAmountDiscount,
        totalAmountFine,
        totalAmountRemaining,
        studentFeesDepositeId,
        status,
        amountDetail,
      ];
}

class OfflinePayment extends Equatable {
  final String id;
  final String submitDate;
  final String paymentDate;
  final String approveDate;
  final double amount;
  final String invoiceId;
  final String reference;
  final String bankFrom;
  final String month;
  final String isActive;
  final String reply;
  final String attachment;
  final String code;
  final String transportFeesMonth;
  final String bankAccountTransferred;
  final String feeGroupName;
  final String routeTitle;
  final String pickupPoint;
  final String type;

  const OfflinePayment({
    required this.id,
    required this.submitDate,
    required this.paymentDate,
    required this.approveDate,
    required this.amount,
    required this.invoiceId,
    required this.reference,
    required this.bankFrom,
    required this.month,
    required this.isActive,
    required this.reply,
    required this.attachment,
    required this.code,
    required this.transportFeesMonth,
    required this.bankAccountTransferred,
    required this.feeGroupName,
    required this.routeTitle,
    required this.pickupPoint,
    required this.type,
  });

  factory OfflinePayment.fromJson(Map<String, dynamic> json) {
    return OfflinePayment(
      id: json['id'].toString(),
      submitDate: json['submit_date'] ?? '',
      paymentDate: json['payment_date'] ?? '',
      approveDate: json['approve_date'] ?? '',
      amount: double.tryParse(json['amount'].toString()) ?? 0.0,
      invoiceId: json['invoice_id'] ?? '',
      reference: json['reference'] ?? '',
      bankFrom: json['bank_from'] ?? '',
      month: json['month'] ?? '',
      isActive: json['is_active'] ?? '',
      reply: json['reply'] ?? '',
      attachment: json['attachment'] ?? '',
      code: json['code'] ?? '',
      transportFeesMonth: json['transport_fees_month'] ?? '',
      bankAccountTransferred: json['bank_account_transferred'] ?? '',
      feeGroupName: json['fee_group_name'] ?? '',
      routeTitle: json['route_title'] ?? '',
      pickupPoint: json['pickup_point'] ?? '',
      type: json['type'] ?? '',
    );
  }

  @override
  List<Object?> get props => [
        id,
        submitDate,
        paymentDate,
        approveDate,
        amount,
        invoiceId,
        reference,
        bankFrom,
        month,
        isActive,
        reply,
        attachment,
        code,
        transportFeesMonth,
        bankAccountTransferred,
        feeGroupName,
        routeTitle,
        pickupPoint,
        type,
      ];
}
