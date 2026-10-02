<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LinkExistingRoleProfilesSeeder extends Seeder
{
    public function run()
    {
        foreach ($this->db->table('users')->whereIn('role', ['Driver', 'Passenger'])->get()->getResultArray() as $user) {
            $table = $user['role'] === 'Driver' ? 'drivers' : 'passengers';
            $exists = $this->db->table($table)->where('user_id', $user['id'])->countAllResults() > 0;
            if ($exists) {
                continue;
            }

            $profile = [
                'user_id' => $user['id'],
                'contact_number' => null,
                'is_active' => $user['is_active'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            if ($table === 'drivers') {
                $profile['license_number'] = null;
            }
            $this->db->table($table)->insert($profile);
        }
    }
}
