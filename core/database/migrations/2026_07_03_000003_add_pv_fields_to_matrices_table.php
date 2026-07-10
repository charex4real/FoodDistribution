<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('matrices', function (Blueprint $table) {
            $table->decimal('pv_left', 15, 2)->default(0)
                  ->after('right')
                  ->comment('Cumulative PV accumulated on the left leg');
            $table->decimal('pv_right', 15, 2)->default(0)
                  ->after('pv_left')
                  ->comment('Cumulative PV accumulated on the right leg');
            $table->decimal('pv_left_pairing', 15, 2)->default(0)
                  ->after('pv_right')
                  ->comment('PV flushed/matched on the left leg (used for pairing calc)');
            $table->decimal('pv_right_pairing', 15, 2)->default(0)
                  ->after('pv_left_pairing')
                  ->comment('PV flushed/matched on the right leg (used for pairing calc)');
            $table->enum('position', ['left', 'right', 'root'])->nullable()
                  ->after('pv_right_pairing')
                  ->comment('Position of this node in its parent\'s binary tree');

            $table->index('position');
        });
    }

    public function down(): void
    {
        Schema::table('matrices', function (Blueprint $table) {
            $table->dropIndex(['position']);
            $table->dropColumn(['pv_left', 'pv_right', 'pv_left_pairing', 'pv_right_pairing', 'position']);
        });
    }
};
