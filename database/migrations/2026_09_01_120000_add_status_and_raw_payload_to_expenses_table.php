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
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('status')->default('validee')->after('description');
            $table->text('raw_payload')->nullable()->after('status');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
            $table->unsignedInteger('amount')->nullable()->change();
        });

        // The pending-expenses badge (HandleInertiaRequests) counts by (user_id,
        // status) on every authenticated request — keep that query cheap.
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
            $table->unsignedInteger('amount')->nullable(false)->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['status', 'raw_payload']);
        });
    }
};
