<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSection extends Model
{
    /** @use HasFactory<\Database\Factories\CourseSectionFactory> */
    use HasFactory;

    protected $fillable = ['subject_id', 'teacher_id', 'group_code', 'capacity'];

    // Pertenece a una Materia
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Pertenece a un Maestro
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // Tiene múltiples horarios (ej: Lunes 8am, Jueves 10am)
    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
