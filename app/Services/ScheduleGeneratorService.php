<?php

namespace App\Services;

use App\Models\User;
use App\Models\Subject;
use App\Models\CourseSection;

class ScheduleGeneratorService
{
    /**
     * Genera el horario óptimo aplicando sistema de pesos y minimización de huecos.
     */
    public function generate(User $student, array $prioritySubjectIds, int $targetCount)
    {
        $schedule = []; 
        $warnings = [];
        $scheduledSubjectIds = [];

        // 1. Obtener Historial
        $passedSubjectIds = $student->academicRecords()->passed()->pluck('subject_id')->toArray();
        $failedSubjectIds = $student->academicRecords()->failed()->pluck('subject_id')->toArray();

        // 2. OBTENER CANDIDATOS
        // Traemos todas las materias que NO ha aprobado.
        // IMPORTANTE: Cargamos 'sections.schedules' y 'sections.teacher'
        $allCandidates = Subject::with(['prerequisites', 'sections.schedules', 'sections.teacher'])
                                ->whereNotIn('id', $passedSubjectIds)
                                ->get();

        // 3. CALCULAR PESO (PRIORITY SCORE)
        $rankedCandidates = $allCandidates->map(function($subject) use ($failedSubjectIds, $prioritySubjectIds) {
            $subject->priority_score = $this->calculatePriorityScore($subject, $failedSubjectIds, $prioritySubjectIds);
            return $subject;
        });

        // 4. ORDENAR POR IMPORTANCIA
        $sortedCandidates = $rankedCandidates->sortByDesc('priority_score');

        // 5. INTENTAR AGENDAR
        foreach ($sortedCandidates as $subject) {
            $isUserPriority = in_array($subject->id, $prioritySubjectIds);
            
            if (count($schedule) >= $targetCount && !$isUserPriority) {
                continue; 
            }

            if ($this->tryToScheduleSubject($subject, $schedule, $passedSubjectIds, $warnings, $isUserPriority)) {
                $scheduledSubjectIds[] = $subject->id;
            }
        }

        // Ordenar visualmente por día
        usort($schedule, function($a, $b) {
            $dayOrder = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7];
            $dayA = $a->schedules->first()->day_of_week ?? 'Monday';
            $dayB = $b->schedules->first()->day_of_week ?? 'Monday';
            
            if ($dayA !== $dayB) return $dayOrder[$dayA] <=> $dayOrder[$dayB];
            return strcmp($a->schedules->first()->start_time, $b->schedules->first()->start_time);
        });

        return [
            'schedule' => $schedule,
            'warnings' => $warnings
        ];
    }

    private function calculatePriorityScore($subject, $failedIds, $priorityIds)
    {
        $score = 0;
        if (in_array($subject->id, $priorityIds)) $score += 10000;
        if (in_array($subject->id, $failedIds)) $score += 5000;
        $score += (100 - $subject->semester); 
        return $score;
    }

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
                $warnings[] = "No puedes cursar '{$subject->name}' por requisitos: " . implode(', ', $missingPrereqs);
            }
            return false;
        }

        // B. Buscar Grupos con Cupo
        $availableSections = $subject->sections->where('capacity', '>', 0);

        if ($availableSections->isEmpty()) {
            if ($isPriority) $warnings[] = "No hay cupo en '{$subject->name}'.";
            return false;
        }

        // C. FILTRAR Y OPTIMIZAR
        $validCandidates = [];

        foreach ($availableSections as $section) {
            if (!$this->hasTimeConflict($section, $schedule)) {
                $validCandidates[] = $section;
            }
        }

        if (empty($validCandidates)) {
            if ($isPriority) $warnings[] = "No se pudo agendar '{$subject->name}' por choque de horarios.";
            return false;
        }

        // Desempate por Huecos
        usort($validCandidates, function($a, $b) use ($schedule) {
            $scoreA = $this->calculateGapScore($a, $schedule);
            $scoreB = $this->calculateGapScore($b, $schedule);
            if ($scoreA === $scoreB) {
                return strcmp($a->schedules->first()->start_time, $b->schedules->first()->start_time);
            }
            return $scoreA <=> $scoreB;
        });

        // --- CORRECCIÓN CRÍTICA AQUÍ ---
        $bestSection = $validCandidates[0];
        
        // Manualmente pegamos la relación 'subject' al objeto section
        // para que esté disponible en la vista sin hacer otra consulta.
        $bestSection->setRelation('subject', $subject); 
        
        $schedule[] = $bestSection;
        return true;
    }

    private function calculateGapScore($candidateSection, $currentSchedule)
    {
        if (empty($currentSchedule)) {
            return (int) str_replace(':', '', $candidateSection->schedules->first()->start_time ?? '2400');
        }

        $totalGapMinutes = 0;

        foreach ($candidateSection->schedules as $newTime) {
            $day = $newTime->day_of_week;
            $startNew = $this->timeToMinutes($newTime->start_time);
            $endNew   = $this->timeToMinutes($newTime->end_time);

            $dayHasClasses = false;
            $minGapForThisBlock = 10000;

            foreach ($currentSchedule as $existingSection) {
                foreach ($existingSection->schedules as $existingTime) {
                    if ($existingTime->day_of_week !== $day) continue;

                    $dayHasClasses = true;
                    $startExisting = $this->timeToMinutes($existingTime->start_time);
                    $endExisting   = $this->timeToMinutes($existingTime->end_time);

                    if ($endNew <= $startExisting) {
                        $gap = $startExisting - $endNew;
                    } else {
                        $gap = $startNew - $endExisting;
                    }

                    if ($gap >= 0 && $gap < $minGapForThisBlock) {
                        $minGapForThisBlock = $gap;
                    }
                }
            }

            if ($dayHasClasses) {
                $totalGapMinutes += $minGapForThisBlock;
            } else {
                $totalGapMinutes += ($startNew / 60); 
            }
        }

        return $totalGapMinutes;
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

    private function timeToMinutes($timeStr) {
        $parts = explode(':', $timeStr);
        return ((int)$parts[0] * 60) + (int)$parts[1];
    }
}