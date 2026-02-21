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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_interval')->nullable(); // daily, weekly, monthly, yearly
            $table->timestamp('next_occurrence_at')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('custom_fields')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn([
                'parent_id', 'is_recurring', 'recurring_interval', 
                'next_occurrence_at', 'estimated_hours', 
                'started_at', 'completed_at', 'custom_fields'
            ]);
        });
    }
};
