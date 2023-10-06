<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'duration_sec',
        'user_id',
        'disk',
        'song_path',
        'cover_path',
        'song_status',
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

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['name','user_id','disk','song_path','cover_path','song_status'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}