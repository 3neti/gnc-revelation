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
        Schema::create('loan_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_code')->unique();
            $table->string('lending_institution');
            $table->decimal('total_contract_price', 15, 2);
            $table->json('inputs'); // Raw buyer, property, order inputs
            $table->json('computation'); // Computed MortgageComputationData
            $table->boolean('qualified');
            $table->decimal('required_equity', 15, 2);
            $table->decimal('income_gap', 15, 2);
            $table->decimal('suggested_down_payment_percent', 5, 4);
            $table->string('reason')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_profiles');
    }
};
