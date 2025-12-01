<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectFactory> */
    use HasFactory;

    protected $fillable = ['name', 'code', 'semester', 'credits'];

    // Relación: Una materia tiene muchos grupos (secciones)
    public function sections()
    {
        return $this->hasMany(CourseSection::class);
    }

    // RELACIÓN DE SERIACIÓN (Self-referencing Many-to-Many)

    // 1. Obtener las materias necesarias para tomar esta (Prerrequisitos)
    // "Para tomar Cálculo II (this), necesito Cálculo I (return)"
    public function prerequisites()
    {
        return $this->belongsToMany(Subject::class, 'subject_prerequisites', 'subject_id', 'prerequisite_id');
    }

    // 2. (Opcional) Obtener las materias que se desbloquean al pasar esta
    // "Si paso Cálculo I (this), se desbloquea Cálculo II (return)"
    public function unlocks()
    {
        return $this->belongsToMany(Subject::class, 'subject_prerequisites', 'prerequisite_id', 'subject_id');
    }
}
