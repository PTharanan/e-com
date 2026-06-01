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
        Schema::table('product_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('product_reviews', 'replied_by')) {
                $table->unsignedBigInteger('replied_by')->nullable();
            }
            if (!Schema::hasColumn('product_reviews', 'replied_at')) {
                $table->timestamp('replied_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            if (Schema::hasColumn('product_reviews', 'replied_by')) {
                $table->dropColumn('replied_by');
            }
            if (Schema::hasColumn('product_reviews', 'replied_at')) {
                $table->dropColumn('replied_at');
            }
        });
    }
};
