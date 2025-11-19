<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nowish = new \DateTime();

        if (Status::firstWhere('name', Status::DEFAULT_NAME) === null ) {
            DB::table('statuses')->insert([
                'name' => Status::DEFAULT_NAME,
                'description' => 'todolist items.',
                'title' => 'Backlog',
                'slug' => 'backlog',
                'order' => 1,
                'user_id' => 1,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (Status::firstWhere('name', Status::READY) === null ) {
            DB::table('statuses')->insert([
                'name' => Status::READY,
                'description' => 'Ready to work.',
                'title' => 'Up Next',
                'slug' => 'up-next',
                'order' => 2,
                'user_id' => 1,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (Status::firstWhere('name', Status::STARTED) === null ) {
            DB::table('statuses')->insert([
                'name' => Status::STARTED,
                'description' => 'Currently being worked on.',
                'title' => 'In Progress',
                'slug' => 'in-progress',
                'order' => 3,
                'user_id' => 1,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }

        if (Status::firstWhere('name', Status::DONE) === null ) {
            DB::table('statuses')->insert([
                'name' => Status::DONE,
                'description' => 'Tasks that are complete.',
                'title' => 'Done',
                'slug' => 'done',
                'order' => 4,
                'user_id' => 1,
                'created_at' => $nowish->format('Y-m-d H:i:s'),
                'updated_at' => $nowish->format('Y-m-d H:i:s')
            ]);
        }
    }
}
