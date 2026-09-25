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
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('shipping_fee', 10, 2)->default(0)->after('shipping_note')->comment('Default shipping fee for delivery orders');
            $table->decimal('free_shipping_min_order', 10, 2)->nullable()->after('shipping_fee')->comment('Order subtotal above which shipping is free; null = never free');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['shipping_fee', 'free_shipping_min_order']);
        });
    }
};
