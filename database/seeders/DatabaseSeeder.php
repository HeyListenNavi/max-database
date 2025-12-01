<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\CourseSection;
use App\Models\ClassSchedule;
use App\Models\SubjectPrerequisite;
use App\Models\AcademicRecord;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. APAGAR LOGS Y LIMPIAR BD (Optimización de Memoria)
        DB::disableQueryLog();
        Schema::disableForeignKeyConstraints();
        
        User::truncate();
        Teacher::truncate();
        Subject::truncate();
        CourseSection::truncate();
        ClassSchedule::truncate();
        SubjectPrerequisite::truncate();
        AcademicRecord::truncate();
        
        Schema::enableForeignKeyConstraints();

        $this->command->info('Base de datos limpiada. Iniciando carga masiva...');

        // ---------------------------------------------------------
        // 1. INFRAESTRUCTURA (Maestros, Materias, Horarios)
        // ---------------------------------------------------------

        // Crear Maestros
        $teacherIds = Teacher::factory(20)->create()->pluck('id')->toArray();
        
        $nombresBase = [
            'Programación', 'Cálculo', 'Álgebra', 'Física', 'Inglés', 
            'Base de Datos', 'Redes', 'Ética', 'Contabilidad', 'Sistemas'
        ];

        $previousSemesterIds = []; 

        // Crear 3 Semestres
        for ($sem = 1; $sem <= 3; $sem++) {
            $currentSemesterIds = []; 
            
            foreach ($nombresBase as $index => $baseName) {
                // Crear Materia
                $subject = Subject::create([
                    'name' => $baseName . " " . $this->roman($sem),
                    'code' => strtoupper(substr($baseName, 0, 3)) . "-{$sem}" . Str::random(4),
                    'semester' => $sem,
                    'credits' => rand(4, 8)
                ]);

                $currentSemesterIds[] = $subject->id;

                // Seriación (Prerrequisitos)
                if ($sem > 1 && isset($previousSemesterIds[$index])) {
                    SubjectPrerequisite::create([
                        'subject_id' => $subject->id,
                        'prerequisite_id' => $previousSemesterIds[$index]
                    ]);
                }

                // Grupos y Horarios
                foreach (['A', 'B'] as $groupCode) {
                    $randomTeacherId = $teacherIds[array_rand($teacherIds)];

                    $section = CourseSection::create([
                        'subject_id' => $subject->id,
                        'teacher_id' => $randomTeacherId,
                        'group_code' => $groupCode,
                        'capacity' => 30
                    ]);

                    $days = ($groupCode === 'A') ? ['Monday', 'Wednesday'] : ['Tuesday', 'Thursday'];
                    $startHour = [7, 9, 11, 13, 15, 17][rand(0, 5)];
                    
                    $schedulesData = [];
                    foreach ($days as $day) {
                        $schedulesData[] = [
                            'course_section_id' => $section->id,
                            'day_of_week' => $day,
                            'start_time' => sprintf('%02d:00:00', $startHour),
                            'end_time' => sprintf('%02d:00:00', $startHour + 2),
                            'created_at' => now(), 'updated_at' => now(),
                        ];
                    }
                    ClassSchedule::insert($schedulesData);
                }
            }
            $previousSemesterIds = $currentSemesterIds;
        }

        $this->command->info('Materias y Horarios creados.');

        // ---------------------------------------------------------
        // 2. CASOS ESPECÍFICOS (Para demos claras)
        // ---------------------------------------------------------

        // A. Nuevo Ingreso
        User::factory()->create(['name' => 'Juan Nuevo (Sem 1)', 'email' => 'juan@test.com']);

        // B. Regular (Pasa a Sem 2)
        $pedro = User::factory()->create(['name' => 'Pedro Regular (Sem 2)', 'email' => 'pedro@test.com']);
        $this->createHistory($pedro, 1); // Aprobó semestre 1

        // C. Irregular (Reprobó una en Sem 1)
        $beto = User::factory()->create(['name' => 'Beto Irregular (Reprobado)', 'email' => 'beto@test.com']);
        $sem1Ids = Subject::where('semester', 1)->pluck('id');
        foreach ($sem1Ids as $index => $id) {
            AcademicRecord::create([
                'user_id' => $beto->id,
                'subject_id' => $id,
                'status' => ($index === 0) ? 'failed' : 'passed', // Reprobó la primera
                'grade' => ($index === 0) ? 50 : 90
            ]);
        }

        // ---------------------------------------------------------
        // 3. GENERACIÓN DE 20 USUARIOS AL AZAR CON HISTORIAL
        // ---------------------------------------------------------
        
        $this->command->info('Generando 20 alumnos al azar con historiales variados...');

        $randomStudents = User::factory(20)->create();

        foreach ($randomStudents as $student) {
            // Decidir al azar en qué nivel va el alumno:
            // 0 = Nuevo Ingreso (Sin historial)
            // 1 = Pasó semestre 1 (Va para 2do)
            // 2 = Pasó semestre 1 y 2 (Va para 3ero)
            $semestersCompleted = rand(0, 2);

            if ($semestersCompleted > 0) {
                $this->createHistory($student, $semestersCompleted, true); // true = agregar probabilidad de reprobar
            }
        }

        $this->command->info('Seeding completado con éxito.');
    }

    // Helper para crear historial académico masivo
    private function createHistory($user, $maxSemester, $randomFailures = false) {
        // Obtener todas las materias hasta el semestre indicado
        $subjectIds = Subject::where('semester', '<=', $maxSemester)->pluck('id');

        $records = [];
        foreach ($subjectIds as $subId) {
            // 15% de probabilidad de haber reprobado la materia (si activamos randomFailures)
            $isFailed = $randomFailures && (rand(1, 100) <= 15);

            $records[] = [
                'user_id' => $user->id,
                'subject_id' => $subId,
                'status' => $isFailed ? 'failed' : 'passed',
                'grade' => $isFailed ? rand(40, 59) : rand(70, 100),
                'created_at' => now(), 'updated_at' => now()
            ];
        }
        AcademicRecord::insert($records);
    }

    private function roman($num) {
        $romans = [1 => 'I', 2 => 'II', 3 => 'III'];
        return $romans[$num] ?? $num;
    }
}