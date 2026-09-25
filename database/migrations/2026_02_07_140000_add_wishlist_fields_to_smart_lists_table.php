<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_lists', function (Blueprint $table) {
            $table->string('category', 100)->nullable()->after('name')->comment('User-defined category e.g. Groceries, Snacks');
            $table->boolean('notify_on_price_drop')->default(true)->after('image')->comment('Notify when a listed item price drops');
            $table->boolean('notify_on_offers')->default(true)->after('notify_on_price_drop')->comment('Notify when a listed item has a limited-time offer');
        });
    }

    public function down(): void
    {
        Schema::table('smart_lists', function (Blueprint $table) {
            $table->dropColumn(['category', 'notify_on_price_drop', 'notify_on_offers']);
        });
    }
};
