<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema Escolar - Elegir Alumno</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .custom-scroll::-webkit-scrollbar { width: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 font-sans">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center font-bold text-xl text-slate-800">
                <span class="bg-indigo-600 text-white rounded-lg p-1.5 mr-2 text-sm">S</span>
                Sistema Escolar
            </div>
            <div class="text-xs text-slate-400 uppercase font-bold tracking-widest">Panel de Control</div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-6 py-12">
        
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-4xl font-extrabold text-slate-900 mb-3 tracking-tight">Simulador de Inscripciones</h1>
            <p class="text-slate-500 text-lg mb-8">Selecciona un perfil de estudiante para gestionar su carga académica.</p>
            
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" 
                    class="block w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-full shadow-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                    placeholder="Buscar alumno por nombre o correo...">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="studentsGrid">
            @php
                $colors = [
                    ['bg'=>'bg-blue-100', 'text'=>'text-blue-600', 'bar'=>'bg-blue-500'],
                    ['bg'=>'bg-emerald-100', 'text'=>'text-emerald-600', 'bar'=>'bg-emerald-500'],
                    ['bg'=>'bg-amber-100', 'text'=>'text-amber-600', 'bar'=>'bg-amber-500'],
                    ['bg'=>'bg-purple-100', 'text'=>'text-purple-600', 'bar'=>'bg-purple-500'],
                    ['bg'=>'bg-rose-100', 'text'=>'text-rose-600', 'bar'=>'bg-rose-500'],
                    ['bg'=>'bg-cyan-100', 'text'=>'text-cyan-600', 'bar'=>'bg-cyan-500'],
                ];
            @endphp

            @foreach($students as $student)
                @php
                    // Color aleatorio consistente basado en ID
                    $theme = $colors[$student->id % count($colors)];
                    // Calcular porcentaje de progreso (Asumiendo 50 materias total aprox)
                    $percent = min(100, ($student->passed_subjects_count / 30) * 100); 
                @endphp

                <a href="{{ route('schedule.index', $student->id) }}" class="student-card group relative bg-white rounded-2xl p-6 border border-slate-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center">
                    
                    <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity transform group-hover:translate-x-1">
                        <svg class="w-5 h-5 text-slate-300 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>

                    <div class="w-16 h-16 rounded-full {{ $theme['bg'] }} {{ $theme['text'] }} flex items-center justify-center text-2xl font-bold mb-4 shadow-inner group-hover:scale-110 transition-transform duration-300">
                        {{ substr($student->name, 0, 1) }}
                    </div>

                    <h3 class="student-name text-lg font-bold text-slate-800 mb-1 group-hover:text-indigo-600 transition-colors">
                        {{ $student->name }}
                    </h3>
                    <p class="student-email text-xs text-slate-400 font-mono mb-6 truncate w-full px-4">
                        {{ $student->email }}
                    </p>

                    <div class="w-full mt-auto">
                        <div class="flex justify-between items-end mb-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avance</span>
                            <span class="text-xs font-bold text-slate-700">{{ $student->passed_subjects_count }} Aprobadas</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                            <div class="{{ $theme['bar'] }} h-2 rounded-full transition-all duration-1000 ease-out" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div id="noResults" class="hidden text-center py-20">
            <div class="inline-block p-4 rounded-full bg-slate-100 mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <h3 class="text-lg font-medium text-slate-900">No se encontraron alumnos</h3>
            <p class="text-slate-500">Intenta con otro nombre o correo electrónico.</p>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const grid = document.getElementById('studentsGrid');
            const cards = document.querySelectorAll('.student-card');
            const noResults = document.getElementById('noResults');

            searchInput.addEventListener('keyup', (e) => {
                const term = e.target.value.toLowerCase();
                let visibleCount = 0;

                cards.forEach(card => {
                    const name = card.querySelector('.student-name').innerText.toLowerCase();
                    const email = card.querySelector('.student-email').innerText.toLowerCase();

                    if (name.includes(term) || email.includes(term)) {
                        card.style.display = 'flex'; // Flex porque las cards son flex containers
                        visibleCount++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleCount === 0) {
                    grid.classList.add('hidden');
                    noResults.classList.remove('hidden');
                } else {
                    grid.classList.remove('hidden');
                    noResults.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>