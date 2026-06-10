-- Phase 3.1: Add Location and Extra Images to Shops

ALTER TABLE `shops` ADD COLUMN `latitude` DECIMAL(10, 8) DEFAULT NULL;
ALTER TABLE `shops` ADD COLUMN `longitude` DECIMAL(11, 8) DEFAULT NULL;

-- We already have shop_image, but we need specific Exterior/Interior separation if desired. 
-- For now we can assume `shop_image` is the main one, and we add extra columns or use the `shop_documents` table for extra images.
-- Let's add specific columns for clarity as requested "outside inside".

ALTER TABLE `shops` ADD COLUMN `shop_image_interior` varchar(255) DEFAULT NULL;
ALTER TABLE `shops` ADD COLUMN `shop_image_exterior` varchar(255) DEFAULT NULL;

-- Note: user already has `shop_image` which can serve as the logo or main thumb.
