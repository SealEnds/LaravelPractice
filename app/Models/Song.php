<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    use HasFactory;

    protected $table = 'songs';
    
    protected $fillable = [
        'title',
        'play_count',
        'description'
    ];

    protected $hidden = [
        'file_path'        
    ];

    public function song() {
        return $this->belongsTo('App\Models\Song', 'user_id'); 
    }
}
