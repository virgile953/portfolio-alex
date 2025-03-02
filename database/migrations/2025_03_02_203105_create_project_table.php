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
        Schema::create('project', function (Blueprint $table) {
            $table->id();
            $table->string(column: 'title');
            $table->text(column: 'description');  // Changed to text for longer descriptions
            $table->string(column: 'type');
            $table->string(column: 'url')->nullable();  // Not all projects may have URLs
            $table->json(column: 'images')->nullable();
            $table->json(column: 'stl_files')->nullable();  // Not all projects may have STL files
            $table->text(column: 'materials')->nullable();  // What materials were used
            $table->json(column: 'specifications')->nullable();  // For dimensions, weight, etc.
            $table->date(column: 'completion_date')->nullable();  // When the project was completed
            $table->boolean(column: 'featured')->default(false);  // To highlight special projects
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};
