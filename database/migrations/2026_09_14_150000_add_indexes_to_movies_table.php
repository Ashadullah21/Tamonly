<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Wrapped in try/catch to be idempotent — safe to run even if indexes
     * already exist on the production database (Aiven).
     */
    public function up(): void
    {
        try {
            Schema::table('movies', function (Blueprint $table) {
                $table->index(['status', 'created_at']);
            });
        } catch (\Exception $e) { /* index already exists */ }

        try {
            Schema::table('movies', function (Blueprint $table) {
                $table->index(['status', 'view_count']);
            });
        } catch (\Exception $e) { /* index already exists */ }

        try {
            Schema::table('movies', function (Blueprint $table) {
                $table->index('title');
            });
        } catch (\Exception $e) { /* index already exists */ }

        try {
            Schema::table('movie_links', function (Blueprint $table) {
                $table->index(['movie_id', 'status']);
            });
        } catch (\Exception $e) { /* index already exists */ }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropIndex(['status', 'created_at']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropIndex(['status', 'view_count']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('movies', function (Blueprint $table) {
                $table->dropIndex(['title']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('movie_links', function (Blueprint $table) {
                $table->dropIndex(['movie_id', 'status']);
            });
        } catch (\Exception $e) {}
    }
};
