<?php

namespace App\Core\Commands;

use App\Core\Utils\FunctionUtil;
use Illuminate\Console\Command;

class MakeFunction extends Command
{
    protected $signature = 'make:base {name} {--a} {--m} {--s} {--r} {--api}';
    protected $description = 'Create a new base with predefined methods in Core/Commands folder';
    protected $name;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(FunctionUtil $functionUtil)
    {
        $name = ucfirst($this->argument('name'));
        $options = $this->options();
        $functionUtil->declareName($name);

        if ($options['m']) {
            $functionUtil->createModel();
        }

        if ($options['s']) {
            $functionUtil->createModel();
        }

        if ($options['r']) {
            if ($options['api']) {
                $functionUtil->createRequest($api = true);
            } else {
                $functionUtil->createRequest($api = false);
            }
        }

        if ($options['a']) {
            if ($options['api']) {
                $functionUtil->createController($api = true);
            } else {
                $functionUtil->createController($api = false);
            }
        }

        $this->info('Base created successfully.');
    }
}
