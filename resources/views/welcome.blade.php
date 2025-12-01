<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Escolar - Elegir Alumno</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen p-10">
    <div class="max-w-7xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-2">Simulador de Inscripciones</h1>
            <p class="text-slate-400">Selecciona un alumno para simular su carga académica y generar su horario.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($students as $student)
                <a href="{{ route('schedule.index', $student->id) }}" class="group relative block bg-slate-800 rounded-xl p-6 border border-slate-700 hover:border-indigo-500 hover:shadow-2xl hover:shadow-indigo-500/20 transition duration-300">
                    
                    <div class="absolute top-4 right-4 text-slate-600 group-hover:text-indigo-400 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </div>
                    
                    <div class="w-12 h-12 bg-indigo-900 rounded-full flex items-center justify-center mb-4 text-indigo-300 font-bold text-xl group-hover:bg-indigo-600 group-hover:text-white transition shadow-lg">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    
                    <h3 class="text-lg font-bold text-white mb-1 truncate group-hover:text-indigo-300 transition">{{ $student->name }}</h3>
                    <p class="text-slate-500 text-xs mb-4 truncate">{{ $student->email }}</p>

                    <div class="border-t border-slate-700 pt-3 flex justify-between items-center">
                        <span class="text-xs text-slate-400 font-mono">Progreso:</span>
                        <span class="bg-slate-700 text-indigo-300 px-2 py-1 rounded text-xs font-bold">
                            {{ $student->passed_subjects_count }} Aprobadas
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</body>
</html>