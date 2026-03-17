import 'package:flutter/material.dart';
import '../models/staff_leave_request.dart';
import '../services/api/staff_workspace_api.dart';

class LeaveRequestProvider extends ChangeNotifier {
  List<StaffLeaveRequest> _leaveRequests = [];
  List<Map<String, dynamic>> _leaveTypes = [];
  bool _isLoading = false;
  bool _isLoadingLeaveTypes = false;
  String? _error;
  int? _lastLoadedStaffId;

  List<StaffLeaveRequest> get leaveRequests => _leaveRequests;
  List<Map<String, dynamic>> get leaveTypes => _leaveTypes;
  bool get isLoading => _isLoading;
  bool get isLoadingLeaveTypes => _isLoadingLeaveTypes;
  String? get error => _error;

  List<StaffLeaveRequest> get pendingRequests => _leaveRequests
      .where((req) => req.status.toLowerCase() == 'pending')
      .toList();

  List<StaffLeaveRequest> get approvedRequests => _leaveRequests
      .where(
        (req) =>
            req.status.toLowerCase() == 'approved' ||
            req.status.toLowerCase() == 'approve',
      )
      .toList();

  List<StaffLeaveRequest> get rejectedRequests => _leaveRequests
      .where(
        (req) =>
            req.status.toLowerCase() == 'disapproved' ||
            req.status.toLowerCase() == 'disapprove' ||
            req.status.toLowerCase() == 'rejected',
      )
      .toList();

  Future<void> loadLeaveRequests(int staffId) async {
    if (_lastLoadedStaffId == staffId && !_isLoading) {
      return; // Already loaded
    }

    _isLoading = true;
    _error = null;
    _lastLoadedStaffId = staffId;
    notifyListeners();

    try {
      final result = await StaffWorkspaceApi.getStaffLeaveRequests(
        staffId: staffId,
      );

      if (result['status'] == 1) {
        final leaveRequests =
            (result['leave_requests'] as List?)
                ?.map(
                  (item) =>
                      StaffLeaveRequest.fromJson(item as Map<String, dynamic>),
                )
                .toList() ??
            [];

        // Sort by date descending (newest first)
        leaveRequests.sort((a, b) => b.date.compareTo(a.date));

        _leaveRequests = leaveRequests;
        _error = null;
      } else {
        _error = result['message'] ?? 'Failed to load leave requests';
        _leaveRequests = [];
      }
    } catch (error) {
      _error = error.toString();
      _leaveRequests = [];
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> addLeaveRequest({
    required int leaveTypeId,
    required DateTime leaveFrom,
    required DateTime leaveTo,
    required String reason,
    bool isHalfDay = false,
  }) async {
    try {
      final result = await StaffWorkspaceApi.addStaffLeaveRequest(
        leaveTypeId: leaveTypeId,
        leaveFrom: leaveFrom,
        leaveTo: leaveTo,
        reason: reason,
        isHalfDay: isHalfDay,
      );

      if (result['status'] == 1) {
        final newRequest = StaffLeaveRequest.fromJson(
          result['leave_request'] as Map<String, dynamic>,
        );
        _leaveRequests.insert(0, newRequest);
        notifyListeners();
        return true;
      }
      _error = result['message'] ?? 'Failed to create leave request';
      return false;
    } catch (error) {
      _error = error.toString();
      return false;
    }
  }

  Future<bool> updateLeaveRequest({
    required int leaveId,
    required int leaveTypeId,
    required DateTime leaveFrom,
    required DateTime leaveTo,
    required String reason,
    bool isHalfDay = false,
  }) async {
    try {
      final result = await StaffWorkspaceApi.updateStaffLeaveRequest(
        leaveId: leaveId,
        leaveTypeId: leaveTypeId,
        leaveFrom: leaveFrom,
        leaveTo: leaveTo,
        reason: reason,
        isHalfDay: isHalfDay,
      );

      if (result['status'] == 1) {
        final updatedRequest = StaffLeaveRequest.fromJson(
          result['leave_request'] as Map<String, dynamic>,
        );
        final index = _leaveRequests.indexWhere((req) => req.id == leaveId);
        if (index >= 0) {
          _leaveRequests[index] = updatedRequest;
        }
        notifyListeners();
        return true;
      }
      _error = result['message'] ?? 'Failed to update leave request';
      return false;
    } catch (error) {
      _error = error.toString();
      return false;
    }
  }

  Future<bool> deleteLeaveRequest(int leaveId) async {
    try {
      final result = await StaffWorkspaceApi.deleteStaffLeaveRequest(
        leaveId: leaveId,
      );

      if (result['status'] == 1) {
        _leaveRequests.removeWhere((req) => req.id == leaveId);
        notifyListeners();
        return true;
      }
      _error = result['message'] ?? 'Failed to delete leave request';
      return false;
    } catch (error) {
      _error = error.toString();
      return false;
    }
  }

  Future<void> loadLeaveTypes(int staffId) async {
    if (_isLoadingLeaveTypes) {
      return;
    }

    _isLoadingLeaveTypes = true;
    _error = null;
    notifyListeners();

    try {
      // The backend resolves staff from the User-ID auth header automatically,
      // so pass whatever staffId we have (even 0) and let the API handle it.
      final result = await StaffWorkspaceApi.getStaffLeaveBalance(
        staffId: staffId,
      );

      if (result['status'] == 1) {
        final balance = (result['leave_balance'] as List?) ?? [];
        _leaveTypes = balance
            .whereType<Map>()
            .map((item) => item.map((key, value) => MapEntry('$key', value)))
            .where((item) {
              final requiresBalanceCheckRaw =
                  (item['requires_balance_check'] ?? 1).toString().trim();
              final requiresBalanceCheck =
                  requiresBalanceCheckRaw == '1' ||
                  requiresBalanceCheckRaw.toLowerCase() == 'yes' ||
                  requiresBalanceCheckRaw.toLowerCase() == 'true';
              final remaining =
                  double.tryParse((item['remaining'] ?? 0).toString()) ?? 0;
              if (requiresBalanceCheck) {
                return remaining > 0;
              }
              return true;
            })
            .toList();

        if (_leaveTypes.isEmpty) {
          _error = 'No applicable leave types are available for your profile.';
        }
      } else {
        _error = result['message'] ?? 'Failed to load leave types';
        _leaveTypes = [];
      }
    } catch (error) {
      _error = error.toString();
      _leaveTypes = [];
    } finally {
      _isLoadingLeaveTypes = false;
      notifyListeners();
    }
  }
}
