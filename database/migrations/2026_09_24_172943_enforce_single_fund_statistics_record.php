<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_statistics', function (Blueprint $table) {
            $table->unsignedTinyInteger('singleton')
                ->default(1)
                ->unique()
                ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('fund_statistics', function (Blueprint $table) {
            $table->dropUnique(['singleton']);
            $table->dropColumn('singleton');
        });
    }
};
