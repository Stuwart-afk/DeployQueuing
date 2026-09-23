<?php

namespace Database\Seeders;

// use App\Models\QueueTicket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        DB::table('queue_tickets')->insert([
            'name' => 'Test User',
            'tracking_number' => 'A002',
            'device_id' => '550e8400-e29b-41d4-a716-446655440000',
            'mobile_number' => '0919238100',
            'status' => 'holding',
            'assigned_teller' => 'A',
        ]);
    }
}
