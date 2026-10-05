
<?php

/* Base URL */
if ($_SERVER['HTTP_HOST'] == 'localhost') {

    define('BASE_URL', '/recruitment-workflow/');

    // Local Database
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'recruitment_workflow');
    define('DB_USER', 'root');
    define('DB_PASS', '');

} else {

    define('BASE_URL', '/');

    // Live Database
    define('DB_HOST', '68.178.227.144');
    define('DB_NAME', 'recruitment_workflow');
    define('DB_USER', 'recruitment_workflow');
    define('DB_PASS', 'recruitment_workflow');

}

define('APP_NAME', 'Recruitment Workflow System');

define('CHARSET', 'utf8mb4');