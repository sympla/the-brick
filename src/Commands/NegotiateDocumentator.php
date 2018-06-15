<?php

namespace Sympla\Search\Commands;

use Illuminate\Console\Command;
use Sympla\Search\DocGen\Generator;

class NegotiateDocumentator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'negotiate:docgen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the negotiate documentation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $generator = new Generator();
        $generator->run();
    }
}
