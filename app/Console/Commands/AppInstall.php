<?php
// app/Console/Commands/AppInstall.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File; // To check for .env
use Illuminate\Support\Str; // To check APP_KEY

class AppInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:install {--force : Force run without confirmation, including destructive actions}'; // Added force option description

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run post-composer install setups: key generation, migrations, seeding, linking, etc.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('Starting application installation...');

        // 1. Check for .env file
        if (! File::exists(base_path('.env'))) {
            $this->warn('.env file not found. Copying .env.example...');
            try {
                File::copy(base_path('.env.example'), base_path('.env'));
                $this->info('.env file created successfully.');
            } catch (\Exception $e) {
                $this->error('Failed to copy .env.example: ' . $e->getMessage());
                $this->error('Please create the .env file manually and configure your database connection.');
                return Command::FAILURE;
            }
        } else {
             $this->info('.env file found.');
        }


        // 2. Generate Application Key (if not already set)
        // Read the APP_KEY from the .env file
        $envContent = File::get(base_path('.env'));
        if (!Str::contains($envContent, 'APP_KEY=') || Str::contains($envContent, 'APP_KEY=SomeRandomString') || Str::contains($envContent, 'APP_KEY=')) {
             $this->warn('Application key not found or is default. Generating key...');
             $this->call('key:generate');
             $this->info('Application key generated successfully!');
        } else {
            $this->info('Application key already exists. Skipping generation.');
        }





        $this->info('Starting Scheduler Application Installation (using Sanctum)...');
        $force = $this->option('force');

        // 5. Run Migrations
        $this->warn('Step 1: Wiping Database...');
        if ($force || App::environment(['local', 'testing']) || $this->confirm('Do you really want to wipe the entire database? This cannot be undone!')) {
            $this->call('db:wipe');
            $this->info('Database wiped successfully!');
        } else {
            $this->error('Database wipe cancelled by user.');
            return 1;
        }

        $this->info('Running database migrations...');
        $this->call('migrate', ['--force' => $this->option('force')]);
        $this->info('Database migrations completed!');


        // 6. Run Seeders (Optional)
        if ($this->confirm('Do you want to run database seeders?', true)) {
             $this->info('Running database seeders...');
             $this->call('db:seed', ['--force' => $this->option('force')]); // Pass force option
             $this->info('Database seeding completed!');
        }



        // 7. Passport Install (Only if using Passport for Auth)
        // If you used `install:api --sanctum` or plan to use Sanctum, REMOVE/COMMENT this.
        // If you genuinely need Passport, keep it.
        /*
        if ($this->confirm('Do you need to install Laravel Passport (for OAuth2 Server)?', false)) {
            $this->info('Running Passport installation...');
            $this->call('passport:install', ['--force' => $this->option('force')]);
            $this->info('Passport installation completed!');
        }
        */

        // 8. Link Storage
        $this->info('Linking storage directory...');
        // Need to handle potential errors if link already exists etc.
        try {
             $this->call('storage:link');
             // Check if link was actually created (exit code 0 doesn't guarantee)
             if (! File::exists(public_path('storage'))) {
                 $this->warn('Storage link might not have been created. Check permissions or run `php artisan storage:link` manually.');
             } else {
                  $this->info('Storage linked successfully.');
             }
        } catch (\Exception $e) {
             $this->error('Could not create storage link: ' . $e->getMessage());
             $this->warn('Please run `php artisan storage:link` manually if needed.');
        }


        // 9. Clear Caches
        $this->info('Clearing caches...');
        $this->call('config:clear'); // Clear config cache first
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('view:clear'); // Good practice to clear view cache too
        $this->info('Application caches cleared.');

        $this->info('---------------------------------');
        $this->info('Application installation complete!');
        $this->info('---------------------------------');

        return Command::SUCCESS; // Use Command constants
    }
}
