-- Active: 1759072799969@@database.c4r4g0w6srxg.us-east-1.rds.amazonaws.com@3306@prueba
CREATE TABLE `products` (
    `product_id` int(11) NOT NULL,
    `product_name` varchar(128) NOT NULL,
    `product_description` text
) ENGINE = InnoDB DEFAULT CHARSET = latin1;

ALTER TABLE `products`
ADD PRIMARY KEY (`product_id`),
ADD KEY `product_name` (`product_name`);

ALTER TABLE `products`
MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 7;