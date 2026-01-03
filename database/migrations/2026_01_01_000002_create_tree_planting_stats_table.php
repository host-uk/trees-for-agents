<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tree_planting_stats', function (Blueprint $table) {
            $table->id();

            // Agent identification
            $table->string('provider', 50)->index();
            $table->string('model', 100)->nullable()->index();

            // Aggregated stats
            $table->unsignedBigInteger('total_trees')->default(0);
            $table->unsignedBigInteger('total_referrals')->default(0);
            $table->unsignedBigInteger('total_signups')->default(0);

            $table->timestamps();

            // Unique constraint for upserts
            $table->unique(['provider', 'model']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_planting_stats');
    }
};
