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
        Schema::table('movies', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['status', 'view_count']);
            $table->index('title');
        });

        Schema::table('movie_links', function (Blueprint $table) {
            $table->index(['movie_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['status', 'view_count']);
            $table->dropIndex(['title']);
        });

        Schema::table('movie_links', function (Blueprint $table) {
            $table->dropIndex(['movie_id', 'status']);
        });
    }
};
