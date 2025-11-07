-- Baza danych: genesis_duels

-- Tabela Użytkowników
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `avatar` VARCHAR(255) DEFAULT 'default.png',
  `level` INT DEFAULT 1,
  `experience` INT DEFAULT 0,
  `credits` INT DEFAULT 500,
  `shards` INT DEFAULT 10,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela Kart (definicje wszystkich istniejących kart w grze)
CREATE TABLE `cards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `type` ENUM('Creature', 'Spell') NOT NULL,
  `rarity` ENUM('Common', 'Rare', 'Epic', 'Legendary') NOT NULL,
  `cost` INT NOT NULL,
  `attack` INT, -- NULL dla zaklęć
  `health` INT, -- NULL dla zaklęć
  `image_url` VARCHAR(255)
);

-- Tabela łącząca Użytkowników i Karty (kolekcja gracza)
CREATE TABLE `user_cards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `card_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`card_id`) REFERENCES `cards`(`id`)
);

-- Tabela Talii
CREATE TABLE `decks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);

-- Tabela łącząca Talie i Karty
CREATE TABLE `deck_cards` (
  `deck_id` INT NOT NULL,
  `card_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  PRIMARY KEY (`deck_id`, `card_id`),
  FOREIGN KEY (`deck_id`) REFERENCES `decks`(`id`),
  FOREIGN KEY (`card_id`) REFERENCES `cards`(`id`)
);

-- Tabela Sesji Gry (do śledzenia aktywnych meczy)
CREATE TABLE `game_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `player1_id` INT NOT NULL,
  `player2_id` INT, -- Może być NULL dla gier PvE
  `player1_deck_id` INT NOT NULL,
  `player2_deck_id` INT,
  `player1_hp` INT DEFAULT 30,
  `player2_hp` INT DEFAULT 30,
  `current_turn` INT NOT NULL,
  `status` ENUM('ongoing', 'finished', 'abandoned') DEFAULT 'ongoing',
  `winner_id` INT,
  `game_type` ENUM('PvE', 'PvP', 'Gambit') NOT NULL,
  `bet_amount` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`player1_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`player2_id`) REFERENCES `users`(`id`),
  FOREIGN KEY (`winner_id`) REFERENCES `users`(`id`)
);

-- Tabela Transakcji (do śledzenia zakupów i nagród)
CREATE TABLE `transactions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `type` ENUM('lootbox_purchase', 'shard_purchase', 'game_reward', 'gambit_win') NOT NULL,
  `amount_credits` INT,
  `amount_shards` INT,
  `description` VARCHAR(255),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
);
