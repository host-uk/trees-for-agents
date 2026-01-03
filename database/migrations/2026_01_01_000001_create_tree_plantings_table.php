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
        Schema::create('tree_plantings', function (Blueprint $table) {
            $table->id();

            // Agent identification
            $table->string('provider', 50)->index();
            $table->string('model', 100)->nullable()->index();

            // User association (optional)
            $table->string('user_id')->nullable()->index();

            // Planting details
            $table->unsignedInteger('trees')->default(1);
            $table->string('source', 50)->default('referral'); // referral, webhook, manual
            $table->string('event', 50)->nullable(); // subscriber.confirmed, etc.
            $table->string('status', 20)->default('queued'); // queued, confirmed, planted, failed

            // Metadata
            $table->json('metadata')->nullable();

            // Timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('planted_at')->nullable();
            $table->timestamps();

            // Indexes for leaderboard queries
            $table->index(['provider', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree_plantings');
    }
};
