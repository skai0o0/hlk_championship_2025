-- HLK Championship 2025 Database Setup
-- Complete database structure including teams

-- Create database
CREATE DATABASE IF NOT EXISTS `hlk_championship_2025` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hlk_championship_2025`;

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
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_student_id` (`student_id`)
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
  UNIQUE KEY `uniq_team_name` (`team_name`),
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
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  INDEX `idx_team_id` (`team_id`),
  INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data (demo users - you can delete these after testing)
INSERT INTO `users` (
  `full_name`, `student_id`, `class`, `grade`,
  `email`, `username`, `password_hash`, `game_uid`,
  `ip`, `user_agent`, `created_at`
) VALUES 
(
  'Nguyen Van A', '12A3456', '12A1', 'K31',
  'vana@example.com', 'vana123',
  '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJKLmnopqrs', -- hash demo
  '987654321',
  '127.0.0.1',
  'Mozilla/5.0',
  CURRENT_TIMESTAMP
),
(
  'Tran Thi B', '12A3457', '12A2', 'K31',
  'thib@example.com', 'thib456',
  '$2y$10$abcdefghijklmnopqrstuv1234567890abcdefghiJKLmnopqrs', -- hash demo
  '987654322',
  '127.0.0.1',
  'Mozilla/5.0',
  CURRENT_TIMESTAMP
);

-- Sample team data (demo - you can delete this after testing)
INSERT INTO `teams` (`team_code`, `team_name`, `team_description`, `leader_id`) VALUES
('123456', 'Team Demo', 'Đây là đội demo để test chức năng', 1);

INSERT INTO `team_members` (`team_id`, `user_id`, `role`) VALUES
(1, 1, 'leader');

-- Views for easier data retrieval
CREATE VIEW `v_teams_with_members` AS
SELECT 
    t.id as team_id,
    t.team_code,
    t.team_name,
    t.team_description,
    t.created_at as team_created_at,
    u.id as leader_id,
    u.full_name as leader_name,
    u.class as leader_class,
    COUNT(tm.user_id) as member_count
FROM teams t
LEFT JOIN users u ON t.leader_id = u.id
LEFT JOIN team_members tm ON t.id = tm.team_id
GROUP BY t.id, t.team_code, t.team_name, t.team_description, t.created_at, u.id, u.full_name, u.class;

-- Function to generate random team code (MySQL 8.0+)
-- Note: This is a fallback, the PHP code handles team code generation
DELIMITER //
CREATE FUNCTION IF NOT EXISTS generate_team_code() 
RETURNS VARCHAR(6)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE new_code VARCHAR(6);
    DECLARE code_exists INT DEFAULT 1;
    
    WHILE code_exists > 0 DO
        SET new_code = LPAD(FLOOR(RAND() * 900000) + 100000, 6, '0');
        SELECT COUNT(*) INTO code_exists FROM teams WHERE team_code = new_code;
    END WHILE;
    
    RETURN new_code;
END//
DELIMITER ;

-- Create indexes for better performance
CREATE INDEX idx_teams_leader ON teams(leader_id);
CREATE INDEX idx_teams_code ON teams(team_code);
CREATE INDEX idx_team_members_team ON team_members(team_id);
CREATE INDEX idx_team_members_user ON team_members(user_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_student_id ON users(student_id);
