<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ClassScheduleFactory> */
    use HasFactory;

    protected $fillable = ['course_section_id', 'day_of_week', 'start_time', 'end_time'];

    public function section()
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }
}
