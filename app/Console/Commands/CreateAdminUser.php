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

        $admin = User::where('role', 'admin')->first() ?? User::where('email', $email)->first();

        if ($admin) {
            $admin->update([
                'email' => $email,
                'name' => $name,
                'password' => Hash::make($password),
                'is_active' => true,
            ]);
            $this->info("Admin user [{$email}] credentials updated!");
        } else {
            User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'phone' => '',
                'is_active' => true,
            ]);
            $this->info("Admin user [{$email}] created!");
        }

        return 0;
    }
}
