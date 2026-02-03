ALTER TABLE `sch_settings`
    ADD COLUMN `adm_include_current_year` TINYINT(1) NOT NULL DEFAULT 0 AFTER `adm_auto_insert`,
    ADD COLUMN `staffid_include_current_year` TINYINT(1) NOT NULL DEFAULT 0 AFTER `staffid_auto_insert`;
