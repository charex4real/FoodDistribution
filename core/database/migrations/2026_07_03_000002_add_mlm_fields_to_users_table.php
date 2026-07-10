<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Project membership
            $table->foreignId('project_id')->nullable()->default(null)
                  ->constrained('projects')->nullOnDelete()
                  ->after('id');

            // Wallet
            $table->decimal('product_wallet', 15, 2)->default(0)
                  ->after('balance')
                  ->comment('Wallet credited from product/project earnings');

            // Bonus accumulators (lifetime totals for reporting)
            $table->decimal('direct_bonus', 15, 2)->default(0)->after('product_wallet');
            $table->decimal('indirect_bonus', 15, 2)->default(0)->after('direct_bonus');
            $table->decimal('upgrade_bonus', 15, 2)->default(0)->after('indirect_bonus');
            $table->decimal('unilevel_bonus', 15, 2)->default(0)->after('upgrade_bonus');
            $table->decimal('pairing_bonus', 15, 2)->default(0)->after('unilevel_bonus');

            // Monthly purchase tracking
            $table->timestamp('last_monthly_purchase_at')->nullable()->after('pairing_bonus');
            $table->json('monthly_purchase_history')->nullable()->after('last_monthly_purchase_at')
                  ->comment('JSON array of {month: "YYYY-MM", amount: n, project_id: n}');

            // Binary tree placement check flag
            $table->tinyInteger('pcheck')->default(0)->after('monthly_purchase_history')
                  ->comment('0=not checked, 1=left placed, 2=right placed, 3=both legs active');

            $table->index('project_id');
            $table->index('pcheck');
            $table->index('last_monthly_purchase_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['pcheck']);
            $table->dropIndex(['last_monthly_purchase_at']);
            $table->dropColumn([
                'project_id', 'product_wallet', 'direct_bonus', 'indirect_bonus',
                'upgrade_bonus', 'unilevel_bonus', 'pairing_bonus',
                'last_monthly_purchase_at', 'monthly_purchase_history', 'pcheck',
            ]);
        });
    }
};
