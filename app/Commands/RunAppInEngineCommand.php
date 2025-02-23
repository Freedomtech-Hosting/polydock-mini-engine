<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\Engine;

use FreedomtechHosting\PolydockAppAmazeeioGeneric\PolydockAppAmazeeioGeneric;
use Symfony\Component\Yaml\Yaml;
use App\PolydockMiniEngineLogger;

class RunAppInEngineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'engine:run-app {app-yml-file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run an app in the mini-engine';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $logger = new PolydockMiniEngineLogger($this);
        $engine = new Engine($logger);
        $appYmlFile = $this->argument('app-yml-file');
        $this->info('Running app in the mini-engine');
        $this->info('App yml file: ' . $appYmlFile);

        if (!file_exists($appYmlFile)) {
            $this->error('App yml file does not exist');
            return;
        }

        $appConfig = Yaml::parseFile($appYmlFile);
        
        if (!isset($appConfig['class'], $appConfig['name'], $appConfig['description'], $appConfig['author'], $appConfig['website'], $appConfig['support-email'])) {
            $missing = [];
            foreach(['class', 'name', 'description', 'author', 'website', 'support-email'] as $field) {
                if (!isset($appConfig[$field])) {
                    $missing[] = $field;
                }
            }
            throw new \Exception('Missing required app configuration fields: ' . implode(', ', $missing));
        }
        
        $appClass = $appConfig['class'];
        $appName = $appConfig['name'];
        $appDescription = $appConfig['description'];
        $appAuthor = $appConfig['author'];
        $appWebsite = $appConfig['website'];
        $appSupportEmail = $appConfig['support-email'];
        $appConfiguration = $appConfig['configuration'] ?? [];

        if(!class_exists($appClass)) {
            throw new \Exception('App class does not exist');
        }

        try {
            $engine->runFullTest(
                $appClass,
                $appName,
                $appDescription,
                $appAuthor,
                $appWebsite,
                $appSupportEmail,
                $appConfiguration
            );
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
