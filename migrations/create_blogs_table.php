<?php
/**
 * Blog Table Migration
 * Creates the database structure for storing blog posts
 * Includes timestamps and soft deletes functionality
 */

// Import required Illuminate components
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

/**
 * Create blogs table if it doesn't exist
 * Schema includes:
 * - Auto-incrementing ID
 * - Title and content fields
 * - Timestamps for creation/updates
 * - Soft deletes for data preservation
 */
Capsule::schema()->create('blogs', function (Blueprint $table) {
    // Primary key
    $table->id();
    
    // Blog content fields
    $table->string('title');      // Blog post title
    $table->text('content');      // Blog post content
    
    // Metadata fields
    $table->timestamps();         // created_at and updated_at
    $table->softDeletes();       // deleted_at for soft deletes
});


echo "Users table created successfully!";
