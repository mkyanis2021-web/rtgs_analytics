-- Allow NULL session_id for pipeline data
-- This removes the foreign key constraint temporarily to allow orphaned records

ALTER TABLE `sprtgs_transactions` 
DROP FOREIGN KEY `sprtgs_transactions_ibfk_1`;

ALTER TABLE `rtgs_message_log`
DROP FOREIGN KEY IF EXISTS `rtgs_message_log_ibfk_1`;

-- Recreate constraints but allow NULL
-- Note: Foreign keys in MySQL allow NULL by default when the column allows NULL
-- We just need to ensure the column definition allows NULL (which it already does)

-- Re-add foreign keys (they will allow NULL values)
ALTER TABLE `sprtgs_transactions`
ADD CONSTRAINT `sprtgs_transactions_ibfk_1` 
FOREIGN KEY (`session_id`) REFERENCES `audit_sessions` (`id`) 
ON DELETE CASCADE;

ALTER TABLE `rtgs_message_log`
ADD CONSTRAINT `rtgs_message_log_ibfk_1`
FOREIGN KEY (`session_id`) REFERENCES `audit_sessions` (`id`)
ON DELETE CASCADE;
