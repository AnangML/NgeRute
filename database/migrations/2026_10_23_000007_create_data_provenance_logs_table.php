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
        Schema::create('data_provenance_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('dataset_name', 128);
            $table->string('operator_code', 32);
            $table->unsignedInteger('records_count');
            $table->string('status', 32); // 'success', 'warning', 'failed'
            $table->string('verified_by', 64);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_provenance_logs');
    }
};
