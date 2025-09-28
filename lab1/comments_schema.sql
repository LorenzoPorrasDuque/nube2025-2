-- Comments table structure
-- This extends the existing products table with a comments relationship

CREATE TABLE `comments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `product_id` int(11) NOT NULL,
    `name` varchar(100) NOT NULL,
    `comment` text NOT NULL,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_product_id` (`product_id`),
    CONSTRAINT `fk_comments_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;