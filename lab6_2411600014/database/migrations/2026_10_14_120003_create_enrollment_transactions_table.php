<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollment_transactions', function (Blueprint $table) {
            $table->id();

            // Deleting a student removes that student's history (cascade).
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            // Keep the audit row even if the staff account is deleted.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('type', 20);               // add | drop | adjustment
            $table->smallInteger('quantity');         // SIGNED: + units added, - units dropped
            $table->string('reference_number', 100)->nullable();
            $table->string('reason', 100)->nullable(); // used by adjustments
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for the common filters / history queries
            $table->index(['student_id', 'created_at']);
            $table->index('type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_transactions');
    }
};
