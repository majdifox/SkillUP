DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
    `category_id` int(11) NOT NULL,
  `category_name` varchar(20) DEFAULT NULL,
  `description` varchar(20) DEFAULT NULL,
  `deletedCategory` int(11) DEFAULT 0
)