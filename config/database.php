<?php
// Configuración global del sistema (credenciales locales de desarrollo).
// Credenciales del docker-compose.yml: MariaDB gym_mariadb:3306, BD gym_db.
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'gym_db');
define('DB_USER', 'gym_user');
define('DB_PASS', 'gym_pass');
