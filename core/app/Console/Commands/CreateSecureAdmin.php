<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateSecureAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--username= : The admin username}
                            {--email= : The admin email}
                            {--name= : The admin name}
                            {--password= : The admin password (leave empty to generate)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a secure admin user with strong password requirements';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('=== Secure Admin User Creation ===');
        $this->newLine();

        // Get or prompt for username
        $username = $this->option('username') ?: $this->ask('Enter admin username', 'admin');

        // Check if username exists
        if (Admin::where('username', $username)->exists()) {
            $this->error("Username '{$username}' already exists!");
            if (!$this->confirm('Do you want to update this admin instead?')) {
                return Command::FAILURE;
            }
            $admin = Admin::where('username', $username)->first();
            $isUpdate = true;
        } else {
            $admin = new Admin();
            $isUpdate = false;
        }

        // Get or prompt for email
        $email = $this->option('email') ?: $this->ask('Enter admin email', 'admin@yourdomain.com');

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email format!');
            return Command::FAILURE;
        }

        // Get or prompt for name
        $name = $this->option('name') ?: $this->ask('Enter admin name', 'Super Admin');

        // Get or generate password
        $password = $this->option('password');
        $generatedPassword = null;

        if (!$password) {
            if ($this->confirm('Generate a secure random password?', true)) {
                $generatedPassword = $this->generateSecurePassword();
                $password = $generatedPassword;
            } else {
                $password = $this->secret('Enter password (min 8 chars, must include uppercase, lowercase, number, special char)');

                // Validate password strength
                if (!$this->validatePassword($password)) {
                    $this->error('Password does not meet security requirements!');
                    $this->info('Requirements: Min 8 characters, uppercase, lowercase, number, and special character (@$!%*?&)');
                    return Command::FAILURE;
                }
            }
        } else {
            if (!$this->validatePassword($password)) {
                $this->error('Password does not meet security requirements!');
                $this->info('Requirements: Min 8 characters, uppercase, lowercase, number, and special character (@$!%*?&)');
                return Command::FAILURE;
            }
        }

        // Create or update admin
        $admin->name = $name;
        $admin->email = $email;
        $admin->username = $username;
        $admin->password = Hash::make($password);
        $admin->save();

        $this->newLine();
        $this->info('=== Admin User ' . ($isUpdate ? 'Updated' : 'Created') . ' Successfully ===');
        $this->newLine();

        $this->table(
            ['Field', 'Value'],
            [
                ['Username', $username],
                ['Email', $email],
                ['Name', $name],
            ]
        );

        if ($generatedPassword) {
            $this->newLine();
            $this->warn('=== IMPORTANT: Save this password securely! ===');
            $this->info("Generated Password: {$generatedPassword}");
            $this->warn('This password will NOT be shown again!');
        }

        $this->newLine();
        $this->info('Login URL: ' . url('/admin'));

        return Command::SUCCESS;
    }

    /**
     * Generate a secure random password
     */
    private function generateSecurePassword(): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '@$!%*?&';

        // Ensure at least one of each required character type
        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        // Fill remaining with random chars
        $allChars = $uppercase . $lowercase . $numbers . $special;
        for ($i = 4; $i < 16; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Validate password meets security requirements
     */
    private function validatePassword(string $password): bool
    {
        // Min 8 characters
        if (strlen($password) < 8) {
            return false;
        }

        // Must contain uppercase
        if (!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        // Must contain lowercase
        if (!preg_match('/[a-z]/', $password)) {
            return false;
        }

        // Must contain number
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }

        // Must contain special character
        if (!preg_match('/[@$!%*?&]/', $password)) {
            return false;
        }

        return true;
    }
}
