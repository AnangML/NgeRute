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
        Schema::table('routes', function (Blueprint $table): void {
            $table->string('headsign', 128)->nullable()->after('name');
        });

        Schema::table('route_segments', function (Blueprint $table): void {
            $table->jsonb('path_coordinates')->nullable()->after('duration_seconds');
        });

        Schema::table('transfers', function (Blueprint $table): void {
            $table->jsonb('path_coordinates')->nullable()->after('source_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table): void {
            $table->dropColumn('headsign');
        });

        Schema::table('route_segments', function (Blueprint $table): void {
            $table->dropColumn('path_coordinates');
        });

        Schema::table('transfers', function (Blueprint $table): void {
            $table->dropColumn('path_coordinates');
        });
    }
};
