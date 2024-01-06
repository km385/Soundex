<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //users:
        $this->call(TestUsersSeeder::class);
        $this->command->info('Seeding users data completed.');
        //TODO fix bug
        //songs
        //$this->call(TestSongsSeeder::class);
        //$this->command->info('Seeding songs data completed.');
        //TODO
        //playlists
        //$this->call(TestPlaylistsSeeder::class);
        //$this->command->info('Seeding playlists data complete.');
        //TODO
        //testsongs
        //$this->call(TestPlaylistsSeeder::class);
        //$this->command->info('Seeding playlists data complete.');
    }
}
