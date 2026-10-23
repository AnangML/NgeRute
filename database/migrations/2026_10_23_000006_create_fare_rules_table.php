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
        Schema::create('fare_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operator_id')->constrained('operators')->cascadeOnDelete();
            $table->string('fare_scheme', 32); // 'flat', 'distance_tier', 'od_matrix'
            $table->decimal('base_fare', 10, 2)->default(0.00);
            $table->decimal('base_distance_km', 6, 2)->nullable();
            $table->decimal('fare_per_next_km', 10, 2)->nullable();
            $table->decimal('max_fare', 10, 2)->nullable();
            $table->jsonb('matrix_payload')->nullable(); // PostgreSQL JSONB for OD fare matrix
            $table->date('valid_from');
            $table->string('source_url')->nullable();
            $table->timestamps();

            $table->index('operator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fare_rules');
    }
};
