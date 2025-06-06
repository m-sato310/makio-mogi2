<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            ['name' => '佐藤太郎', 'email' => 'taro@example.com'],
            ['name' => '佐々木花子', 'email' => 'hanako@example.com'],
            ['name' => '高橋健一', 'email' => 'kenichi@example.com'],
            ['name' => '田中美咲', 'email' => 'misaki@example.com'],
            ['name' => '山本勝太郎', 'email' => 'shotaro@example.com'],
        ];

        foreach ($users as $u) {
            User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('testpass'),
                'is_admin' => false,
            ]);
        }

        User::create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'password' => Hash::make('testpass'),
            'is_admin' => true,
        ]);
    }
}
