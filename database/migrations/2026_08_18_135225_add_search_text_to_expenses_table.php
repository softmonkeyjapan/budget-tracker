<?php

declare(strict_types=1);

use App\Support\SearchNormalizer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('search_text')->nullable()->after('description');
        });

        DB::table('expenses')->orderBy('id')->select(['id', 'description'])
            ->chunkById(200, function ($expenses) {
                foreach ($expenses as $expense) {
                    DB::table('expenses')->where('id', $expense->id)->update([
                        'search_text' => SearchNormalizer::normalize((string) $expense->description),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('search_text');
        });
    }
};
