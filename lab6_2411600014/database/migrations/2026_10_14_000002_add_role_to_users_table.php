<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds a simple role column so we can have admin-only actions (Part 3 destroy,
 * Part 10 item 10) and decide who receives low stock alerts (Part 6).
 * Roles: admin, manager, staff (default for newly registered users).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('staff')->after('password')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn('role');
        });
    }
};
