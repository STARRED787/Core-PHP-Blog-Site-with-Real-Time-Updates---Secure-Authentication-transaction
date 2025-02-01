<?php

require_once __DIR__ . '/../vendor/autoload.php';
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blogs';   // Table name
    protected $fillable = [
        'title',
        'content'
    ]; // Fillable columns

    // Define relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
