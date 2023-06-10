/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `acs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acs` (
  `acs_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `acs_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `acs_owner` int NOT NULL DEFAULT '0',
  `acs_galaxy` int DEFAULT NULL,
  `acs_system` int DEFAULT NULL,
  `acs_planet` int DEFAULT NULL,
  `acs_planet_type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`acs_id`),
  UNIQUE KEY `acs_name` (`acs_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acs_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `acs_members` (
  `acs_member_id` int unsigned NOT NULL AUTO_INCREMENT,
  `acs_group_id` int unsigned NOT NULL,
  `acs_user_id` int unsigned NOT NULL,
  PRIMARY KEY (`acs_member_id`),
  UNIQUE KEY `acs_group_id` (`acs_group_id`,`acs_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alliance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alliance` (
  `alliance_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `alliance_name` varchar(32) DEFAULT NULL,
  `alliance_tag` varchar(8) DEFAULT NULL,
  `alliance_owner` int NOT NULL DEFAULT '0',
  `alliance_register_time` int NOT NULL DEFAULT '0',
  `alliance_description` text,
  `alliance_web` varchar(255) DEFAULT NULL,
  `alliance_text` text,
  `alliance_image` varchar(255) DEFAULT NULL,
  `alliance_request` text,
  `alliance_request_notallow` tinyint NOT NULL DEFAULT '0',
  `alliance_ranks` text,
  PRIMARY KEY (`alliance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `alliance_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alliance_statistics` (
  `alliance_statistic_alliance_id` bigint unsigned NOT NULL,
  `alliance_statistic_buildings_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `alliance_statistic_buildings_old_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_buildings_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_defenses_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `alliance_statistic_defenses_old_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_defenses_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_ships_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `alliance_statistic_ships_old_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_ships_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_technology_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `alliance_statistic_technology_old_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_technology_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_total_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `alliance_statistic_total_old_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_total_rank` int NOT NULL DEFAULT '0',
  `alliance_statistic_update_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`alliance_statistic_alliance_id`),
  CONSTRAINT `alliance_statistics_alliance_statistic_alliance_id_foreign` FOREIGN KEY (`alliance_statistic_alliance_id`) REFERENCES `alliance` (`alliance_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banned`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `banned` (
  `banned_id` bigint NOT NULL AUTO_INCREMENT,
  `banned_who` varchar(64) NOT NULL DEFAULT '',
  `banned_theme` text NOT NULL,
  `banned_time` int NOT NULL DEFAULT '0',
  `banned_longer` int NOT NULL DEFAULT '0',
  `banned_author` varchar(64) NOT NULL DEFAULT '',
  `banned_email` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`banned_id`),
  KEY `ID` (`banned_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `buddys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buddys` (
  `buddy_id` int unsigned NOT NULL AUTO_INCREMENT,
  `buddy_sender` int unsigned NOT NULL,
  `buddy_receiver` int unsigned NOT NULL,
  `buddy_status` tinyint(1) NOT NULL DEFAULT '0',
  `buddy_request_text` text,
  PRIMARY KEY (`buddy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `buildings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buildings` (
  `building_id` int unsigned NOT NULL AUTO_INCREMENT,
  `building_planet_id` int unsigned NOT NULL,
  `building_metal_mine` int NOT NULL DEFAULT '0',
  `building_crystal_mine` int NOT NULL DEFAULT '0',
  `building_deuterium_sintetizer` int NOT NULL DEFAULT '0',
  `building_solar_plant` int NOT NULL DEFAULT '0',
  `building_fusion_reactor` int NOT NULL DEFAULT '0',
  `building_robot_factory` int NOT NULL DEFAULT '0',
  `building_nano_factory` int NOT NULL DEFAULT '0',
  `building_hangar` int NOT NULL DEFAULT '0',
  `building_metal_store` int NOT NULL DEFAULT '0',
  `building_crystal_store` int NOT NULL DEFAULT '0',
  `building_deuterium_tank` int NOT NULL DEFAULT '0',
  `building_laboratory` int NOT NULL DEFAULT '0',
  `building_terraformer` int NOT NULL DEFAULT '0',
  `building_ally_deposit` int NOT NULL DEFAULT '0',
  `building_missile_silo` int NOT NULL DEFAULT '0',
  `building_mondbasis` int NOT NULL DEFAULT '0',
  `building_phalanx` int NOT NULL DEFAULT '0',
  `building_jump_gate` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`building_id`),
  UNIQUE KEY `building_planet_id` (`building_planet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `changelog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `changelog` (
  `changelog_id` int unsigned NOT NULL,
  `changelog_lang_id` int NOT NULL,
  `changelog_version` varchar(16) NOT NULL,
  `changelog_date` date NOT NULL,
  `changelog_description` text NOT NULL,
  PRIMARY KEY (`changelog_id`),
  UNIQUE KEY `changelog_id` (`changelog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `defenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `defenses` (
  `defense_id` int unsigned NOT NULL AUTO_INCREMENT,
  `defense_planet_id` int unsigned NOT NULL,
  `defense_rocket_launcher` int NOT NULL DEFAULT '0',
  `defense_light_laser` int NOT NULL DEFAULT '0',
  `defense_heavy_laser` int NOT NULL DEFAULT '0',
  `defense_ion_cannon` int NOT NULL DEFAULT '0',
  `defense_gauss_cannon` int NOT NULL DEFAULT '0',
  `defense_plasma_turret` int NOT NULL DEFAULT '0',
  `defense_small_shield_dome` int NOT NULL DEFAULT '0',
  `defense_large_shield_dome` int NOT NULL DEFAULT '0',
  `defense_anti-ballistic_missile` int NOT NULL DEFAULT '0',
  `defense_interplanetary_missile` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`defense_id`),
  UNIQUE KEY `defense_planet_id` (`defense_planet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fleets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fleets` (
  `fleet_id` bigint NOT NULL AUTO_INCREMENT,
  `fleet_owner` int NOT NULL DEFAULT '0',
  `fleet_mission` int NOT NULL DEFAULT '0',
  `fleet_amount` bigint NOT NULL DEFAULT '0',
  `fleet_array` text,
  `fleet_start_time` int NOT NULL DEFAULT '0',
  `fleet_start_galaxy` int NOT NULL DEFAULT '0',
  `fleet_start_system` int NOT NULL DEFAULT '0',
  `fleet_start_planet` int NOT NULL DEFAULT '0',
  `fleet_start_type` int NOT NULL DEFAULT '0',
  `fleet_end_time` int NOT NULL DEFAULT '0',
  `fleet_end_stay` int NOT NULL DEFAULT '0',
  `fleet_end_galaxy` int NOT NULL DEFAULT '0',
  `fleet_end_system` int NOT NULL DEFAULT '0',
  `fleet_end_planet` int NOT NULL DEFAULT '0',
  `fleet_end_type` int NOT NULL DEFAULT '0',
  `fleet_target_obj` int NOT NULL DEFAULT '0',
  `fleet_resource_metal` bigint NOT NULL DEFAULT '0',
  `fleet_resource_crystal` bigint NOT NULL DEFAULT '0',
  `fleet_resource_deuterium` bigint NOT NULL DEFAULT '0',
  `fleet_fuel` bigint NOT NULL DEFAULT '0',
  `fleet_target_owner` int NOT NULL DEFAULT '0',
  `fleet_group` varchar(15) NOT NULL DEFAULT '0',
  `fleet_mess` tinyint(1) NOT NULL DEFAULT '0',
  `fleet_creation` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`fleet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` int NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `code` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `language_id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `message_id` bigint NOT NULL AUTO_INCREMENT,
  `message_sender` int NOT NULL DEFAULT '0',
  `message_receiver` int NOT NULL DEFAULT '0',
  `message_time` int NOT NULL DEFAULT '0',
  `message_type` int NOT NULL DEFAULT '0',
  `message_from` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message_subject` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `message_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `message_read` tinyint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `note_id` bigint NOT NULL AUTO_INCREMENT,
  `note_owner` int DEFAULT NULL,
  `note_time` int DEFAULT NULL,
  `note_priority` tinyint(1) DEFAULT NULL,
  `note_title` varchar(32) DEFAULT NULL,
  `note_text` text,
  PRIMARY KEY (`note_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) DEFAULT NULL,
  `value` longtext NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `option_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `planets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `planets` (
  `planet_id` bigint NOT NULL AUTO_INCREMENT,
  `planet_name` varchar(255) DEFAULT NULL,
  `planet_user_id` int DEFAULT NULL,
  `planet_galaxy` int NOT NULL DEFAULT '0',
  `planet_system` int NOT NULL DEFAULT '0',
  `planet_planet` int NOT NULL DEFAULT '0',
  `planet_last_update` int DEFAULT NULL,
  `planet_type` int NOT NULL DEFAULT '1',
  `planet_destroyed` int NOT NULL DEFAULT '0',
  `planet_b_building` int NOT NULL DEFAULT '0',
  `planet_b_building_id` text NOT NULL,
  `planet_b_tech` int NOT NULL DEFAULT '0',
  `planet_b_tech_id` int NOT NULL DEFAULT '0',
  `planet_b_hangar` int NOT NULL DEFAULT '0',
  `planet_b_hangar_id` text,
  `planet_image` varchar(32) NOT NULL DEFAULT 'normaltempplanet01',
  `planet_diameter` int NOT NULL DEFAULT '12800',
  `planet_field_current` int NOT NULL DEFAULT '0',
  `planet_field_max` int NOT NULL DEFAULT '163',
  `planet_temp_min` int NOT NULL DEFAULT '-17',
  `planet_temp_max` int NOT NULL DEFAULT '23',
  `planet_metal` double(132,8) NOT NULL DEFAULT '0.00000000',
  `planet_metal_perhour` int NOT NULL DEFAULT '0',
  `planet_crystal` double(132,8) NOT NULL DEFAULT '0.00000000',
  `planet_crystal_perhour` int NOT NULL DEFAULT '0',
  `planet_deuterium` double(132,8) NOT NULL DEFAULT '0.00000000',
  `planet_deuterium_perhour` int NOT NULL DEFAULT '0',
  `planet_energy_used` int NOT NULL DEFAULT '0',
  `planet_energy_max` bigint NOT NULL DEFAULT '0',
  `planet_building_metal_mine_percent` int NOT NULL DEFAULT '10',
  `planet_building_crystal_mine_percent` int NOT NULL DEFAULT '10',
  `planet_building_deuterium_sintetizer_percent` int NOT NULL DEFAULT '10',
  `planet_building_solar_plant_percent` int NOT NULL DEFAULT '10',
  `planet_building_fusion_reactor_percent` int NOT NULL DEFAULT '10',
  `planet_ship_solar_satellite_percent` int NOT NULL DEFAULT '10',
  `planet_last_jump_time` int NOT NULL DEFAULT '0',
  `planet_debris_metal` bigint NOT NULL DEFAULT '0',
  `planet_debris_crystal` bigint NOT NULL DEFAULT '0',
  `planet_invisible_start_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`planet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `preferences` (
  `preference_id` int unsigned NOT NULL AUTO_INCREMENT,
  `preference_user_id` int NOT NULL,
  `preference_nickname_change` int DEFAULT NULL,
  `preference_spy_probes` tinyint NOT NULL DEFAULT '1',
  `preference_planet_sort` tinyint(1) NOT NULL DEFAULT '0',
  `preference_planet_sort_sequence` tinyint(1) NOT NULL DEFAULT '0',
  `preference_vacation_mode` int DEFAULT NULL,
  `preference_delete_mode` int DEFAULT NULL,
  PRIMARY KEY (`preference_id`),
  UNIQUE KEY `preference_user_id` (`preference_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `premium`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `premium` (
  `premium_user_id` int unsigned NOT NULL,
  `premium_dark_matter` int NOT NULL DEFAULT '0',
  `premium_officier_commander` int NOT NULL DEFAULT '0',
  `premium_officier_admiral` int NOT NULL DEFAULT '0',
  `premium_officier_engineer` int NOT NULL DEFAULT '0',
  `premium_officier_geologist` int NOT NULL DEFAULT '0',
  `premium_officier_technocrat` int NOT NULL DEFAULT '0',
  UNIQUE KEY `premium_user_id` (`premium_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reports` (
  `report_owners` varchar(255) NOT NULL,
  `report_rid` varchar(42) NOT NULL,
  `report_content` text NOT NULL,
  `report_destroyed` tinyint unsigned NOT NULL DEFAULT '0',
  `report_time` int unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `report_rid` (`report_rid`),
  KEY `time` (`report_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `research`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `research` (
  `research_id` int unsigned NOT NULL AUTO_INCREMENT,
  `research_user_id` int unsigned NOT NULL,
  `research_current_research` int NOT NULL DEFAULT '0',
  `research_espionage_technology` int NOT NULL DEFAULT '0',
  `research_computer_technology` int NOT NULL DEFAULT '0',
  `research_weapons_technology` int NOT NULL DEFAULT '0',
  `research_shielding_technology` int NOT NULL DEFAULT '0',
  `research_armour_technology` int NOT NULL DEFAULT '0',
  `research_energy_technology` int NOT NULL DEFAULT '0',
  `research_hyperspace_technology` int NOT NULL DEFAULT '0',
  `research_combustion_drive` int NOT NULL DEFAULT '0',
  `research_impulse_drive` int NOT NULL DEFAULT '0',
  `research_hyperspace_drive` int NOT NULL DEFAULT '0',
  `research_laser_technology` int NOT NULL DEFAULT '0',
  `research_ionic_technology` int NOT NULL DEFAULT '0',
  `research_plasma_technology` int NOT NULL DEFAULT '0',
  `research_intergalactic_research_network` int NOT NULL DEFAULT '0',
  `research_astrophysics` int NOT NULL DEFAULT '0',
  `research_graviton_technology` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`research_id`),
  UNIQUE KEY `research_user_id` (`research_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ships` (
  `ship_id` int unsigned NOT NULL AUTO_INCREMENT,
  `ship_planet_id` int unsigned NOT NULL,
  `ship_small_cargo_ship` int NOT NULL DEFAULT '0',
  `ship_big_cargo_ship` int NOT NULL DEFAULT '0',
  `ship_light_fighter` int NOT NULL DEFAULT '0',
  `ship_heavy_fighter` int NOT NULL DEFAULT '0',
  `ship_cruiser` int NOT NULL DEFAULT '0',
  `ship_battleship` int NOT NULL DEFAULT '0',
  `ship_colony_ship` int NOT NULL DEFAULT '0',
  `ship_recycler` int NOT NULL DEFAULT '0',
  `ship_espionage_probe` int NOT NULL DEFAULT '0',
  `ship_bomber` int NOT NULL DEFAULT '0',
  `ship_solar_satellite` int NOT NULL DEFAULT '0',
  `ship_destroyer` int NOT NULL DEFAULT '0',
  `ship_deathstar` int NOT NULL DEFAULT '0',
  `ship_battlecruiser` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`ship_id`),
  UNIQUE KEY `ship_planet_id` (`ship_planet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(64) NOT NULL DEFAULT '',
  `user_password` varchar(64) NOT NULL DEFAULT '',
  `user_email` varchar(64) NOT NULL DEFAULT '',
  `user_authlevel` tinyint NOT NULL DEFAULT '0',
  `user_home_planet_id` int NOT NULL DEFAULT '0',
  `user_galaxy` int NOT NULL DEFAULT '0',
  `user_system` int NOT NULL DEFAULT '0',
  `user_planet` int NOT NULL DEFAULT '0',
  `user_current_planet` int NOT NULL DEFAULT '0',
  `user_lastip` varchar(39) NOT NULL DEFAULT '',
  `user_ip_at_reg` varchar(39) NOT NULL DEFAULT '',
  `user_agent` text,
  `user_current_page` text,
  `user_register_time` int NOT NULL DEFAULT '0',
  `user_onlinetime` int NOT NULL DEFAULT '0',
  `user_fleet_shortcuts` text,
  `user_ally_id` int NOT NULL DEFAULT '0',
  `user_ally_request` int NOT NULL DEFAULT '0',
  `user_ally_request_text` text,
  `user_ally_register_time` int NOT NULL DEFAULT '0',
  `user_ally_rank_id` int NOT NULL DEFAULT '0',
  `user_banned` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users_statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_statistics` (
  `user_statistic_user_id` bigint unsigned NOT NULL,
  `user_statistic_buildings_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `user_statistic_buildings_old_rank` int NOT NULL DEFAULT '0',
  `user_statistic_buildings_rank` int NOT NULL DEFAULT '0',
  `user_statistic_defenses_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `user_statistic_defenses_old_rank` int NOT NULL DEFAULT '0',
  `user_statistic_defenses_rank` int NOT NULL DEFAULT '0',
  `user_statistic_ships_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `user_statistic_ships_old_rank` int NOT NULL DEFAULT '0',
  `user_statistic_ships_rank` int NOT NULL DEFAULT '0',
  `user_statistic_technology_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `user_statistic_technology_old_rank` int NOT NULL DEFAULT '0',
  `user_statistic_technology_rank` int NOT NULL DEFAULT '0',
  `user_statistic_total_points` double(132,8) NOT NULL DEFAULT '0.00000000',
  `user_statistic_total_old_rank` int NOT NULL DEFAULT '0',
  `user_statistic_total_rank` int NOT NULL DEFAULT '0',
  `user_statistic_update_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_statistic_user_id`),
  CONSTRAINT `users_statistics_user_statistic_user_id_foreign` FOREIGN KEY (`user_statistic_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` VALUES (1,'2014_10_12_100000_create_password_reset_tokens_table',1);
INSERT INTO `migrations` VALUES (2,'2019_08_19_000000_create_failed_jobs_table',1);
INSERT INTO `migrations` VALUES (3,'2019_12_14_000001_create_personal_access_tokens_table',1);
INSERT INTO `migrations` VALUES (4,'2023_04_15_153054_create_sessions_table',1);
INSERT INTO `migrations` VALUES (5,'2023_06_10_092839_update_languages_table',2);
INSERT INTO `migrations` VALUES (6,'2023_06_10_093448_update_languages_records',3);
INSERT INTO `migrations` VALUES (7,'2023_06_10_143652_update_version',4);
INSERT INTO `migrations` VALUES (10,'2023_06_10_152102_create_alliance_statistics_alliance_statistic_alliance_id_fk',5);
INSERT INTO `migrations` VALUES (11,'2023_06_10_155911_update_options_table',6);
INSERT INTO `migrations` VALUES (12,'2023_06_10_160413_update_options_types',7);
