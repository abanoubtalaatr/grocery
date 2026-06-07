<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('app_language', 5)->default('en')->after('preferred_languages');
            $table->string('app_theme', 10)->default('light')->after('app_language');
        });

        Schema::table('user_notification_settings', function (Blueprint $table) {
            $table->boolean('order_updates')->default(true)->after('sms_notifications');
            $table->boolean('promotion_emails')->default(true)->after('order_updates');
            $table->boolean('nutrition_insights')->default(true)->after('promotion_emails');
            $table->boolean('price_alerts')->default(true)->after('nutrition_insights');
        });
    }

    public function down(): void
    {
        Schema::table('user_notification_settings', function (Blueprint $table) {
            $table->dropColumn(['order_updates', 'promotion_emails', 'nutrition_insights', 'price_alerts']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['app_language', 'app_theme']);
        });
    }
};
