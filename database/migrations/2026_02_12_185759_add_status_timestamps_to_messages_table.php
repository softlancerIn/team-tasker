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
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('attachment_original_name');
            $table->timestamp('read_at')->nullable()->after('delivered_at');
            $table->dropColumn('is_read');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['delivered_at', 'read_at']);
            $table->boolean('is_read')->default(false)->after('attachment_original_name');
        });
    }
};
