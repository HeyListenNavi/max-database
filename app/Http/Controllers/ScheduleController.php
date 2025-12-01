<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ScheduleGeneratorService;
use App\Models\User;
use App\Models\Subject;

class ScheduleController 
{
    protected $generator;

    public function __construct(ScheduleGeneratorService $generator)
    {
        $this->generator = $generator;
    }

    // PANTALLA DE INICIO: Elegir Alumno
    public function chooseStudent()
    {
        // Obtenemos todos los alumnos y contamos cuántas materias han aprobado
        // para mostrar un indicador de "progreso" en la tarjeta.
        $students = User::withCount(['academicRecords as passed_subjects_count' => function($query){
            $query->where('status', 'passed');
        }])->get();

        return view('welcome', compact('students'));
    }

    // FORMULARIO: Ver materias disponibles para el alumno seleccionado
    public function index(User $user)
    {
        // 1. Historial del alumno seleccionado
        $passedIds = $user->academicRecords()->passed()->pluck('subject_id')->toArray();
        $failedIds = $user->academicRecords()->failed()->pluck('subject_id')->toArray();

        // 2. Todas las materias agrupadas por semestre
        $subjectsBySemester = Subject::with('prerequisites')->get()->groupBy('semester');

        return view('schedule.index', compact('user', 'subjectsBySemester', 'passedIds', 'failedIds'));
    }

    // ACCIÓN: Generar el horario
    public function generate(Request $request, User $user)
    {
        $request->validate([
            'target_count' => 'required|integer|min:1|max:12',
            'subjects' => 'array'
        ]);

        $prioritySubjectIds = $request->input('subjects', []);
        $targetCount = $request->input('target_count');

        // Llamamos al servicio pasando al usuario real de la URL
        $result = $this->generator->generate($user, $prioritySubjectIds, $targetCount);

        return view('schedule.result', [
            'schedule' => $result['schedule'],
            'warnings' => $result['warnings'],
            'user' => $user, // Pasamos el usuario para el link de "volver"
            'filled_count' => count($result['schedule'])
        ]);
    }
}