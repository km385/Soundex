<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Song extends Model
{
    use HasFactory;
    protected $fillable = [
        'extension',
        'duration_sec',
        'user_id',
        'disk',
        'song_path',
        'size_kb',
        'cover_path',
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
    protected $hidden = ['user_id','disk','song_path','cover_path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
