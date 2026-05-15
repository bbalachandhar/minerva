-- Performance indexes added for MCC overview AJAX widgets (2025-05-15)
-- book_issues.is_returned  → used by getAcademicsSummary: WHERE is_returned=0
-- item_stock.is_active      → used by getInventorySummary: WHERE ist.is_active='yes'
-- item_category.is_active   → used by getInventorySummary: WHERE ic.is_active='yes'

USE mcekknagar;
DROP PROCEDURE IF EXISTS add_index_if_missing;
DELIMITER //
CREATE PROCEDURE add_index_if_missing(
    IN p_db   VARCHAR(64),
    IN p_tbl  VARCHAR(64),
    IN p_idx  VARCHAR(64),
    IN p_col  VARCHAR(64)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = p_db
          AND TABLE_NAME   = p_tbl
          AND INDEX_NAME   = p_idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', p_db, '`.`', p_tbl, '` ADD INDEX `', p_idx, '` (`', p_col, '`)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END //
DELIMITER ;

-- book_issues.is_returned
CALL add_index_if_missing('mcekknagar',  'book_issues', 'idx_is_returned', 'is_returned');
CALL add_index_if_missing('amace',       'book_issues', 'idx_is_returned', 'is_returned');
CALL add_index_if_missing('amacedu',     'book_issues', 'idx_is_returned', 'is_returned');
CALL add_index_if_missing('maasc',       'book_issues', 'idx_is_returned', 'is_returned');
CALL add_index_if_missing('maptc',       'book_issues', 'idx_is_returned', 'is_returned');
CALL add_index_if_missing('minervademo', 'book_issues', 'idx_is_returned', 'is_returned');

-- item_stock.is_active
CALL add_index_if_missing('mcekknagar',  'item_stock', 'idx_is_active', 'is_active');
CALL add_index_if_missing('amace',       'item_stock', 'idx_is_active', 'is_active');
CALL add_index_if_missing('amacedu',     'item_stock', 'idx_is_active', 'is_active');
CALL add_index_if_missing('maasc',       'item_stock', 'idx_is_active', 'is_active');
CALL add_index_if_missing('maptc',       'item_stock', 'idx_is_active', 'is_active');
CALL add_index_if_missing('minervademo', 'item_stock', 'idx_is_active', 'is_active');

-- item_category.is_active
CALL add_index_if_missing('mcekknagar',  'item_category', 'idx_is_active', 'is_active');
CALL add_index_if_missing('amace',       'item_category', 'idx_is_active', 'is_active');
CALL add_index_if_missing('amacedu',     'item_category', 'idx_is_active', 'is_active');
CALL add_index_if_missing('maasc',       'item_category', 'idx_is_active', 'is_active');
CALL add_index_if_missing('maptc',       'item_category', 'idx_is_active', 'is_active');
CALL add_index_if_missing('minervademo', 'item_category', 'idx_is_active', 'is_active');

DROP PROCEDURE IF EXISTS add_index_if_missing;
