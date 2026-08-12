<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pharmcare:admin {email} {password} {name=Administrator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update an admin user account with specified credentials';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = trim($this->argument('email'));
        $password = $this->argument('password');
        $name = trim($this->argument('name'));

        // Check if any admin account exists
        $existingAdmin = User::where('role', 'admin')->first();

        if ($existingAdmin) {
            $this->info("Admin user [{$existingAdmin->email}] already exists. Preserving existing password.");
            return 0;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'phone' => '',
            'is_active' => true,
        ]);

        $this->info("Default Admin user [{$email}] successfully created!");
        return 0;
    }
}
