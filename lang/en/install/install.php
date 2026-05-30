<?php

declare(strict_types=1);

return [
    // sidebar
    'sidebar_heading' => 'Install',
    'steps' => 'Steps',
    'requirements' => 'Requirements',
    'database' => 'Database settings',
    'tables' => 'Create tables',
    'admin' => 'Administrator details',
    'final' => 'Final Steps',
    'finish' => 'Finish',

    // next step notices
    'index_notice' => 'On the next step we\'ll check if you can install XGP',
    'requirements_notice' => 'On the next step we\'ll begin to install XGP 🎉',
    'database_notice' => 'On the next step we\'ll start creating and filling the tables with data. <strong>Warning!</strong> The next step will clean up the database!',
    'tables_notice' => 'On the next step we\'ll create the admin account.',
    'admin_notice' => 'On the next step we\'ll do some final checks.',
    'final_notice' => 'All done! 🎉 Let\'s go to the Admin CP',

    // home
    'welcome' => 'Welcome to XG Proyect!',
    'introduction' => '<strong>XG Proyect</strong>, also known as XGP, is the ultimate OGame clone that you can find. <strong>XGP 4</strong> is the latest and most reliable package ever developed. Like previous versions, XG Proyect receives support from the team formerly known as Xtreme-gameZ, ensuring top-quality care and release stability.<br><br>XG Proyect 4 is dedicated to constant growth, stability, flexibility, dynamism, quality, and user confidence. We always aim for XG Proyect to exceed your expectations.<br><br>The installation system will seamlessly guide you through the installation process. If you have any doubts, problems, or queries, feel free to visit our <a href="https://www.xgproyect.org/" target="_blank"><em>support and development community at GitHub</em></a>.',
    'license' => 'License',
    'accept' => 'Sure, got it!',

    // requirements
    'requirements_details' => 'These requirements are the minimum needed to run XGP.<br><br>At the moment we only support MySQL / MariaDB, but we intend to support other drivers in a future. The database server version is validated after you enter the connection details.',
    'php_version_check' => 'PHP Version :version',
    'php_version_current' => 'You have :php',
    'php_version_need' => 'You need at least :version',
    'mysql_check' => 'MySQL / MariaDB :version+',
    'mysql_check_current' => 'Validated after you enter the database credentials',
    'mysql_check_need' => 'You need MySQL / MariaDB :version or later',
    'config_writable' => 'Writable config path',
    'config_writable_ok' => 'OK',
    'config_writable_need' => ':file is not writable',
    'php_ext_check' => 'PHP Extensions',
    'php_ext_check_ok' => 'OK',
    'php_ext_check_need' => 'You need: :ext',
    'requirements_fail' => 'Your system does not meet the requirements, you cannot continue!',

    // database
    'database_details' => 'Here you\'ll be able to set up your database connection details.<br><br>Please pay attention to your settings, since they need to be exactly the same.',
    'driver' => 'Driver',
    'host' => 'Database host',
    'port' => 'Database port',
    'port_help' => 'Leave this blank unless you know the server operates on a non-standard port.',
    'db_name' => 'Database name',
    'db_username' => 'Database username',
    'db_password' => 'Database password',
    'prefix' => 'Tables prefix',
    'prefix_help' => 'Mostly helpful if you plan to run multiple XGP installations on the same database.',
    'db_check' => 'Try to connect',
    'db_connect_success' => 'Connection stablished successfully!',
    'db_version_fail' => 'The database server must run version :version or later. Current version: :current.',
    'db_connect_fail' => 'The connection couldn\'t be stablished. Check them and try again!',

    // tables
    'tables_details' => 'Basic database structure is finally created.<br><br><strong>Caution</strong> - this script will destroy any existing table and their records.',
    'results' => 'Results logs',
    'wipe' => 'Database cleaned',
    'clear_cache' => 'Cache cleared',
    'prepare_config' => 'Configuration ready',
    'install' => 'Base tables ready',
    'create' => 'Game tables ready',
    'insert_changelog' => 'Changelog records inserted',
    'insert_languages' => 'Language records inserted',
    'insert_options' => 'Options records inserted',
    'no_logs' => 'No logs - You need to refresh the page to retry the install.',
    'error_install' => 'Error installing the game! Refresh the page to try again!',
    'success_install' => 'The game has been installed successfully!',

    // admin
    'admin_details' => 'On this step you\'ll be able to create your administrator account.<br><br>Please keep the credentials safe! If possible use a password manager like <a href="https://1password.com/" target="_blank">1Password</a>, <a href="https://www.lastpass.com/" target="_blank">LastPass</a>, or your browser default, and have a super secure password.',
    'admin_email' => 'Administrator email address',
    'admin_email_help' => 'This is used to log in into the admin panel',
    'admin_username' => 'Administrator username',
    'admin_username_help' => 'This is used to show you on the game to other players.',
    'admin_password' => 'Administrator password',
    'admin_password_confirm' => 'Confirm administrator password',
    'random_password' => 'Generate a super secure random password for me',
    'create_admin' => 'Create administrator',
    'admin_create_success' => 'Admin created successfully!',
    'admin_create_fail' => 'The admin couldn\'t be created. Check them and try again!',

    // final
    'final_details' => 'On this step you\'ll be able to do some final checks and further secure your game.<br><br>Let\'s do some clean up!',
    'final_install_not_accessible' => 'NOT accessible install',
    'final_install_not_accessible_ok' => 'OK',
    'final_install_not_accessible_need' => 'Install is accessible',
    'final_config_writable' => 'NOT writable config path',
    'final_config_writable_ok' => 'OK',
    'final_config_writable_need' => ':file is writable',
    'final_fail' => 'You need to mark the game as installed and be sure that the config file is not writable!',
];
