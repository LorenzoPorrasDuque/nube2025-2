#!/bin/bash
# update everything
sudo apt update -y
sudo apt install -y

# Install packages
sudo apt install mysql-server -y
sudo apt install php-mysql -y
sudo apt install php -y
sudo apt install apache2 -y

cp index.html /var/www/html
cp 2-products.php /var/www/html


sudo mysql <<EOF
CREATE DATABASE IF NOT EXISTS prueba;
CREATE USER IF NOT EXISTS 'prueba'@'localhost' IDENTIFIED BY 'prueba';
GRANT ALL PRIVILEGES ON prueba.* TO 'prueba'@'localhost';
FLUSH PRIVILEGES;

USE prueba;

CREATE TABLE IF NOT EXISTS products (
  product_id int(11) NOT NULL,
  product_name varchar(128) NOT NULL,
  product_description text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO products (product_id, product_name, product_description) VALUES
(1, 'Vulture Of Fortune', 'A sweated bump consents across the here separator.'),
(2, 'Guardian Without Duty', 'Does a table migrate inside an excessive paranoid?'),
(3, 'Enemies Without Hope', 'A cured parameter fears behind the phenomenon.'),
(4, 'Lords Of The Void', 'The diary scores around the generalized lie.'),
(5, 'Doctors And Aliens', 'The diary scores around the generalized lie.'),
(6, 'Blacksmiths And Criminals', 'A considerable snail works into a purchase.');

ALTER TABLE products
  ADD PRIMARY KEY (product_id),
  ADD KEY product_name (product_name);

ALTER TABLE products
  MODIFY product_id int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
EOF

