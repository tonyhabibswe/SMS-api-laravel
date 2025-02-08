<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    protected $signature = 'make:superadmin {email} {username} {password}';
    protected $description = 'Create a user with superadmin role';

    public function handle()
    {
        $email = $this->argument('email');
        $username = $this->argument('username');
        $password = $this->argument('password');

        // Check if the user already exists
        if (User::where('username', $username)->exists()) {
            $this->error('User already exists.');
            return;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists.');
            return;
        }

        // Create the user
        $user = User::create([
            'email' => $email,
            'username' => $username,
            'password' => Hash::make($password),
        ]);

        // Check if the 'superadmin' role exists; if not, create it
        $role = Role::firstOrCreate(['name' => 'superadmin']);

        // Assign the 'superadmin' role to the user
        $user->assignRole($role);

        $this->info('Superadmin user created successfully.');
    }
}