<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subject;
use App\Models\CourseSection;

class ScheduleGeneratorService
{
    public function generate(User $student, array $prioritySubjectIds, int $targetCount)
    {
        $schedule = []; 
        $warnings = [];
        
        // IDs de las materias ya agendadas para no repetirlas en el relleno
        $scheduledSubjectIds = [];

        // 1. Obtener Historial (Aprobadas)
        $passedSubjectIds = $student->academicRecords()->passed()->pluck('subject_id')->toArray();

        // ---------------------------------------------------------
        // FASE 1: MATERIAS PRIORITARIAS (Las que el usuario marcó)
        // ---------------------------------------------------------
        if (!empty($prioritySubjectIds)) {
            $prioritySubjects = Subject::with('prerequisites')
                                    ->whereIn('id', $prioritySubjectIds)
                                    ->get();

            foreach ($prioritySubjects as $subject) {
                // Intentamos agendar usando la función auxiliar
                if ($this->tryToScheduleSubject($subject, $schedule, $passedSubjectIds, $warnings, true)) {
                    $scheduledSubjectIds[] = $subject->id;
                }
            }
        }

        // ---------------------------------------------------------
        // FASE 2: RELLENO AUTOMÁTICO (Auto-fill)
        // ---------------------------------------------------------
        $slotsRemaining = $targetCount - count($schedule);

        if ($slotsRemaining > 0) {
            // Buscamos materias que:
            // 1. No haya aprobado ya.
            // 2. No estén ya en el horario (prioritarias).
            // 3. Ordenadas por Semestre ASC (primero recursamiento/atrasadas, luego actuales).
            $candidateSubjects = Subject::with('prerequisites')
                                    ->whereNotIn('id', $passedSubjectIds)
                                    ->whereNotIn('id', $scheduledSubjectIds)
                                    ->orderBy('semester', 'asc') // <--- CLAVE: Llena desde lo más viejo
                                    ->orderBy('id', 'asc')
                                    ->get();

            foreach ($candidateSubjects as $subject) {
                if ($slotsRemaining <= 0) break; // Ya llenamos el cupo

                // Intentamos agendar (pasamos false en $isPriority para que no genere warnings si no cabe)
                // Usamos un array de warnings temporal porque si una de relleno no cabe, no nos importa avisar.
                $dummyWarnings = []; 
                if ($this->tryToScheduleSubject($subject, $schedule, $passedSubjectIds, $dummyWarnings, false)) {
                    $scheduledSubjectIds[] = $subject->id;
                    $slotsRemaining--; // Ocupamos un lugar
                }
            }
        }

        return [
            'schedule' => $schedule, // Array de CourseSection
            'warnings' => $warnings
        ];
    }

    /**
     * Intenta encontrar grupo y agendar una materia específica.
     * Retorna true si tuvo éxito, false si falló.
     * Modifica el array $schedule por referencia.
     */
    private function tryToScheduleSubject($subject, array &$schedule, array $passedSubjectIds, array &$warnings, bool $isPriority)
    {
        // A. Validar Seriación
        $missingPrereqs = [];
        foreach ($subject->prerequisites as $prereq) {
            if (!in_array($prereq->id, $passedSubjectIds)) {
                $missingPrereqs[] = $prereq->name;
            }
        }

        if (!empty($missingPrereqs)) {
            if ($isPriority) {
                $warnings[] = "No puedes cursar '{$subject->name}' (Prioritaria) por falta de requisitos: " . implode(', ', $missingPrereqs);
            }
            return false;
        }

        // B. Buscar Grupos con Cupo
        $availableSections = CourseSection::with(['schedules', 'teacher', 'subject'])
                                ->where('subject_id', $subject->id)
                                ->where('capacity', '>', 0)
                                ->get();

        if ($availableSections->isEmpty()) {
            if ($isPriority) $warnings[] = "No hay cupo en ningún grupo para '{$subject->name}'.";
            return false;
        }

        // C. Buscar uno que no choque (Algoritmo Greedy)
        foreach ($availableSections as $potentialSection) {
            if (!$this->hasTimeConflict($potentialSection, $schedule)) {
                // ¡Éxito! Agregamos la sección al horario
                $schedule[] = $potentialSection;
                return true;
            }
        }

        if ($isPriority) {
            $warnings[] = "No se pudo agendar '{$subject->name}' por choque de horarios con tus otras materias.";
        }
        
        return false;
    }

    private function hasTimeConflict(CourseSection $newSection, array $currentSchedule)
    {
        foreach ($newSection->schedules as $newTime) {
            foreach ($currentSchedule as $existingSection) {
                foreach ($existingSection->schedules as $existingTime) {
                    if ($newTime->day_of_week !== $existingTime->day_of_week) continue;

                    if ($newTime->start_time < $existingTime->end_time && 
                        $newTime->end_time > $existingTime->start_time) {
                        return true; 
                    }
                }
            }
        }
        return false;
    }
}