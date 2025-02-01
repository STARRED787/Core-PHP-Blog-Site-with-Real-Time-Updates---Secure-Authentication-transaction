<?php

require_once __DIR__ . '/../config/database.php';
use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::schema()->create('users', function ($table) {
    $table->increments('id');
    $table->string('username')->unique();
    $table->string('password');
    $table->enum('role', ['admin', 'user'])->default('user');
    $table->string('jwt_token')->nullable();
    $table->timestamp('created_at')->useCurrent();
    $table->timestamp('updated_at')->useCurrent()->nullable();
});

echo "Users table created successfully!";
