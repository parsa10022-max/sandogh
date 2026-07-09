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
        Schema::create('user_otps', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('mobile', 11);

            $table->string('code', 255);

            $table->string('type', 30);

            $table->string('status', 20);

            $table->timestamp('expires_at');

            $table->timestamp('verified_at')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);

            $table->string('user_agent', 500)->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // ---------- Index ----------


            $table->index('mobile');

            $table->index('status');

            $table->index('type');

            $table->index('expires_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
