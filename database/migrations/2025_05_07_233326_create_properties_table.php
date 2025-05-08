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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->index();
            $table->string('location')->index();
            $table->schemalessAttributes('meta');
            $table->unique(['name', 'location']);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name')->index();
            $table->string('brand')->index();
            $table->string('category')->index();
            $table->text('description');
            $table->integer('price'); //typical price
            $table->schemalessAttributes('meta');
            $table->timestamps();
        });

        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable()->index();
            $table->string('type')->nullable();
            $table->string('cluster')->nullable();
            $table->string('status')->nullable();
            $table->string('sku')->nullable();
            $table->string('project_code')->nullable();
            $table->schemalessAttributes('meta');
            $table->foreign('sku')->references('sku')->on('products');
            $table->foreign('project_code')->references('code')->on('projects');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
        Schema::dropIfExists('products');
        Schema::dropIfExists('projects');
    }
};
