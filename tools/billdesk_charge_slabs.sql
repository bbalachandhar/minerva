-- BillDesk charge slabs table
-- Run this on mcekknagar (and any other instance that uses BillDesk as payment gateway).
-- The admin can configure the actual contracted rates via Admin > Payment Settings > BillDesk.

CREATE TABLE IF NOT EXISTS `billdesk_charge_slabs` (
  `id`                  int(11)        NOT NULL AUTO_INCREMENT,
  `payment_method`      varchar(50)    NOT NULL COMMENT 'Machine key: debit_visa_mc, debit_rupay, credit_card, net_banking, upi, wallet',
  `label`               varchar(100)   NOT NULL COMMENT 'Display label shown to student',
  `charge_type`         enum('percentage','flat') NOT NULL DEFAULT 'percentage',
  `charge_value`        decimal(10,4)  NOT NULL DEFAULT 0.0000 COMMENT 'Rate at or below amount_threshold',
  `amount_threshold`    decimal(10,2)  NOT NULL DEFAULT 0.00   COMMENT '0 = no two-slab; >0 = threshold in rupees',
  `charge_value_above`  decimal(10,4)  NOT NULL DEFAULT 0.0000 COMMENT 'Rate above amount_threshold (0 when no two-slab)',
  `is_active`           tinyint(1)     NOT NULL DEFAULT 1,
  `sort_order`          int(11)        NOT NULL DEFAULT 0,
  `created_at`          datetime       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          datetime       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_payment_method` (`payment_method`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default slab rows (rates are set to 0; configure actual contracted rates via Admin > Payment Settings).
INSERT IGNORE INTO `billdesk_charge_slabs`
  (`id`, `payment_method`, `label`, `charge_type`, `charge_value`, `amount_threshold`, `charge_value_above`, `is_active`, `sort_order`)
VALUES
  (1, 'debit_visa_mc',  'Debit Card (Visa/MC)',    'percentage', 0.0000, 2000.00, 0.0000, 1, 1),
  (2, 'debit_rupay',    'Debit Card (RuPay)',       'percentage', 0.0000,    0.00, 0.0000, 1, 2),
  (3, 'credit_card',    'Credit Card',              'percentage', 0.0000,    0.00, 0.0000, 1, 3),
  (4, 'net_banking',    'Net Banking',              'flat',       0.0000,    0.00, 0.0000, 1, 4),
  (5, 'upi',            'UPI',                      'percentage', 0.0000,    0.00, 0.0000, 1, 5),
  (6, 'wallet',         'Wallet',                   'percentage', 0.0000,    0.00, 0.0000, 1, 6);
