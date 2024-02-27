<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\ResetPassword;
class DeleteResetPasswordCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-reset-password-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        ResetPassword::where('created_at', '<', \Carbon\Carbon::now())->delete();
    }
}
