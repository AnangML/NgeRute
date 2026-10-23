<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure cube & earthdistance extensions are active for spatial queries
        DB::statement('CREATE EXTENSION IF NOT EXISTS cube');
        DB::statement('CREATE EXTENSION IF NOT EXISTS earthdistance');

        Schema::create('stops', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operator_id')->nullable()->constrained('operators')->nullOnDelete();
            $table->string('code', 32)->nullable()->index();
            $table->string('name', 128)->index();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_type', 32)->default('station'); // 'station', 'stop', 'interchange', 'entrance'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add PostgreSQL GiST spatial index for radius searching
        DB::statement('CREATE INDEX idx_stops_earth_distance ON stops USING gist(ll_to_earth(latitude, longitude))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stops');
    }
};
