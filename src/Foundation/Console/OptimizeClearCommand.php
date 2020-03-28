<?php

namespace TarBlog\Foundation\Console;

use Illuminate\Console\Command;

class OptimizeClearCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'optimize:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the cached bootstrap files';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->call('route:clear');
        $this->call('config:clear');

        $this->info('Caches cleared successfully!');
    }
}
