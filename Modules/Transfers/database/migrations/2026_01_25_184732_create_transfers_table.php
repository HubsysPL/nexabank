<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Enums\TransferStatus;
use Modules\Core\Enums\TransferType; // Dodano nowy Enum

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->uuid('from_account_id')->nullable();
            $table->uuid('to_account_id')->nullable();

            // Pełny numer HRB konta źródłowego (dla przejrzystości i weryfikacji)
            $table->string('from_account_hrb', 24);
            // Pełny numer HRB konta docelowego (dla przejrzystości i weryfikacji)
            $table->string('to_account_hrb', 24);

            $table->bigInteger('amount'); // Kwota w hubitach
            $table->string('title'); // Tytuł przelewu
            $table->enum('status', array_map(fn($case) => $case->value, TransferStatus::cases()))->default(TransferStatus::PENDING->value);
            $table->enum('type', array_map(fn($case) => $case->value, TransferType::cases())); // Typ przelewu

            $table->timestamps();

            // Indeksy dla wydajności
            $table->index('from_account_id');
            $table->index('to_account_id');
            $table->index('from_account_hrb');
            $table->index('to_account_hrb');
            $table->index('status');
            $table->index('type');

            // Klucze obce (z uwzględnieniem nullable)
            $table->foreign('from_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('to_account_id')->references('id')->on('accounts')->onDelete('restrict');
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
