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
        Schema::table('users', function (Blueprint $table) {
            $table->string('patreon_id')->unique()->nullable()->after('id');
            $table->text('patreon_access_token')->nullable()->after('email');
            $table->text('patreon_refresh_token')->nullable()->after('patreon_access_token');
            $table->timestamp('patreon_token_expires_at')->nullable()->after('patreon_refresh_token');
            $table->string('patron_status')->nullable()->after('patreon_token_expires_at');
            $table->unsignedInteger('patron_tier_cents')->default(0)->after('patron_status');
            $table->string('avatar')->nullable()->after('patron_tier_cents');
            $table->boolean('is_admin')->default(false)->after('avatar');
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'patreon_id',
                'patreon_access_token',
                'patreon_refresh_token',
                'patreon_token_expires_at',
                'patron_status',
                'patron_tier_cents',
                'avatar',
                'is_admin',
            ]);
            $table->string('password')->nullable(false)->change();
        });
    }
};
