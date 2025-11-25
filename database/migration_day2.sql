-- Script SQL pour le Jour 2 - Coffre-fort Numérique
-- Ajout des tables folders et shares

-- Table des dossiers
CREATE TABLE IF NOT EXISTS `folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `fk_folders_parent` FOREIGN KEY (`parent_id`) REFERENCES `folders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des partages publics
CREATE TABLE IF NOT EXISTS `shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime DEFAULT NULL,
  `max_downloads` int(11) DEFAULT 0,
  `downloads` int(11) DEFAULT 0,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `file_id` (`file_id`),
  CONSTRAINT `fk_shares_file` FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Modification de la table files pour ajouter le support des dossiers et du chiffrement
-- Ignorer les erreurs si les colonnes existent déjà
ALTER TABLE `files` ADD COLUMN `folder_id` int(11) DEFAULT NULL AFTER `mime_type`;
ALTER TABLE `files` ADD COLUMN `is_encrypted` tinyint(1) DEFAULT 0 AFTER `folder_id`;
ALTER TABLE `files` ADD KEY `folder_id` (`folder_id`);
ALTER TABLE `files` ADD CONSTRAINT `fk_files_folder` FOREIGN KEY (`folder_id`) REFERENCES `folders`(`id`) ON DELETE SET NULL;

-- Insertion de quelques dossiers de test (optionnel)
INSERT INTO `folders` (`name`, `parent_id`, `created_at`) VALUES
('Documents', NULL, NOW()),
('Images', NULL, NOW()),
('Vidéos', NULL, NOW());
