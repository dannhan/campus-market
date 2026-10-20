<?php

namespace App\Console\Commands;

use Database\Seeders\Demo\DemoProductSeeder;
use Database\Seeders\Demo\DemoUserSeeder;
use Database\Seeders\DemoMarketplaceSeeder;
use Illuminate\Console\Command;

class DemoSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create demo marketplace accounts, product listings, and placeholder photos';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $status = $this->call('db:seed', ['--class' => DemoMarketplaceSeeder::class]);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        $this->newLine();
        $this->info(sprintf(
            'Seeded %d demo accounts and %d demo products.',
            count(DemoUserSeeder::USERS),
            count(DemoProductSeeder::PRODUCTS),
        ));
        $this->line('Password for every demo account: '.DemoUserSeeder::PASSWORD);
        $this->table(
            ['Email', 'Name'],
            array_map(fn (array $user) => [$user['email'], $user['name']], DemoUserSeeder::USERS),
        );
        $this->comment('Remove them again with: php artisan demo:clean');

        return self::SUCCESS;
    }
}
