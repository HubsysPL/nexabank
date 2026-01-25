<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Enums\ClearingBatchStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clearing_batches', function (Blueprint $table) {
            $table->id();
            $table->enum('status', array_map(fn($case) => $case->value, ClearingBatchStatus::cases()))->default(ClearingBatchStatus::PENDING->value);
            $table->integer('processed_transfers_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clearing_batches');
    }
};
