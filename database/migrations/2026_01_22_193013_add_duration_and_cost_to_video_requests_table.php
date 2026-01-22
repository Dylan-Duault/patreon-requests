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
        Schema::table('video_requests', function (Blueprint $table) {
            $table->unsignedInteger('duration_seconds')->nullable()->after('thumbnail');
            $table->unsignedTinyInteger('request_cost')->default(1)->after('duration_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_requests', function (Blueprint $table) {
            $table->dropColumn(['duration_seconds', 'request_cost']);
        });
    }
};
