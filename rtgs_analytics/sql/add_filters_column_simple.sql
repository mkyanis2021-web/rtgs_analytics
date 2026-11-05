-- Simple migration: Add filters_json column
USE rtgs_analytics;

ALTER TABLE audit_sessions ADD COLUMN filters_json TEXT;

SELECT 'Migration completed!' AS status;
