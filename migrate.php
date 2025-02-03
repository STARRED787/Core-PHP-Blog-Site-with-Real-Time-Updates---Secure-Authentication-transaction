<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/database.php';

use Illuminate\Database\Capsule\Manager as DB;

try {
    // Check database connection
    try {
        DB::connection()->getPdo();
        echo "Connected to database: " . DB::connection()->getDatabaseName() . "\n";
    } catch (\Exception $e) {
        die("Could not connect to the database. Please check your configuration. Error: " . $e->getMessage() . "\n");
    }

    // Drop all existing tables if --fresh flag is used
    if (in_array('--fresh', $argv)) {
        echo "Dropping existing tables...\n";
        $tables = ['users', 'blogs'];
        foreach ($tables as $table) {
            if (DB::schema()->hasTable($table)) {
                DB::schema()->drop($table);
                echo "✓ Dropped table: {$table}\n";
            }
        }
    }

    echo "\nStarting migrations...\n";

    // Create users table
    if (!DB::schema()->hasTable('users')) {
        DB::schema()->create('users', function ($table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->string('jwt_token')->nullable();
            $table->timestamps();
        });
        echo "✓ Created users table\n";
    }

    // Create blogs table
    if (!DB::schema()->hasTable('blogs')) {
        DB::schema()->create('blogs', function ($table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
            $table->softDeletes();
        });
        echo "✓ Created blogs table\n";
    }

    // Seed admin user if --seed flag is used
    if (in_array('--seed', $argv)) {
        echo "\nSeeding database...\n";
        // Check if admin user already exists
        if (!DB::table('users')->where('username', 'admin')->exists()) {
            DB::table('users')->insert([
                'username' => 'admin',
                'password' => password_hash('admin123', PASSWORD_BCRYPT),
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            echo "✓ Created admin user (username: admin, password: admin123)\n";
        } else {
            echo "! Admin user already exists\n";
        }
    }

    echo "\n✨ Migration completed successfully!\n";
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 