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
        // Add new timestamp columns only if they don't exist
        if (!Schema::hasColumn('orders', 'placed_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('placed_at')->nullable()->after('notes');
            });
        }
        if (!Schema::hasColumn('orders', 'processing_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('processing_at')->nullable()->after('placed_at');
            });
        }
        if (!Schema::hasColumn('orders', 'shipping_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('shipping_at')->nullable()->after('processing_at');
            });
        }
        if (!Schema::hasColumn('orders', 'estimated_delivery_time')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('estimated_delivery_time')->nullable()->after('delivered_at');
            });
        }

        // Migrate data from old columns to new columns
        DB::table('orders')
            ->whereNotNull('confirmed_at')
            ->update([
                'placed_at' => DB::raw('confirmed_at'),
                'processing_at' => DB::raw('preparing_at'),
            ]);

        // IMPORTANT: Expand ENUM FIRST to include both old and new values
        // This allows us to update the status values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'ready', 'placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending'");
        
        // Now update status values from old to new
        DB::table('orders')
            ->where('status', 'pending')
            ->update(['status' => 'placed']);
        
        DB::table('orders')
            ->whereIn('status', ['confirmed', 'preparing', 'ready'])
            ->update(['status' => 'processing']);

        // Finally, restrict ENUM to only new values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'placed'");

        // Drop old timestamp columns only if they exist
        $columnsToDrop = [];
        if (Schema::hasColumn('orders', 'confirmed_at')) {
            $columnsToDrop[] = 'confirmed_at';
        }
        if (Schema::hasColumn('orders', 'preparing_at')) {
            $columnsToDrop[] = 'preparing_at';
        }
        if (Schema::hasColumn('orders', 'ready_at')) {
            $columnsToDrop[] = 'ready_at';
        }
        if (!empty($columnsToDrop)) {
            Schema::table('orders', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
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

        // Revert status enum - first expand to include both old and new values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('placed', 'processing', 'shipping', 'out_for_delivery', 'delivered', 'cancelled', 'pending', 'confirmed', 'preparing', 'ready') DEFAULT 'pending'");
        
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

        // Now revert to only old values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'preparing', 'ready', 'out_for_delivery', 'delivered', 'cancelled') DEFAULT 'pending'");

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_time', 'placed_at', 'processing_at', 'shipping_at']);
        });
    }
};
