<?php

/**
 * legacy
 * @todo deprecate
 */

//##########################################################################
//
// Constants should not be changed, unless you know what you are doing!
//
//##########################################################################

/**
 *
 * TIMING CONSTANTS
 *
 */
defined('ONE_DAY') || define('ONE_DAY', (60 * 60 * 24)); // 1 DAY
defined('ONE_WEEK') || define('ONE_WEEK', (ONE_DAY * 7)); // 1 WEEK
defined('ONE_MONTH') || define('ONE_MONTH', (ONE_DAY * 30)); // 1 MONTH

/**
 *
 * GAME MECHANICS RELATED
 * You can change almost anything below without breaking the game
 *
 */
// UNIVERSE DATA, GALAXY, SYSTEMS AND PLANETS || DEFAULT 9-499-15 RESPECTIVELY
defined('MAX_GALAXY_IN_WORLD') || define('MAX_GALAXY_IN_WORLD', 9);
defined('MAX_SYSTEM_IN_GALAXY') || define('MAX_SYSTEM_IN_GALAXY', 499);
defined('MAX_PLANET_IN_SYSTEM') || define('MAX_PLANET_IN_SYSTEM', 15);

/**
 * New accounts planet position separation
 * By default new players are separated by just 1 galaxy and/or 1 system
 * Changing these factors can increase the initial separation
 */
defined('GALAXY_SEPARATION_FACTOR') || define('GALAXY_SEPARATION_FACTOR', 1);
defined('SYSTEM_SEPARATION_FACTOR') || define('SYSTEM_SEPARATION_FACTOR', 1);
defined('PLANET_SEPARATION_FACTOR') || define('PLANET_SEPARATION_FACTOR', 2);

// FIELDS FOR EACH LEVEL OF THE LUNAR BASE
defined('FIELDS_BY_MOONBASIS_LEVEL') || define('FIELDS_BY_MOONBASIS_LEVEL', 3);

// FIELDS FOR EACH LEVEL OF THE TERRAFORMER
defined('FIELDS_BY_TERRAFORMER') || define('FIELDS_BY_TERRAFORMER', 5);

// NUMBER OF BUILDINGS THAT CAN GO IN THE CONSTRUCTION QUEUE
defined('MAX_BUILDING_QUEUE_SIZE') || define('MAX_BUILDING_QUEUE_SIZE', 5);

// NUMBER OF SHIPS THAT CAN BUILD FOR ONCE
defined('MAX_FLEET_OR_DEFS_PER_ROW') || define('MAX_FLEET_OR_DEFS_PER_ROW', 9999);

// MAX RESULTS TO SHOW IN SEARCH
defined('MAX_SEARCH_RESULTS') || define('MAX_SEARCH_RESULTS', 25);

//PLANET SIZE MULTIPLER
defined('PLANETSIZE_MULTIPLER') || define('PLANETSIZE_MULTIPLER', 1);

// INITIAL RESOURCE OF NEW PLANETS
defined('BUILD_METAL') || define('BUILD_METAL', 500);
defined('BUILD_CRISTAL') || define('BUILD_CRISTAL', 500);
defined('BUILD_DEUTERIUM') || define('BUILD_DEUTERIUM', 0);

// OFFICIERS DEFAULT VALUES
defined('AMIRAL') || define('AMIRAL', 2);
defined('ENGINEER_DEFENSE') || define('ENGINEER_DEFENSE', 2);
defined('ENGINEER_ENERGY') || define('ENGINEER_ENERGY', 0.1);
defined('GEOLOGUE') || define('GEOLOGUE', 0.1);
defined('TECHNOCRATE_SPY') || define('TECHNOCRATE_SPY', 2);
defined('TECHNOCRATE_SPEED') || define('TECHNOCRATE_SPEED', 0.25);

// INVISIBLES DEBRIS
defined('DEBRIS_LIFE_TIME') || define('DEBRIS_LIFE_TIME', ONE_WEEK);
defined('DEBRIS_MIN_VISIBLE_SIZE') || define('DEBRIS_MIN_VISIBLE_SIZE', 300);

// DESTROYED PLANETS LIFE TIME
defined('PLANETS_LIFE_TIME') || define('PLANETS_LIFE_TIME', 24); // IN HOURS

// VACATION TIME THAT AN USER HAS TO BE ON VACATION MODE BEFORE IT CAN REMOVE IT
defined('VACATION_TIME_FORCED') || define('VACATION_TIME_FORCED', 2); // IN DAYS

// RESOURCE MARKET
defined('BASIC_RESOURCE_MARKET_DM') || define('BASIC_RESOURCE_MARKET_DM', [
    'metal' => 4500,
    'crystal' => 9000,
    'deuterium' => 13500,
]);

// PHALANX COST
defined('PHALANX_COST') || define('PHALANX_COST', 10000);

/**
 *
 * DATABASE RELATED
 *
 */
//##########################################################################
//
// Constants should not be changed, unless you know what you are doing!
//
//##########################################################################

// TABLES
defined('ACS') || define('ACS', '{xgp_prefix}acs');
defined('ACS_MEMBERS') || define('ACS_MEMBERS', '{xgp_prefix}acs_members');
defined('ALLIANCE') || define('ALLIANCE', '{xgp_prefix}alliance');
defined('ALLIANCE_STATISTICS') || define('ALLIANCE_STATISTICS', '{xgp_prefix}alliance_statistics');
defined('BANNED') || define('BANNED', '{xgp_prefix}bans');
defined('BUDDY') || define('BUDDY', '{xgp_prefix}buddys');
defined('BUILDINGS') || define('BUILDINGS', '{xgp_prefix}buildings');
defined('CHANGELOG') || define('CHANGELOG', '{xgp_prefix}changelog');
defined('DEFENSES') || define('DEFENSES', '{xgp_prefix}defenses');
defined('FLEETS') || define('FLEETS', '{xgp_prefix}fleets');
defined('LANGUAGES') || define('LANGUAGES', '{xgp_prefix}languages');
defined('MESSAGES') || define('MESSAGES', '{xgp_prefix}messages');
defined('NOTES') || define('NOTES', '{xgp_prefix}notes');
defined('OPTIONS') || define('OPTIONS', '{xgp_prefix}options');
defined('PLANETS') || define('PLANETS', '{xgp_prefix}planets');
defined('PREFERENCES') || define('PREFERENCES', '{xgp_prefix}preferences');
defined('PREMIUM') || define('PREMIUM', '{xgp_prefix}premium');
defined('RESEARCH') || define('RESEARCH', '{xgp_prefix}research');
defined('REPORTS') || define('REPORTS', '{xgp_prefix}reports');
defined('SHIPS') || define('SHIPS', '{xgp_prefix}ships');
defined('USERS') || define('USERS', '{xgp_prefix}users');
defined('USERS_STATISTICS') || define('USERS_STATISTICS', '{xgp_prefix}users_statistics');
