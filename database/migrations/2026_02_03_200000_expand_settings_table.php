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
            // Social (full URLs)
            $table->string('whatsapp', 100)->nullable()->after('twitter');
            $table->string('tiktok', 255)->nullable()->after('whatsapp');
            $table->string('snapchat', 255)->nullable()->after('tiktok');
            $table->string('youtube', 255)->nullable()->after('snapchat');

            // Contact context
            $table->string('support_email', 255)->nullable()->after('email')->comment('Support / help desk email');
            $table->string('support_phone', 50)->nullable()->after('phone')->comment('Support / help desk phone');
            $table->string('store_address', 500)->nullable()->after('address')->comment('Store physical / HQ address for customers');

            // Store operations
            $table->string('store_status', 20)->default('open')->after('copyright_text')->comment('open|closed|maintenance');
            $table->boolean('maintenance_mode')->default(false)->after('store_status');
            $table->text('store_hours')->nullable()->after('maintenance_mode')->comment('e.g. Sat-Thu 9am-10pm');

            // Payment & shipping
            $table->string('currency_code', 10)->default('EGP')->after('store_hours');
            $table->string('currency_symbol', 10)->default('E£')->after('currency_code');
            $table->decimal('tax_rate', 5, 2)->default(0)->after('currency_symbol')->comment('Default tax %');
            $table->text('payment_methods')->nullable()->after('tax_rate')->comment('e.g. card, cash_on_delivery');
            $table->text('shipping_note')->nullable()->after('payment_methods');

            // Localization
            $table->string('locale', 10)->default('en')->after('shipping_note')->comment('Default app locale: en, ar');
            $table->string('timezone', 50)->default('Africa/Cairo')->after('locale');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp', 'tiktok', 'snapchat', 'youtube',
                'support_email', 'support_phone', 'store_address',
                'store_status', 'maintenance_mode', 'store_hours',
                'currency_code', 'currency_symbol', 'tax_rate',
                'payment_methods', 'shipping_note',
                'locale', 'timezone',
            ]);
        });
    }
};
