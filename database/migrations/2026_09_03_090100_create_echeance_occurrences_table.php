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
        Schema::create('echeance_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('echeance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->unsignedInteger('amount');
            $table->string('status')->default('en_attente');
            $table->timestamps();

            $table->index(['status', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('echeance_occurrences');
    }
};
