<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectPrerequisite extends Model
{
    /** @use HasFactory<\Database\Factories\SubjectPrerequisiteFactory> */
    use HasFactory;

    // Especificamos la tabla explícitamente
    protected $table = 'subject_prerequisites';

    protected $fillable = ['subject_id', 'prerequisite_id'];

    // La materia que TIENE el requisito (ej: Cálculo II)
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    // La materia que ES el requisito (ej: Cálculo I)
    public function prerequisite()
    {
        return $this->belongsTo(Subject::class, 'prerequisite_id');
    }
}
