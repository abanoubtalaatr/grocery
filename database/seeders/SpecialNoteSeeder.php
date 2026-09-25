<?php

namespace Database\Seeders;

use App\Models\SpecialNote;
use Illuminate\Database\Seeder;

class SpecialNoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SpecialNote::insert([
            ['name' => 'Leave order in front of the door'],
            ['name' => "Don't ring the Bell"],
            ['name' => 'Call me 30 minutes in advance'],
        ]);
    }
}
