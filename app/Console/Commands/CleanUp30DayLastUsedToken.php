<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanUp30DayLastUsedToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:clean-up-30-day-last-used-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command used for clean up the token where Last_used > 30 days from now';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedTokens = DB::table('personal_access_tokens')
            ->where('last_used_at', '<', now()->addDays(30))
            ->delete();
        $this->info('Deleted ' . $deletedTokens . ' not using for 30 days up Sanctum tokens.');
    }
}
