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
        Schema::table('order_returns', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_boy_id')->nullable()->after('user_id');
            $table->string('assignment_type')->nullable()->after('delivery_boy_id');
            
            $table->foreign('delivery_boy_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropForeign(['delivery_boy_id']);
            $table->dropColumn(['delivery_boy_id', 'assignment_type']);
        });
    }
};
