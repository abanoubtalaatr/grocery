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
        Schema::create('user_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Order & Delivery Updates
            $table->boolean('order_confirmation')->default(true);
            $table->boolean('order_shipped')->default(true);
            $table->boolean('delivery_updates')->default(true);
            $table->boolean('out_of_stock_alerts')->default(true);
            
            // Deals & Promotions
            $table->boolean('weekly_discounts')->default(true);
            $table->boolean('exclusive_member_offers')->default(true);
            $table->boolean('seasonal_campaigns')->default(true);
            
            // Account & Reminders
            $table->boolean('cart_reminders')->default(true);
            $table->boolean('payment_billing')->default(true);
            
            // Channels
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_settings');
    }
};
