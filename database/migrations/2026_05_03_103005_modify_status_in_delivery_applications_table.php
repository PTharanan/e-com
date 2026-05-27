<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add 'approved' to the allowed list while keeping 'accepted'
        DB::statement("ALTER TABLE delivery_applications MODIFY COLUMN status ENUM('pending', 'accepted', 'approved', 'rejected') DEFAULT 'pending'");
        
        // 2. Migrate existing 'accepted' records to 'approved'
        DB::table('delivery_applications')->where('status', 'accepted')->update(['status' => 'approved']);
        
        // 3. Remove 'accepted' from the allowed list
        DB::statement("ALTER TABLE delivery_applications MODIFY COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE delivery_applications MODIFY COLUMN status ENUM('pending', 'accepted', 'approved', 'rejected') DEFAULT 'pending'");
        DB::table('delivery_applications')->where('status', 'approved')->update(['status' => 'accepted']);
        DB::statement("ALTER TABLE delivery_applications MODIFY COLUMN status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
    }
};
