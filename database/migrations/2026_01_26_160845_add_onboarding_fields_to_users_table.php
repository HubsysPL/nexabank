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
        Schema::table('users', function (Blueprint $table) {
            $table->string('hid')->nullable()->after('pesel');
            $table->timestamp('wdo_verified_at')->nullable()->after('hid');
            $table->string('wdo_oauth_state')->nullable()->after('wdo_verified_at');
            $table->string('agreement_id')->nullable()->after('wdo_oauth_state');
            $table->timestamp('agreement_signed_at')->nullable()->after('agreement_id');
            $table->string('onboarding_status')->default('initial')->after('agreement_signed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'hid',
                'wdo_verified_at',
                'wdo_oauth_state',
                'agreement_id',
                'agreement_signed_at',
                'onboarding_status',
            ]);
        });
    }
};
