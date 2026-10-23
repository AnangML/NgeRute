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
        Schema::create('transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('from_stop_id')->constrained('stops')->cascadeOnDelete();
            $table->foreignId('to_stop_id')->constrained('stops')->cascadeOnDelete();
            $table->unsignedInteger('walking_distance_meters');
            $table->unsignedInteger('walking_duration_seconds');
            $table->string('transfer_type', 32); // 'in_station', 'bridge_jpo', 'street_walk', 'underground'
            $table->string('source_notes')->nullable();
            $table->date('verified_at');
            $table->timestamps();

            $table->index(['from_stop_id', 'to_stop_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
