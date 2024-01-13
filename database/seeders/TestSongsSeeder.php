<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSongsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // To run only this seeder, use the command:
        // php artisan db:seed --class=TestSongsSeeder

        // Loop creating 15 random songs with names from 'test (1)' to 'test (15)'.
        // Files must be provided manually.
        // Mass renaming in Windows automatically names files in this format.
        // By default, all songs should belong to the Test user (id = 1).

        $songTitles = [
            'Guitar Melody',
            'Rainy Days',
            'Dancing in the Moonlight',
            'Love Song',
            'The Sound of Silence',
            'Magic Moments',
            'Morning Sunshine',
            'Lost in the Music',
            'City Lights',
            'Summer Breeze',
            'In My Dreams',
            'Sunset Serenade',
            'A very long name for testing if very long names show properly, it is in fact a very long name when you consider average length of song titles',
        ];

        $metadataSets = [
            [
                'artist' => "Queen",
                'album' => "A Night at the Opera",
                'year' => "1975-01-01 00:00:00",
                'comment' => "Classic rock masterpiece",
                'composer' => "Freddie Mercury",
                'copyright_message' => "© 1975 Queen Productions Ltd.",
                'publisher' => "EMI Music Publishing",
                'genre' => "Rock",
                'lyrics' => "Is this the real life? Is this just fantasy?",
                'track_number' => 1,
            ],
            [
                'artist' => "Ed Sheeran",
                'album' => "÷ (Divide)",
                'year' => "2017-01-01 00:00:00",
                'comment' => "Chart-topping hit",
                'composer' => "Ed Sheeran",
                'copyright_message' => "© 2017 Asylum Records UK, a division of Atlantic Records UK. A Warner Music Group Company",
                'publisher' => "Sony/ATV Music Publishing",
                'genre' => "Pop",
                'lyrics' => "The club isn't the best place to find a lover",
                'track_number' => 2,
            ],
            [
                'artist' => "Michael Jackson",
                'album' => "Thriller",
                'year' => "1982-01-01 00:00:00",
                'comment' => "Iconic dance track",
                'composer' => "Michael Jackson",
                'copyright_message' => "© 1982 Mijac Music (BMI)",
                'publisher' => "Warner Chappell Music",
                'genre' => "Pop",
                'lyrics' => "She says I am the one, but the kid is not my son",
                'track_number' => 3,
            ],
            [
                'artist' => "Eagles",
                'album' => "Hotel California",
                'year' => "1976-01-01 00:00:00",
                'comment' => "Timeless classic",
                'composer' => "Don Felder, Don Henley, Glenn Frey",
                'copyright_message' => "© 1976 Cass County Music (ASCAP), Don Henley Songs (ASCAP), Fingers Music (ASCAP), Wisteria Music (ASCAP)",
                'publisher' => "Eagles Music",
                'genre' => "Rock",
                'lyrics' => "Welcome to the Hotel California, such a lovely place",
                'track_number' => 4,
            ],
        ];

        $songStatus = ['banned', 'published', 'waiting'];

        for ($i = 1; $i <= 15; $i++) {
            $songName = 'test (' . $i . ')';

            DB::table('songs')->insert([
                'name' => $songName,
                'duration_sec' => rand(15, 500),
                'user_id' => 1,
                'disk' => '',
                'song_path' => $songName . '.mp3',
                'cover_path' => $songName . '.jpg',

                'created_at' => now(),
                'updated_at' => now(),

                'title' => $songTitles[array_rand($songTitles)],
                'artist' => $metadataSets[array_rand($metadataSets)]['artist'],
                'album' => $metadataSets[array_rand($metadataSets)]['album'],
                'year' => $metadataSets[array_rand($metadataSets)]['year'],
                'comment' => $metadataSets[array_rand($metadataSets)]['comment'],
                'composer' => $metadataSets[array_rand($metadataSets)]['composer'],
                'copyright_message' => $metadataSets[array_rand($metadataSets)]['copyright_message'],
                'publisher' => $metadataSets[array_rand($metadataSets)]['publisher'],
                'genre' => $metadataSets[array_rand($metadataSets)]['genre'],
                'lyrics' => $metadataSets[array_rand($metadataSets)]['lyrics'],
                'track_number' => $metadataSets[array_rand($metadataSets)]['track_number'],
            ]);
        }






    }
}
