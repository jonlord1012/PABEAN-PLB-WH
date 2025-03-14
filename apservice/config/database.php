<?php
return [

    'default' => env('DB_CONNECTION_SQLSRV', 'sqlsrv'), // Atur koneksi default

    'connections' => [

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST_SQLSRV', 'localhost'), // Host SQL Server
            'port' => env('DB_PORT_SQLSRV', '1433'),      // Port SQL Server
            'database' => env('DB_DATABASE_SQLSRV', ''),   // Nama database SQL Server
            'username' => env('DB_USERNAME_SQLSRV', ''),   // Username SQL Server
            'password' => env('DB_PASSWORD_SQLSRV', ''),   // Password SQL Server
            'charset' => 'utf8',                           // Set charset, sesuaikan jika perlu
            'prefix' => '',                                // Prefix, jika ada
            'encrypt' => env('DB_ENCRYPT', 'no'),          // Enkripsi, sesuaikan jika perlu
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'oracle' => [
            'driver' => 'oracle',
            'tns' => env('DB_TNS', ''),  // Kosongkan atau sesuaikan jika TNS digunakan
            'host' => env('DB_HOST_ORACLE', 'localhost'),
            'port' => env('DB_PORT_ORACLE', '1521'),
            'database' => env('DB_SERVICE_ORACLE', ''),  // SID atau SERVICE_NAME
            'username' => env('DB_USERNAME_ORACLE', ''),
            'password' => env('DB_PASSWORD_ORACLE', ''),
            'charset' => env('DB_CHARSET_ORACLE', 'AL32UTF8'),  // Disesuaikan dari CodeIgniter
            'prefix' => '',
        ],

    ],

    'migrations' => 'migrations',

];
