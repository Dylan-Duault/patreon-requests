<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:make-admin
                            {identifier : Email address or user ID}
                            {--patreon-id= : Use Patreon ID instead of email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant admin privileges to a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('identifier');
        $patreonId = $this->option('patreon-id');

        if ($patreonId) {
            $user = User::where('patreon_id', $patreonId)->first();
        } elseif (is_numeric($identifier)) {
            $user = User::find($identifier);
        } else {
            $user = User::where('email', $identifier)->first();
        }

        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if ($user->is_admin) {
            $this->warn("User {$user->email} is already an admin.");

            return self::SUCCESS;
        }

        $user->update(['is_admin' => true]);

        $this->info("User {$user->email} has been granted admin privileges.");

        $this->table(
            ['ID', 'Name', 'Email', 'Patreon ID', 'Is Admin'],
            [[
                $user->id,
                $user->name,
                $user->email,
                $user->patreon_id ?? 'N/A',
                $user->is_admin ? 'Yes' : 'No',
            ]]
        );

        return self::SUCCESS;
    }
}
