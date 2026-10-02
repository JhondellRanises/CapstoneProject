<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group = 'Admin';
    protected $name = 'admin:create';
    protected $description = 'Create or reset the local system administrator account.';

    public function run(array $params)
    {
        $db = db_connect();
        if (! $db->tableExists('users')) {
            CLI::error('Run php spark migrate before creating an administrator.');
            return EXIT_ERROR;
        }

        $email = strtolower(trim((string) CLI::prompt('Admin email', null, 'required')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Enter a valid email address.');
            return EXIT_ERROR;
        }
        $name = trim((string) CLI::prompt('Admin full name', null, 'required'));
        $password = (string) CLI::prompt('Admin password (at least 12 characters)', null, 'required');
        if (strlen($password) < 12) {
            CLI::error('The password must be at least 12 characters.');
            return EXIT_ERROR;
        }

        $existing = $db->table('users')->where('email', $email)->get()->getRowArray();
        $values = [
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'Admin',
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $db->table('users')->where('id', $existing['id'])->update($values);
        } else {
            $values['created_at'] = date('Y-m-d H:i:s');
            $db->table('users')->insert($values);
        }

        CLI::write('Administrator account saved. Sign in at /admin/login.', 'green');
        return EXIT_SUCCESS;
    }
}
