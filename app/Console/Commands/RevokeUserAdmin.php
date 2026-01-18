<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RevokeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:revoke-admin
                            {identifier : Email address or user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove admin privileges from a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $identifier = $this->argument('identifier');

        if (is_numeric($identifier)) {
            $user = User::find($identifier);
        } else {
            $user = User::where('email', $identifier)->first();
        }

        if (! $user) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if (! $user->is_admin) {
            $this->warn("User {$user->email} is not an admin.");

            return self::SUCCESS;
        }

        $user->update(['is_admin' => false]);

        $this->info("Admin privileges revoked from {$user->email}.");

        return self::SUCCESS;
    }
}
