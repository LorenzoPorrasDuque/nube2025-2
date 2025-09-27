-- Add this to your existing database to create the comments table
CREATE TABLE `product_comments` (
    `comment_id` int(11) NOT NULL AUTO_INCREMENT,
    `product_name` varchar(255) NOT NULL,
    `user_name` varchar(100) NOT NULL,
    `comment_text` text NOT NULL,
    `comment_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`comment_id`),
    INDEX `idx_product_name` (`product_name`),
    INDEX `idx_comment_date` (`comment_date`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- Insert some sample data for testing
INSERT INTO
    `product_comments` (
        `product_name`,
        `user_name`,
        `comment_text`,
        `comment_date`
    )
VALUES (
        'Vulture Of Fortune',
        'John Doe',
        'Great product! Really loved the quality.',
        '2025-09-26 10:30:00'
    ),
    (
        'Guardian Without Duty',
        'Jane Smith',
        'Good value for money. Would recommend!',
        '2025-09-26 11:15:00'
    ),
    (
        'Enemies Without Hope',
        'Mike Johnson',
        'Excellent service and fast delivery.',
        '2025-09-26 12:00:00'
    );