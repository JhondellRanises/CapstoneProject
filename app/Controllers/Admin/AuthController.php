<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('user_role') === 'Admin') {
            return redirect()->to('/admin');
        }

        return view('admin/auth/login', ['title' => 'Admin Sign In']);
    }

    public function attemptLogin()
    {
        $rules = [
            'email' => 'required|valid_email|max_length[190]',
            'password' => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return view('admin/auth/login', [
                'title' => 'Admin Sign In',
                'validation' => $this->validator,
            ]);
        }

        $user = db_connect()->table('users')
            ->where('email', strtolower(trim((string) $this->request->getPost('email'))))
            ->where('role', 'Admin')
            ->where('is_active', 1)
            ->get()->getRowArray();

        if (! $user || ! password_verify((string) $this->request->getPost('password'), $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'The email or password is incorrect.');
        }

        session()->regenerate();
        session()->set([
            'user_id' => $user['id'],
            'user_name' => $user['name'],
            'user_role' => $user['role'],
        ]);

        return redirect()->to('/admin');
    }

    public function forgotPassword()
    {
        return view('admin/auth/forgot_password', ['title' => 'Forgot Password']);
    }

    public function sendResetLink()
    {
        if (! $this->validate(['email' => 'required|valid_email|max_length[190]'])) {
            return view('admin/auth/forgot_password', ['title' => 'Forgot Password', 'validation' => $this->validator]);
        }

        $emailAddress = strtolower(trim((string) $this->request->getPost('email')));
        $db = db_connect();
        $user = $db->table('users')->where('email', $emailAddress)->where('role', 'Admin')->where('is_active', 1)->get()->getRowArray();

        if ($user) {
            $rawToken = bin2hex(random_bytes(32));
            $now = date('Y-m-d H:i:s');
            $db->table('password_resets')->where('user_id', $user['id'])->delete();
            $db->table('password_resets')->insert([
                'user_id' => $user['id'],
                'token_hash' => hash('sha256', $rawToken),
                'expires_at' => date('Y-m-d H:i:s', time() + 1800),
                'created_at' => $now,
            ]);

            $emailConfig = config('Email');
            $mailIsConfigured = $emailConfig->protocol === 'smtp'
                && trim($emailConfig->fromEmail) !== ''
                && trim($emailConfig->SMTPHost) !== ''
                && trim($emailConfig->SMTPUser) !== ''
                && trim($emailConfig->SMTPPass) !== '';

            if ($mailIsConfigured) {
                $mailer = service('email');
                $mailer->clear(true);
                $mailer->setFrom($emailConfig->fromEmail, $emailConfig->fromName ?: 'GPTransco MPC');
                $mailer->setTo($user['email']);
                $mailer->setSubject('Reset your GPTransco Admin password');
                $mailer->setMailType('html');
                $mailer->setMessage(view('admin/auth/password_reset_email', [
                    'resetUrl' => site_url('reset-password/' . $rawToken),
                ]));

                if (! $mailer->send()) {
                    $db->table('password_resets')->where('user_id', $user['id'])->delete();
                    log_message('error', 'Unable to send an admin password reset email. Check the Email configuration.');
                }
            } else {
                $db->table('password_resets')->where('user_id', $user['id'])->delete();
                log_message('warning', 'Admin password reset requested but Email is not configured.');
            }
        }

        return redirect()->to('/forgot-password')->with('message', 'If an active admin account matches that email, a password reset link will be sent.');
    }

    public function resetPassword(string $token)
    {
        if (! $this->findValidReset($token)) {
            return view('admin/auth/reset_password', [
                'title' => 'Reset Password',
                'token' => '',
                'resetError' => 'This reset link is invalid or has expired. Request a new link to continue.',
            ]);
        }

        return view('admin/auth/reset_password', ['title' => 'Reset Password', 'token' => $token]);
    }

    public function saveNewPassword(string $token)
    {
        if (! $this->validate([
            'password' => 'required|min_length[12]|max_length[255]',
            'password_confirm' => 'required|matches[password]',
        ])) {
            return view('admin/auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'validation' => $this->validator,
            ]);
        }

        $reset = $this->findValidReset($token);
        if (! $reset) {
            return view('admin/auth/reset_password', [
                'title' => 'Reset Password',
                'token' => '',
                'resetError' => 'This reset link is invalid or has expired. Request a new link to continue.',
            ]);
        }

        $db = db_connect();
        $db->transStart();
        $db->table('users')->where('id', $reset['user_id'])->where('role', 'Admin')->where('is_active', 1)->update([
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $db->table('password_resets')->where('user_id', $reset['user_id'])->delete();
        $db->transComplete();

        if (! $db->transStatus()) {
            return view('admin/auth/reset_password', [
                'title' => 'Reset Password',
                'token' => $token,
                'resetError' => 'The password could not be updated. Please try again.',
            ]);
        }

        return redirect()->to('/admin/login')->with('message', 'Your password has been updated. Sign in with your new password.');
    }

    private function findValidReset(string $token): ?array
    {
        if (! preg_match('/^[a-f0-9]{64}$/', $token)) {
            return null;
        }

        return db_connect()->table('password_resets r')->select('r.user_id,r.expires_at')
            ->join('users u', 'u.id=r.user_id')
            ->where('r.token_hash', hash('sha256', $token))
            ->where('r.expires_at >=', date('Y-m-d H:i:s'))
            ->where('u.role', 'Admin')->where('u.is_active', 1)
            ->get()->getRowArray() ?: null;
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login')->with('message', 'You have signed out.');
    }
}
