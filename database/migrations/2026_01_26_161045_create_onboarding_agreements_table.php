<?php

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
        Schema::create('onboarding_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('content'); // Faktyczna treść wygenerowanej umowy
            $table->text('signed_content')->nullable(); // Treść umowy po podpisaniu
            $table->string('status')->default('generated'); // np. 'generated', 'signed', 'rejected'
            $table->string('signed_by')->nullable(); // np. 'WDO_Profile'
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_agreements');
    }
};
