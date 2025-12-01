<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicRecord extends Model
{
    /** @use HasFactory<\Database\Factories\AcademicRecordFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'subject_id', 'status', 'grade'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
    
    // Scope helper para buscar reprobadas fácilmente
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
    
    // Scope helper para buscar aprobadas
    public function scopePassed($query)
    {
        return $query->where('status', 'passed');
    }
}
