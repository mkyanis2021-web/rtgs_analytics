-- Migration: Add filters_json column to audit_sessions
-- Date: 2026-02-08
-- Purpose: Store filter parameters for saved reports (Bug #2 fix)

USE rtgs_analytics;

-- Check if column exists and add it if it doesn't
SET @dbname = DATABASE();
SET @tablename = 'audit_sessions';
SET @columnname = 'filters_json';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE 
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  'SELECT ''Column already exists'' AS message;',
  'ALTER TABLE audit_sessions ADD COLUMN filters_json TEXT;'
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Verify the change
DESCRIBE audit_sessions;
