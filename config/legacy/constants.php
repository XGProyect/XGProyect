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
define('ONE_DAY', (60 * 60 * 24)); // 1 DAY
define('ONE_WEEK', (ONE_DAY * 7)); // 1 WEEK
define('ONE_MONTH', (ONE_DAY * 30)); // 1 MONTH

/**
 *
 * GAME MECHANICS RELATED
 * You can change almost anything below without breaking the game
 *
 */
// UNIVERSE DATA, GALAXY, SYSTEMS AND PLANETS || DEFAULT 9-499-15 RESPECTIVELY
define('MAX_GALAXY_IN_WORLD', 9);
define('MAX_SYSTEM_IN_GALAXY', 499);
define('MAX_PLANET_IN_SYSTEM', 15);

/**
 * New accounts planet position separation
 * By default new players are separated by just 1 galaxy and/or 1 system
 * Changing these factors can increase the initial separation
 */
define('GALAXY_SEPARATION_FACTOR', 1);
define('SYSTEM_SEPARATION_FACTOR', 1);
define('PLANET_SEPARATION_FACTOR', 2);

// FIELDS FOR EACH LEVEL OF THE LUNAR BASE
define('FIELDS_BY_MOONBASIS_LEVEL', 3);

// FIELDS FOR EACH LEVEL OF THE TERRAFORMER
define('FIELDS_BY_TERRAFORMER', 5);

// NUMBER OF BUILDINGS THAT CAN GO IN THE CONSTRUCTION QUEUE
define('MAX_BUILDING_QUEUE_SIZE', 5);

// NUMBER OF SHIPS THAT CAN BUILD FOR ONCE
define('MAX_FLEET_OR_DEFS_PER_ROW', 9999);

// MAX RESULTS TO SHOW IN SEARCH
define('MAX_SEARCH_RESULTS', 25);

//PLANET SIZE MULTIPLER
define('PLANETSIZE_MULTIPLER', 1);

// INITIAL RESOURCE OF NEW PLANETS
define('BUILD_METAL', 500);
define('BUILD_CRISTAL', 500);
define('BUILD_DEUTERIUM', 0);

// OFFICIERS DEFAULT VALUES
define('AMIRAL', 2);
define('ENGINEER_DEFENSE', 2);
define('ENGINEER_ENERGY', 0.1);
define('GEOLOGUE', 0.1);
define('TECHNOCRATE_SPY', 2);
define('TECHNOCRATE_SPEED', 0.25);

// INVISIBLES DEBRIS
define('DEBRIS_LIFE_TIME', ONE_WEEK);
define('DEBRIS_MIN_VISIBLE_SIZE', 300);

// DESTROYED PLANETS LIFE TIME
define('PLANETS_LIFE_TIME', 24); // IN HOURS

// VACATION TIME THAT AN USER HAS TO BE ON VACATION MODE BEFORE IT CAN REMOVE IT
define('VACATION_TIME_FORCED', 2); // IN DAYS

// RESOURCE MARKET
define('BASIC_RESOURCE_MARKET_DM', [
    'metal' => 4500,
    'crystal' => 9000,
    'deuterium' => 13500,
]);

// PHALANX COST
define('PHALANX_COST', 10000);

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
define('ACS', '{xgp_prefix}acs');
define('ACS_MEMBERS', '{xgp_prefix}acs_members');
define('ALLIANCE', '{xgp_prefix}alliance');
define('ALLIANCE_STATISTICS', '{xgp_prefix}alliance_statistics');
define('BANNED', '{xgp_prefix}bans');
define('BUDDY', '{xgp_prefix}buddys');
define('BUILDINGS', '{xgp_prefix}buildings');
define('CHANGELOG', '{xgp_prefix}changelog');
define('DEFENSES', '{xgp_prefix}defenses');
define('FLEETS', '{xgp_prefix}fleets');
define('LANGUAGES', '{xgp_prefix}languages');
define('MESSAGES', '{xgp_prefix}messages');
define('NOTES', '{xgp_prefix}notes');
define('OPTIONS', '{xgp_prefix}options');
define('PLANETS', '{xgp_prefix}planets');
define('PREFERENCES', '{xgp_prefix}preferences');
define('PREMIUM', '{xgp_prefix}premium');
define('RESEARCH', '{xgp_prefix}research');
define('REPORTS', '{xgp_prefix}reports');
define('SESSIONS', '{xgp_prefix}sessions');
define('SHIPS', '{xgp_prefix}ships');
define('USERS', '{xgp_prefix}users');
define('USERS_STATISTICS', '{xgp_prefix}users_statistics');
