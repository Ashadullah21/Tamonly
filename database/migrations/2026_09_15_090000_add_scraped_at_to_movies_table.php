<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds scraped_at timestamp to movies so the scraper can track
 * when each entry was last seen / discovered, enabling incremental sync.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->timestamp('scraped_at')->nullable()->after('source_url');
            $table->index('scraped_at');
        });
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            $table->dropIndex(['scraped_at']);
            $table->dropColumn('scraped_at');
        });
    }
};
