<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->string('operation_type')->after('status')->comment('e.g. \'internal_transfer\', \'deposit\'');
            $table->string('counterparty_bank')->nullable()->after('counterparty_account');

            $table->index(['account_id', 'status', 'booked_at']);
            $table->index('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropColumn('operation_type');
            $table->dropColumn('counterparty_bank');

            $table->dropIndex(['account_id', 'status', 'booked_at']);
            $table->dropIndex(['reference_id']);
        });
    }
};