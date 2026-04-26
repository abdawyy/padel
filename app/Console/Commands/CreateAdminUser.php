<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-admin-user
        {name : The display name for the admin user}
        {email : The email address for the admin user}
        {--password= : The password to assign}
        {--role=admin : The system role (super_admin, admin, manager, coach)}
        {--phone= : Optional phone number}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or update an admin/coaching account that can access the Filament panel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $role = (string) $this->option('role');
        $allowedRoles = ['super_admin', 'admin', 'manager', 'coach'];

        if (! in_array($role, $allowedRoles, true)) {
            $this->error('Invalid role. Allowed roles: '.implode(', ', $allowedRoles));

            return self::FAILURE;
        }

        $generatedPassword = false;
        $password = (string) ($this->option('password') ?: '');

        if ($password === '') {
            $password = Str::password(12);
            $generatedPassword = true;
        }

        $user = User::query()->updateOrCreate(
            ['email' => (string) $this->argument('email')],
            [
                'name' => (string) $this->argument('name'),
                'phone' => $this->option('phone') ?: null,
                'password' => $password,
                'role' => $role,
                'is_active' => true,
            ]
        );

        $this->info('Admin account is ready.');
        $this->line('Name: '.$user->name);
        $this->line('Email: '.$user->email);
        $this->line('Role: '.$user->role);
        $this->line('Password: '.$password);

        if ($generatedPassword) {
            $this->warn('Save this generated password now.');
        }

        return self::SUCCESS;
    }
}
