<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $samples = [
            ['name' => 'GPTransco Admin', 'email' => 'admin@gmail.com', 'password' => 'admin123', 'role' => 'Admin'],
            ['name' => 'GPTransco Dispatcher', 'email' => 'dispatcher@gmail.com', 'password' => 'dispatcher', 'role' => 'Dispatcher'],
            ['name' => 'Sample Driver', 'email' => 'driver@gmail.com', 'password' => 'Driver', 'role' => 'Driver'],
            ['name' => 'Sample Passenger', 'email' => 'passenger1@gmail.com', 'password' => 'passenger1', 'role' => 'Passenger'],
        ];

        foreach ($samples as $sample) {
            $exists = $this->db->table('users')->where('email', $sample['email'])->countAllResults() > 0;
            if ($exists) {
                continue;
            }

            $this->db->table('users')->insert([
                'name' => $sample['name'],
                'email' => $sample['email'],
                'password_hash' => password_hash($sample['password'], PASSWORD_DEFAULT),
                'role' => $sample['role'],
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
