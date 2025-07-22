<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'url', 'stars', 'last_updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
