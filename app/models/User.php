<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';   // Table name
    protected $fillable = ['username', 'password', 'role', 'jwt_token']; // Fillable columns

    // Hide these fields when converting to JSON/array
    protected $hidden = [
        'password',
        'jwt_token'
    ];

    // Define relationships if needed
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    // Custom method to verify password
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }
}
