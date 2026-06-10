
-- Add columns to order_table to support multi-vendor flow
-- We use a stored procedure or conditional logic to avoid errors if columns exist, 
-- but for simplicity in this environment, we'll just try to add them.
-- If you run this multiple times, it might error on duplicate column names, which is fine.

ALTER TABLE `order_table` ADD COLUMN `shop_id` INT(11) NOT NULL DEFAULT 0 AFTER `user_id`;
ALTER TABLE `order_table` ADD COLUMN `delivery_partner_id` INT(11) DEFAULT NULL AFTER `shop_id`;
ALTER TABLE `order_table` ADD COLUMN `order_status` VARCHAR(50) DEFAULT 'Pending' AFTER `total_amount`;

-- Ensure products have shop_id (already seems to be the case, but strictly speaking)
-- ALTER TABLE `product` ADD COLUMN `shop_id` INT(11) NOT NULL AFTER `pr_id`;

-- Add indexes for performance
ALTER TABLE `order_table` ADD INDEX `idx_shop_id` (`shop_id`);
ALTER TABLE `order_table` ADD INDEX `idx_delivery_partner_id` (`delivery_partner_id`);
ALTER TABLE `shops` ADD INDEX `idx_lat_long` (`latitude`, `longitude`);
