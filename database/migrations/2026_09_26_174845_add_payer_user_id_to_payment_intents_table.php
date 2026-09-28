<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_intents', function (Blueprint $table) {
            $table->unsignedBigInteger('payer_user_id')
                ->nullable()
                ->after('reference_id');

            $table->index(
                'payer_user_id',
                'payment_intents_payer_user_id_index'
            );

            $table->foreign('payer_user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_intents', function (Blueprint $table) {
            $table->dropForeign([
                'payer_user_id',
            ]);

            $table->dropIndex(
                'payment_intents_payer_user_id_index'
            );

            $table->dropColumn('payer_user_id');
        });
    }
};
