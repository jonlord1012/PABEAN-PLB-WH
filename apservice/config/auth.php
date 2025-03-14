<?php

return [
  'defaults' => [
    'guard' => 'api',
    'passwords' => 'users',
  ],

  'guards' => [
    'api' => [
      'driver' => 'jwt',
      'provider' => 'users',
    ],
  ],

  'providers' => [
    'users' => [
      'driver' => 'eloquent',
      'model' => App\Models\User::class, // Ubah ke model user yang digunakan
    ],
  ],

  'passwords' => [
    'users' => [
      'provider' => 'users',
      'table' => 'password_resets',
      'expire' => 60,
    ],
  ],
];
