<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $brands = [
            ['name' => 'Tealive', 'primary_color' => '#E91E63', 'description' => "Malaysia's largest lifestyle tea brand with 800+ outlets."],
            ['name' => 'Gong Cha', 'primary_color' => '#8B4513', 'description' => 'Premium tea brand from Taiwan specializing in artisan teas.'],
            ['name' => 'Tiger Sugar', 'primary_color' => '#F5A623', 'description' => 'Taiwanese brown sugar boba tea chain.'],
            ['name' => 'Daboba', 'primary_color' => '#FF6B6B', 'description' => 'Popular bubble tea brand across Southeast Asia.'],
            ['name' => 'CoolBlog', 'primary_color' => '#00BCD4', 'description' => 'Malaysian frozen yogurt and smoothie chain.'],
            ['name' => 'Boost Juice', 'primary_color' => '#FF5722', 'description' => 'Australian juice and smoothie bar franchise.'],
            ['name' => 'The Alley', 'primary_color' => '#2C2C2C', 'description' => 'Taiwanese handcrafted tea and bubble tea brand.'],
            ['name' => 'Chatime', 'primary_color' => '#6A1B9A', 'description' => 'Global bubble tea franchise originating from Taiwan.'],
            ['name' => 'Bask Bear Coffee', 'primary_color' => '#8D6E63', 'description' => 'Malaysian specialty coffee chain with affordable pricing.'],
            ['name' => 'Lucky Cup', 'primary_color' => '#FFD700', 'description' => 'Bubble tea and fruit tea chain in Malaysia.'],
            ['name' => 'Luckin Coffee', 'primary_color' => '#003DA5', 'description' => 'Chinese technology-driven coffee chain expanding across Asia.'],
            ['name' => 'Tutti Frutti', 'primary_color' => '#FF69B4', 'description' => 'Self-serve frozen yogurt chain with a variety of toppings.'],
            ['name' => 'Inside Scoop', 'primary_color' => '#F06292', 'description' => 'Malaysian artisan ice cream brand with unique local flavours.'],
            ['name' => 'Llaollao', 'primary_color' => '#7CB342', 'description' => 'Spanish frozen yogurt chain popular in Malaysia.'],
            ['name' => 'Secret Recipe', 'primary_color' => '#B71C1C', 'description' => 'Malaysian lifestyle cafe chain known for cakes and meals.'],
        ];

        foreach ($brands as $brand) {
            $slug = Str::slug($brand['name']);

            if (DB::table('brands')->where('slug', $slug)->exists()) {
                continue;
            }

            DB::table('brands')->insert([
                'name' => $brand['name'],
                'slug' => $slug,
                'primary_color' => $brand['primary_color'],
                'description' => $brand['description'],
                'points_multiplier' => 1.00,
                'rewards_budget' => 0,
                'active' => true,
                'status' => 'approved',
                'user_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugs = [
            'tealive', 'gong-cha', 'tiger-sugar', 'daboba', 'coolblog',
            'boost-juice', 'the-alley', 'chatime', 'bask-bear-coffee',
            'lucky-cup', 'luckin-coffee', 'tutti-frutti', 'inside-scoop',
            'llaollao', 'secret-recipe',
        ];

        DB::table('brands')->whereIn('slug', $slugs)->whereNull('user_id')->delete();
    }
};
