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

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'admin',
                'phone' => '',
                'is_active' => true,
            ]
        );

        $this->info("Admin user [{$email}] successfully set!");
        return 0;
    }
}
