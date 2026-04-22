<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'stripe_checkout_session_id')) {
                $table->string('stripe_checkout_session_id')->nullable()->after('payment_method_id');
            }
            if (! Schema::hasColumn('orders', 'stripe_payment_intent_id')) {
                $table->string('stripe_payment_intent_id')->nullable()->after('stripe_checkout_session_id');
            }
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('card', 'cash_on_delivery', 'stripe_checkout') NOT NULL DEFAULT 'cash_on_delivery'");
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('awaiting_payment', 'placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'placed'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['stripe_checkout_session_id', 'stripe_payment_intent_id']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN payment_method ENUM('card', 'cash_on_delivery') NOT NULL DEFAULT 'cash_on_delivery'");
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled') NOT NULL DEFAULT 'placed'");
        }
    }
};
