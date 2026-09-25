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
        Schema::table('meals', function (Blueprint $table) {
            // Add subcategory relationship
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained()->onDelete('set null');
            
            // Add new fields
            $table->decimal('rating', 3, 2)->default(0)->after('discount_price')->comment('Average rating (0-5)');
            $table->integer('rating_count')->default(0)->after('rating')->comment('Number of ratings');
            $table->string('size')->nullable()->after('rating_count')->comment('Product size (e.g., 500g, 1kg, 2L)');
            $table->date('expiry_date')->nullable()->after('size')->comment('Product expiry date');
            $table->text('includes')->nullable()->after('expiry_date')->comment('What is included (e.g., 1 piece, 6 pack)');
            $table->text('how_to_use')->nullable()->after('includes')->comment('Instructions on how to use the product');
            $table->text('features')->nullable()->after('how_to_use')->comment('Product features (JSON or text)');
            $table->string('brand')->nullable()->after('features')->comment('Product brand name');
            $table->integer('stock_quantity')->default(0)->after('brand')->comment('Available stock quantity');
            $table->integer('sold_count')->default(0)->after('stock_quantity')->comment('Number of times sold');
            
            // Add indexes
            $table->index('subcategory_id');
            $table->index('rating');
            $table->index('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meals', function (Blueprint $table) {
            $table->dropForeign(['subcategory_id']);
            $table->dropIndex(['subcategory_id']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['stock_quantity']);
            
            $table->dropColumn([
                'subcategory_id',
                'rating',
                'rating_count',
                'size',
                'expiry_date',
                'includes',
                'how_to_use',
                'features',
                'brand',
                'stock_quantity',
                'sold_count',
            ]);
        });
    }
};
