<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TemporarySong extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'song_path',
        'extension',
        'cover_path',
        'size_kb',
        'duration_sec',
        'title',
        'artist',
        'album',
        'year',
        'comment',
        'composer',
        'copyright_message',
        'publisher',
        'genre',
        'lyrics',
        'track_number',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            $file->token = Str::uuid();
        });
    }

}
