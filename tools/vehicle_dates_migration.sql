-- Vehicle module: add 12 date columns for validity/expiry tracking
-- Run on: mcekknagar (local) and production DB before deploying

ALTER TABLE `vehicles`
  ADD COLUMN `fc_validity_start`    DATE NULL AFTER `vehicle_photo`,
  ADD COLUMN `fc_validity_end`      DATE NULL AFTER `fc_validity_start`,
  ADD COLUMN `insurance_start`      DATE NULL AFTER `fc_validity_end`,
  ADD COLUMN `insurance_end`        DATE NULL AFTER `insurance_start`,
  ADD COLUMN `permit_expiry_start`  DATE NULL AFTER `insurance_end`,
  ADD COLUMN `permit_expiry_end`    DATE NULL AFTER `permit_expiry_start`,
  ADD COLUMN `road_tax_start`       DATE NULL AFTER `permit_expiry_end`,
  ADD COLUMN `road_tax_end`         DATE NULL AFTER `road_tax_start`,
  ADD COLUMN `pollution_cert_start` DATE NULL AFTER `road_tax_end`,
  ADD COLUMN `pollution_cert_end`   DATE NULL AFTER `pollution_cert_start`,
  ADD COLUMN `green_tax_start`      DATE NULL AFTER `pollution_cert_end`,
  ADD COLUMN `green_tax_end`        DATE NULL AFTER `green_tax_start`;
