<?php

class Database
{
    private static $pdo = null;

    public static function connect()
    {
        if (self::$pdo === null) {

            // Load database configuration
            require_once __DIR__ . '/config.php';

            $dsn = "mysql:host=" . DB_HOST .
                   ";dbname=" . DB_NAME .
                   ";charset=" . CHARSET;

            self::$pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        }

        return self::$pdo;
    }
}