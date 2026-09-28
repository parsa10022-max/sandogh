<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // transaction_no already has a unique index.
    }

    public function down(): void
    {
        // Nothing to rollback.
    }
};
