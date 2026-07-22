<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('act_users', 'acb_users');
    }

    public function down(): void
    {
        Schema::rename('acb_users', 'act_users');
    }
};
