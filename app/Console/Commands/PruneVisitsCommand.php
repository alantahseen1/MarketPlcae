<?php

namespace App\Console\Commands;

use App\Services\VisitStatsService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('visits:prune {--days=400 : The number of days after which visit records are deleted}')]
#[Description('Prune visitor statistics records older than 400 days')]
class PruneVisitsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(VisitStatsService $visitStatsService): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 400;
        }

        $deleted = $visitStatsService->pruneOldVisits($days);

        $this->info("Pruned {$deleted} visitor records older than {$days} days.");

        return self::SUCCESS;
    }
}
