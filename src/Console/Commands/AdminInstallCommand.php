<?php

namespace Despark\Cms\Console\Commands;

use Illuminate\Console\Command;

class AdminInstallCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'admin:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs the application by setting up all the necessary resources.';

    /**
     * Create a new command instance.
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
    public function fire()
    {
        if (env('CACHE_DRIVER') !== 'array') {
            $path = base_path('.env');
            if (file_exists($path)) {
                file_put_contents($path, str_replace(
                    'CACHE_DRIVER='.env('CACHE_DRIVER'),
                    'CACHE_DRIVER=array',
                    file_get_contents($path)
                ));
            }
        }

        // Generate the Application Encryption key
        $this->call('key:generate');

        // Publish the packages.
        $this->call('vendor:publish', [
            '--force' => true,
            '--provider' => 'Despark\Cms\Providers\AdminServiceProvider',
        ]);

        // Run the Migrations
        $this->call('migrate');

        exec('composer dumpautoload');

        // Seed the tables with dummy data
        $this->call('db:seed', [
            '--class' => 'DesparkDatabaseSeeder',
        ]);

        $this->info('npm install..');
        exec('npm install --silent');
        $this->progressBar(9);

        $this->info('bower install..');
        exec('bower install');
        $this->progressBar(3);

        $this->info('gulp dev..');
        exec('gulp dev');
        $this->progressBar(1);
    }

    private function progressBar($time)
    {
        $this->output->progressStart(10);

        for ($i = 0; $i < 10; ++$i) {
            sleep($time);

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }
}
