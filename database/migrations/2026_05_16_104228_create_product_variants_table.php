<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('variant_type', ['color', 'size']);
            $table->string('value'); // e.g. "Red", "XL"
            $table->string('hex_code')->nullable(); // For colors: #FF0000
            $table->string('image_url')->nullable(); // Image for this color
            $table->integer('stock_quantity')->default(0);
            $table->decimal('price_adjustment', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
