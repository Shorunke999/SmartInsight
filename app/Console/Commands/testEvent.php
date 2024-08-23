<?php

namespace App\Console\Commands;

use App\Events\BroadcastUserCountEvent;
use Illuminate\Console\Command;

class testEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-event';

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
        BroadcastUserCountEvent::dispatch(3);
    }
}
