<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add new timestamp columns
            $table->timestamp('placed_at')->nullable()->after('notes');
            $table->timestamp('processing_at')->nullable()->after('placed_at');
            $table->timestamp('shipping_at')->nullable()->after('processing_at');
            // Add estimated_delivery_time column
            $table->timestamp('estimated_delivery_time')->nullable()->after('delivered_at');
        });

        // Migrate data from old columns to new columns
        DB::table('orders')
            ->whereNotNull('confirmed_at')
            ->update([
                'placed_at' => DB::raw('confirmed_at'),
                'processing_at' => DB::raw('preparing_at'),
            ]);

        // Update status enum - we need to use raw SQL for enum changes
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'placed'");
        
        // Map old statuses to new ones
        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => 'placed']);
        
        DB::table('orders')
            ->whereIn('status', ['confirmed', 'preparing', 'ready'])
            ->update(['status' => 'processing']);

        // Drop old timestamp columns
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'preparing_at', 'ready_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back old timestamp columns
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('confirmed_at')->nullable()->after('notes');
            $table->timestamp('preparing_at')->nullable()->after('confirmed_at');
            $table->timestamp('ready_at')->nullable()->after('preparing_at');
        });

        // Migrate data back
        DB::table('orders')
            ->whereNotNull('placed_at')
            ->update([
                'confirmed_at' => DB::raw('placed_at'),
                'preparing_at' => DB::raw('processing_at'),
            ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_time', 'placed_at', 'processing_at', 'shipping_at']);
        });

        // Revert status enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending'");
        
        // Map new statuses back to old ones
        DB::table('orders')
            ->where('status', 'placed')
            ->update(['status' => 'pending']);
        
        DB::table('orders')
            ->where('status', 'processing')
            ->update(['status' => 'confirmed']);
        
        DB::table('orders')
            ->where('status', 'shipping')
            ->update(['status' => 'preparing']);
    }
};
