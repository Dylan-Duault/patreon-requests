<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PatreonService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshPatreonMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'patreon:refresh-memberships
                            {--user= : Specific user ID to refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Patreon membership status for all users';

    public function __construct(
        protected PatreonService $patreonService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user');

        if ($userId) {
            $users = User::whereNotNull('patreon_id')
                ->where('id', $userId)
                ->get();

            if ($users->isEmpty()) {
                $this->error("User {$userId} not found or has no Patreon ID.");

                return self::FAILURE;
            }
        } else {
            $users = User::whereNotNull('patreon_id')
                ->whereNotNull('patreon_access_token')
                ->get();
        }

        $this->info("Refreshing membership status for {$users->count()} users...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $updated = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                $result = $this->patreonService->updateUserMembership($user);

                if ($result) {
                    $updated++;
                    $tierAmount = $user->patron_tier_cents / 100;
                    $this->line(" Updated: {$user->email} - Status: {$user->patron_status}, Tier: \${$tierAmount}");
                } else {
                    $failed++;
                    $this->warn(" Failed to update: {$user->email}");
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to refresh Patreon membership', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error(" Error for {$user->email}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Completed: {$updated} updated, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
