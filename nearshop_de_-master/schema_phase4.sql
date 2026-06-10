-- Phase 4.1: Add shop_id to product table to link products to shops
ALTER TABLE `product` ADD COLUMN `shop_id` INT(11) DEFAULT NULL;
ALTER TABLE `product` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Index for performance
CREATE INDEX `idx_product_shop` ON `product`(`shop_id`);
