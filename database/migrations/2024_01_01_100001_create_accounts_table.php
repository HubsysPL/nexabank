<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('product_code');
            $table->char('hrb', 24)->unique();
            $table->char('bank_code', 4);
            $table->char('institution_code', 4);
            $table->char('account_sequence', 12)->unique();
            $table->string('status');
            $table->timestamps();

            $table->foreign('product_code')->references('code')->on('account_products');
        });
    }
};
