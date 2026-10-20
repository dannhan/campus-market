<?php

namespace App\Console\Commands;

use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\Demo\DemoUserSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DemoCleanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:clean
                            {--dry-run : Report what would be removed without deleting anything}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove demo marketplace accounts, listings, and uploaded images';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $emailPattern = DemoUserSeeder::EMAIL_PREFIX.'%@%';

        $demoUsers = User::where('email', 'like', $emailPattern)->get();
        $demoProducts = Product::whereIn('user_id', $demoUsers->pluck('id'))->get();

        $imagePaths = ProductImage::whereIn('product_id', $demoProducts->pluck('id'))->pluck('path');
        $avatarPaths = $demoUsers->pluck('avatar')->filter()->values();

        $this->table(['Data', 'Count'], [
            ['Demo accounts', $demoUsers->count()],
            ['Demo students', Student::where('university_email', 'like', $emailPattern)->count()],
            ['Demo products', $demoProducts->count()],
            ['Product photos', $imagePaths->count()],
            ['Avatars', $avatarPaths->count()],
        ]);

        if ($this->option('dry-run')) {
            $this->comment('Dry run: nothing was deleted.');

            return self::SUCCESS;
        }

        $userIds = $demoUsers->pluck('id');
        $productIds = $demoProducts->pluck('id');

        DB::transaction(function () use ($userIds, $productIds, $emailPattern) {
            Offer::whereIn('product_id', $productIds)
                ->orWhereIn('user_id', $userIds)
                ->delete();

            ProductImage::whereIn('product_id', $productIds)->delete();
            Product::whereIn('id', $productIds)->delete();
            User::whereIn('id', $userIds)->delete();
            Student::where('university_email', 'like', $emailPattern)->delete();
        });

        $imagePaths
            ->merge($avatarPaths)
            ->each(fn (string $path) => Storage::disk('public')->delete($path));

        // Safety net for files that no longer have a database row.
        Storage::disk('public')->deleteDirectory('products/demo');
        Storage::disk('public')->deleteDirectory('avatars/demo');

        $this->info(sprintf(
            'Removed %d demo accounts, %d products, and %d photos.',
            $demoUsers->count(),
            $demoProducts->count(),
            $imagePaths->count(),
        ));

        return self::SUCCESS;
    }
}
