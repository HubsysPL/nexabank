<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('account_id')->constrained('accounts')->onDelete('cascade');
            $table->bigInteger('amount'); // in hubits, always positive
            $table->string('direction'); // debit or credit
            $table->string('status'); // pending, booked, reversed
            $table->string('title', 255);
            $table->string('counterparty_name')->nullable();
            $table->string('counterparty_account')->nullable();
            $table->uuid('reference_id')->nullable()->comment('e.g., transfer_id');
            $table->timestamp('booked_at')->nullable();
            $table->timestamps();
        });
    }
};
