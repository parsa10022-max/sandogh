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
        Schema::create('loan_guarantors', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | وام
            |--------------------------------------------------------------------------
            */

            $table->foreignId('loan_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | ترتیب ضامن
            | 1 = ضامن اول
            | 2 = ضامن دوم
            |--------------------------------------------------------------------------
            */

            $table->unsignedTinyInteger('guarantor_order');

            /*
            |--------------------------------------------------------------------------
            | نوع ضامن
            |--------------------------------------------------------------------------
            */

            $table->string('guarantor_type');

            /*
            |--------------------------------------------------------------------------
            | اگر عضو صندوق باشد
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | اطلاعات ضامن غیرعضو
            |--------------------------------------------------------------------------
            */

            $table->string('first_name')
                ->nullable();

            $table->string('last_name')
                ->nullable();

            $table->string('national_code', 10)
                ->nullable();

            $table->string('mobile', 11)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | نوع مدرک ضمانت
            |--------------------------------------------------------------------------
            */

            $table->string('guarantee_type');

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | هر وام فقط یک ضامن اول و یک ضامن دوم دارد
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'loan_id',
                'guarantor_order',
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_guarantors');
    }
};
