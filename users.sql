-- HLK users export (append-only)
-- Schema for table `users`

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `student_id` VARCHAR(32) NOT NULL,
  `class` VARCHAR(32) NOT NULL,
  `grade` VARCHAR(8) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `game_uid` VARCHAR(64) NOT NULL,
  `ip` VARCHAR(64) DEFAULT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample insert row (demo, bạn có thể xoá sau khi test)
INSERT INTO `users` (
  `full_name`, `student_id`, `class`, `grade`,
  `email`, `username`, `password_hash`, `game_uid`,
  `ip`, `user_agent`, `created_at`
) VALUES (
  'Nguyen Van A', '12A3456', '12A1', 'K31',
  'vana@example.com', 'vana123',
  '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJKLmnopqrs', -- hash demo
  '987654321',
  '127.0.0.1',
  'Mozilla/5.0',
  CURRENT_TIMESTAMP
);