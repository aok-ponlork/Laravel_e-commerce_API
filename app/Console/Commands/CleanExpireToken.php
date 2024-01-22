<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function Laravel\Prompts\info;

class CleanExpireToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:clean-expire-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the expires token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $deletedTokens = DB::table('personal_access_tokens')
            ->where('expires_at', '<', now())
            ->delete();
        $this->info('Deleted ' . $deletedTokens . ' expired Sanctum tokens.');
    }
}
