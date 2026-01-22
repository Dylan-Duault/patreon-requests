<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GrantMonthlyCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:grant-monthly
                            {--user= : Specific user ID to grant credits to}
                            {--dry-run : Show what would be done without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant monthly credits to active patrons who have not received them yet';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->option('user');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('DRY RUN MODE - No changes will be made');
        }

        if ($userId) {
            $users = User::where('id', $userId)
                ->where('patron_status', 'active_patron')
                ->where('patron_tier_cents', '>', 0)
                ->get();

            if ($users->isEmpty()) {
                $this->error("User {$userId} not found or is not an active patron.");

                return self::FAILURE;
            }
        } else {
            $users = User::where('patron_status', 'active_patron')
                ->where('patron_tier_cents', '>', 0)
                ->get();
        }

        $this->info("Processing {$users->count()} active patrons...");

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $granted = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($users as $user) {
            try {
                if ($user->hasReceivedMonthlyCredits()) {
                    $skipped++;
                    $this->line(" Skipped: {$user->email} (already received credits this month)");
                    $bar->advance();

                    continue;
                }

                $credits = $user->getMonthlyRequestLimit();

                if ($credits <= 0) {
                    $skipped++;
                    $this->line(" Skipped: {$user->email} (0 credits for tier)");
                    $bar->advance();

                    continue;
                }

                if ($dryRun) {
                    $granted++;
                    $this->line(" Would grant: {$user->email} - {$credits} credits");
                } else {
                    $transaction = $user->grantMonthlyCredits();

                    if ($transaction) {
                        $granted++;
                        $this->line(" Granted: {$user->email} - {$credits} credits");
                    } else {
                        $skipped++;
                        $this->line(" Skipped: {$user->email} (already granted or ineligible)");
                    }
                }
            } catch (\Exception $e) {
                $failed++;
                Log::error('Failed to grant monthly credits', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error(" Error for {$user->email}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $action = $dryRun ? 'Would grant to' : 'Granted to';
        $this->info("Completed: {$action} {$granted}, Skipped {$skipped}, Failed {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
