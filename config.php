<?php
/**
 * DSS Recruitment - Database configuration
 * Copy to config.local.php and set your credentials (config.local.php can be gitignored).
 */
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
    return;
}

// Defaults for local XAMPP (override in config.local.php for production)
define('DSS_DB_HOST', 'localhost');
define('DSS_DB_NAME', 'dss_volunteers');
define('DSS_DB_USER', 'root');
define('DSS_DB_PASS', '');
define('DSS_DB_CHARSET', 'utf8mb4');
