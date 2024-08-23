<?php

namespace App\Console\Commands;

use App\Jobs\CreateAutobotJob;
use Illuminate\Console\Command;

class AutobotsJob extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:autobots-job';

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
        CreateAutobotJob::dispatch()->onQueue('seeding');
    }
}
