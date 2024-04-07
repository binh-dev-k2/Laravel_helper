<?php

namespace App\Core\Commands;

use App\Core\Utils\FunctionUtil;
use Illuminate\Console\Command;

class MakeFunction extends Command
{
    protected $signature = 'make:base {name} {--a} {--m} {--s} {--r} {--api} {--sub=}';
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
        $subFolder = $options['sub'] ?? '';
        $functionUtil->declareName($name);

        if ($options['m']) {
            $functionUtil->createModel($subFolder);
        }

        if ($options['s']) {
            $functionUtil->createService($subFolder);
        }

        if ($options['r']) {
            $isApi = $options['api'] ? true : false;
            $functionUtil->createRequest($isApi, $subFolder);
        }

        if ($options['a']) {
            $isApi = $options['api'] ? true : false;
            $functionUtil->createController($isApi, $subFolder);
        }

        $this->info('Base created successfully.');
    }
}
