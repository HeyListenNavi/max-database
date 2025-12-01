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
        Schema::create('academic_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // El alumno
            $table->foreignId('subject_id')->constrained()->onDelete('cascade'); // La materia
            // Status: 'passed' (ya no la toma), 'failed' (prioridad alta), 'enrolled' (actual)
            $table->enum('status', ['passed', 'failed', 'enrolled'])->default('enrolled');
            $table->decimal('grade', 5, 2)->nullable(); // Calificación opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_records');
    }
};
