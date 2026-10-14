<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lab 6 mapping: "reorder level" -> min_units (minimum full-time load).
     * low_load_notified_at prevents sending duplicate low-load notifications.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedTinyInteger('min_units')->default(12)->after('enrolled_units');
            $table->timestamp('low_load_notified_at')->nullable()->after('min_units');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['min_units', 'low_load_notified_at']);
        });
    }
};
