<?php

namespace App\Console\Commands;

use App\Models\VerificationCodes;
use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class DeleteVerificationCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-verification-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(Schedule $schedule)
    {
        VerificationCodes::where('created_at', '<', \Carbon\Carbon::now())->delete();
    }
}
