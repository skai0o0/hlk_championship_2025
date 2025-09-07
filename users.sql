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

-- Schema for table `teams`
CREATE TABLE IF NOT EXISTS `teams` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_code` VARCHAR(6) NOT NULL,
  `team_name` VARCHAR(255) NOT NULL,
  `team_description` TEXT DEFAULT NULL,
  `leader_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_team_code` (`team_code`),
  FOREIGN KEY (`leader_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Schema for table `team_members`
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `team_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `role` ENUM('leader', 'member') NOT NULL DEFAULT 'member',
  `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_team` (`user_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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