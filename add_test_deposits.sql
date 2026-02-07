-- Add test fee deposits for February 2026
USE mcekknagar;

INSERT INTO student_fees_deposite (student_fees_master_id, fee_groups_feetype_id, amount_detail, is_active, created_at) VALUES 
(1, 1, '{"1":{"amount":"5000.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-01","description":"February Tuition","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-01 10:00:00'),
(1, 1, '{"1":{"amount":"4500.00","amount_discount":"0.00","amount_fine":"100.00","date":"2026-02-03","description":"February Fee","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-03 11:00:00'),
(1, 1, '{"1":{"amount":"3000.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-05","description":"Lab Fee","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-05 09:00:00'),
(1, 1, '{"1":{"amount":"5500.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-07","description":"Exam Fee","collected_by":"Admin","payment_mode":"Online","received_by":"1","inv_no":1}}', 'yes', '2026-02-07 14:00:00'),
(1, 1, '{"1":{"amount":"2000.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-10","description":"Library Fee","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-10 10:00:00'),
(1, 1, '{"1":{"amount":"4800.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-12","description":"Sports Fee","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-12 15:00:00'),
(1, 1, '{"1":{"amount":"3500.00","amount_discount":"0.00","amount_fine":"50.00","date":"2026-02-15","description":"Development Fee","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-15 11:00:00'),
(1, 1, '{"1":{"amount":"5200.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-18","description":"Monthly Fee","collected_by":"Admin","payment_mode":"Online","received_by":"1","inv_no":1}}', 'yes', '2026-02-18 09:30:00'),
(1, 1, '{"1":{"amount":"2500.00","amount_discount":"0.00","amount_fine":"0.00","date":"2026-02-20","description":"Activity Fee","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-20 13:00:00'),
(1, 1, '{"1":{"amount":"4700.00","amount_discount":"300.00","amount_fine":"0.00","date":"2026-02-25","description":"Tuition with Discount","collected_by":"Admin","payment_mode":"Cash","received_by":"1","inv_no":1}}', 'yes', '2026-02-25 16:00:00');

SELECT 'Test deposits added successfully!' as Status;
SELECT COUNT(*) as 'Total February 2026 Deposits' FROM student_fees_deposite WHERE JSON_UNQUOTE(JSON_EXTRACT(amount_detail, '$.1.date')) >= '2026-02-01' AND JSON_UNQUOTE(JSON_EXTRACT(amount_detail, '$.1.date')) < '2026-03-01';
