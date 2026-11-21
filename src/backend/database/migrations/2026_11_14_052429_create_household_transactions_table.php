<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('household_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('household_category_id')
                ->constrained('household_categories');
            $table->date('record_date');
            $table->string('memo', 50)->nullable();
            $table->integer('amount');
            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE household_transactions
             ADD CONSTRAINT household_amount_check
             CHECK (amount > 0)'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('household_transactions');
    }
};
