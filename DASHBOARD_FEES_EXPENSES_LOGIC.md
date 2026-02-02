-- ============================================================================
-- Fees Collection & Expenses Dashboard Logic Analysis
-- Session: 2025-26 (Academic Year)
-- Start Month: July (Month 7), End Month: June (Month 6)
-- ============================================================================

-- ============================================================================
-- 1. DASHBOARD LOGIC EXPLANATION
-- ============================================================================

/* 
Current Session: 2025-26
Start Month: July 2025 (Month 7)
End Month: June 2026 (Month 6)

The dashboard displays:
- Yearly Collection & Expenses for Session 2025-26 (July 2025 - June 2026)
- Current Month Collection & Expenses (Today's Month)
- Monthly breakdown chart with both collection and expense data

Session Calculation in Admin.php dashboard() method:
-----------------------------------------------------
Line 127-135: Parse Session Name
  - Current session: "2025-26"
  - $a = "2025", $b = "26"
  - $Current_year = "2025"
  - $Next_year = "2026" (converts "26" to "2026")

Line 134: Get Start & End Months
  - startmonthandend() returns (7, 6) - July to June
  - $ar[0] = 7 (start month)
  - $ar[1] = 6 (end month)

Line 135-136: Calculate Year Boundaries
  - $year_str_month = "2025-07-01" (Session Start: July 1, 2025)
  - $year_end_month = "2026-06-30" (Session End: June 30, 2026)

Line 139: Get Total Session Fee Collection
  - Method: getDepositAmountBetweenDate("2025-07-01", "2026-06-30")
  - Returns array of all fee deposits within session date range
  - Includes tuition fees, other fees, hostel fees, transport fees

Line 140: Get Transport Fee Collection
  - Method: getTransportDepositAmountBetweenDate("2025-07-01", "2026-06-30")
  - Returns array of all transport fee deposits within session
  - Will be combined with regular fees

Monthly Breakdown (Line 155-185):
---------------------------------
For each month in session (July 2025 to June 2026):
  1. Get month start and end dates
  2. Calculate collection for that month:
     - Call whatever() to sum deposits between month boundaries
     - Add transport fees for that month
     - Format with currency
  3. Store in $s array for charting
  
Result: $yearly_collection array with 12 values (one per month)
Example: ['0.00', '500.00', '1250.00', '0.00', '2000.00', ...]

Expense Breakdown (Line 186-206):
---------------------------------
For each month in session (July 2025 to June 2026):
  1. Get month start and end dates
  2. Calculate expenses for that month:
     - Call getTotalExpenseBwdate() to sum all expenses
     - Format with currency
  3. Store in $ex array for charting
  
Note: If no expenses, array value remains undefined (gap in chart)

Current Month Breakdown (Line 211-238):
---------------------------------------
For each day in current month (January 2026):
  1. Get collection for that day
  2. Get expenses for that day
  3. Combine and format with currency
  4. Store in $days_collection and $days_expense arrays

These arrays power the "Current Month" line chart.
*/

-- ============================================================================
-- 2. POTENTIAL LOGIC ISSUES TO INVESTIGATE
-- ============================================================================

/*
Issue 1: Transport Fee Double Counting?
-----------
Current Logic:
  Line 139: $getDepositeAmount = studentfeemaster_model->getDepositAmountBetweenDate()
  Line 140: $student_transport_fee = studenttransportfee_model->getTransportDepositAmountBetweenDate()
  
  Line 180: $return = whatever($getDepositeAmount, $month_start, $month_end)
  Line 181: $tranport_amt = whatever($student_transport_fee, $month_start, $month_end)
  Line 183: $s[] = convertBaseAmountCurrencyFormat($return + $tranport_amt)

Question: Does getDepositAmountBetweenDate() already include transport fees?
If YES: Adding $tranport_amt will double-count
If NO: Logic is correct

Recommendation: Check studentfeemaster_model->getDepositAmountBetweenDate()
and studenttransportfee_model->getTransportDepositAmountBetweenDate() to confirm
they don't overlap.

---

Issue 2: Missing Data in Expense Array
-----------
Current Logic (Line 197-206):
  if (!empty($expense_monthly)) {
      $amt  = 0;
      $ex[] = $amt + convertBaseAmountCurrencyFormat($expense_monthly->amount);
  }
  // No else statement, so if no expenses, no value added to array

Problem: If a month has no expenses:
  - No value is added to $ex array
  - Array has gaps/missing months
  - Chart labels don't align with data points

Fix: Should add 0.00 when no expenses:
  if (!empty($expense_monthly)) {
      $ex[] = convertBaseAmountCurrencyFormat($expense_monthly->amount);
  } else {
      $ex[] = "0.00";
  }

---

Issue 3: Redundant Variable Assignment
-----------
Line 181: $tranport_amt = $this->whatever(...)
Line 181 (Current Month): $tranport_amt = ...

The variable is reused in two different loops without proper scope separation.

---

Issue 4: Collection Data Source Not Filtered by Session
-----------
Current Logic:
  - Uses getDepositAmountBetweenDate() which likely uses transaction dates
  - Does NOT filter by session_id or academic session
  
Problem: If student has fees from multiple sessions, all will be included
if they fall within the July 2025 - June 2026 date range.

Should verify: Does the model filter by current session_id?
*/

-- ============================================================================
-- 3. DATABASE QUERIES TO VERIFY LOGIC
-- ============================================================================

-- Check if studentfeemaster includes transport in deposits
-- SELECT DISTINCT fee_type FROM studentfeemaster WHERE fee_type LIKE '%transport%';

-- Count records within session date range
-- SELECT COUNT(*) FROM studentfee 
-- WHERE payment_date BETWEEN '2025-07-01' AND '2026-06-30'
-- AND is_active = 'yes';

-- Check for duplicate transport fees
-- SELECT payment_date, SUM(amount) as total 
-- FROM studentfeemaster 
-- WHERE payment_date BETWEEN '2025-07-01' AND '2026-06-30'
-- GROUP BY DATE(payment_date)
-- ORDER BY payment_date;

-- Compare with transport fees
-- SELECT payment_date, SUM(amount) as total 
-- FROM studenttransportfee 
-- WHERE payment_date BETWEEN '2025-07-01' AND '2026-06-30'
-- GROUP BY DATE(payment_date)
-- ORDER BY payment_date;

-- ============================================================================
-- 4. RECOMMENDATIONS
-- ============================================================================

/*
1. FIX: Add missing "0.00" values to expense array when no expenses exist
   Location: Admin.php, lines 197-206
   
2. VERIFY: Check if transport fees are included in getDepositAmountBetweenDate()
   Location: Studentfeemaster_model.php, getDepositAmountBetweenDate()
   
3. VERIFY: Confirm session filtering is applied to collection data
   Location: Studentfeemaster_model.php, Studenttransportfee_model.php
   
4. REFACTOR: Consider using single unified query instead of two separate fee sources
   Combines studentfeemaster and studenttransportfee in one result set
   
5. TEST: Generate reports for 2025-26 and verify:
   - Collection matches the Fee Collection Report
   - Expenses match the Expense Report
   - No obvious gaps in monthly charts
   - No duplicate amounts when comparing by date
*/

-- ============================================================================
-- 5. CURRENT SESSION CONFIGURATION
-- ============================================================================

SELECT 
    'Active Session' as metric,
    '2025-26' as value
UNION ALL
SELECT 'Start Month', 'July (7)'
UNION ALL
SELECT 'End Month', 'June (6)'
UNION ALL
SELECT 'Session Start', '2025-07-01'
UNION ALL
SELECT 'Session End', '2026-06-30'
UNION ALL
SELECT 'Dashboard Mode', 'Yearly Collection & Expenses for 2025-26'
UNION ALL
SELECT 'Data Range', 'July 1, 2025 to June 30, 2026'
UNION ALL
SELECT 'Chart Months', '12 (July through June)'
UNION ALL
SELECT 'Current Month Chart', 'January 1 to January 31, 2026 (by day)';

-- ============================================================================
