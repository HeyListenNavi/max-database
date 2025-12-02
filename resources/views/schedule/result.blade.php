<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Oficial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos para impresión y scrollbar */
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ccc; }
            /* Ocultar scrollbars al imprimir */
            ::-webkit-scrollbar { display: none; }
        }
        
        .custom-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background-color: #f1f5f9; }

        /* Animación del Modal */
        .modal-enter { opacity: 0; transform: scale(0.95); }
        .modal-enter-active { opacity: 1; transform: scale(1); transition: opacity 0.2s, transform 0.2s; }
        .modal-exit { opacity: 1; transform: scale(1); }
        .modal-exit-active { opacity: 0; transform: scale(0.95); transition: opacity 0.2s, transform 0.2s; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 font-sans">

    {{-- LÓGICA PHP PARA ORDENAR LA MATRIZ --}}
    @php
        // Convertimos a array para evitar errores de objetos
        $scheduleArray = json_decode(json_encode($schedule), true);
        $totalClasesEncontradas = 0;
        
        // Rango de horas (7am a 9pm)
        $startHour = 7; 
        $endHour = 21;
        $hoursRange = range($startHour, $endHour);
        
        // Días de la semana
        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']; 
        $daysEs = [
            'monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 
            'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'
        ];

        // Inicializar matriz vacía
        $calendar = [];
        foreach ($weekDays as $day) {
            foreach ($hoursRange as $h) $calendar[$day][$h] = null;
        }

        // Definir colores para las materias
        $colores = [
            ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'border' => 'border-blue-300'],
            ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-300'],
            ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'border' => 'border-purple-300'],
            ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'border' => 'border-amber-300'],
            ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'border' => 'border-rose-300'],
        ];

        // Llenar matriz con los datos del horario
        foreach ($scheduleArray as $index => $section) {
            // Asignar un color fijo basado en el ID de la materia
            $color = $colores[$index % count($colores)];

            if (!empty($section['schedules']) && is_array($section['schedules'])) {
                foreach ($section['schedules'] as $time) {
                    $totalClasesEncontradas++;
                    $dayKey = strtolower(trim($time['day_of_week'] ?? ''));
                    $hStart = (int) substr($time['start_time'] ?? '00', 0, 2); 
                    $hEnd   = (int) substr($time['end_time'] ?? '00', 0, 2);   

                    for ($h = $hStart; $h < $hEnd; $h++) {
                        if (isset($calendar[$dayKey]) && array_key_exists($h, $calendar[$dayKey])) {
                            $calendar[$dayKey][$h] = [
                                'name'    => $section['subject']['name'] ?? 'Materia',
                                'code'    => $section['subject']['code'] ?? '---',
                                'group'   => $section['group_code'] ?? '?',
                                'teacher' => $section['teacher']['name'] ?? 'Profesor',
                                'credits' => $section['subject']['credits'] ?? 0,
                                'time_range' => substr($time['start_time'],0,5) . ' - ' . substr($time['end_time'],0,5),
                                'color'   => $color // Guardamos el estilo completo
                            ];
                        }
                    }
                }
            }
        }
    @endphp

    <div class="max-w-7xl mx-auto py-8 px-4">
        
        <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 no-print">
            <div>
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Horario Generado</h2>
                <h1 class="text-3xl font-extrabold text-slate-800">
                    {{ $user->name }}
                </h1>
                <div class="flex gap-4 mt-2 text-sm text-slate-500">
                    <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg> {{ count($scheduleArray) }} Materias</span>
                    <span class="flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> {{ $totalClasesEncontradas }} Horas clase</span>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('schedule.index', $user->id) }}" class="px-4 py-2 text-slate-600 bg-slate-100 rounded-lg font-bold hover:bg-slate-200 transition text-sm">
                    Reintentar
                </a>
                <button onclick="window.print()" class="px-4 py-2 text-white bg-indigo-600 rounded-lg font-bold hover:bg-indigo-700 transition text-sm shadow-lg shadow-indigo-200 flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Imprimir
                </button>
            </div>
        </div>

        @if(!empty($warnings))
            <div class="mb-6 bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg no-print">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-bold text-amber-800">Avisos del Sistema</h3>
                        <ul class="mt-1 list-disc list-inside text-sm text-amber-700">
                            @foreach($warnings as $w) <li>{{ $w }}</li> @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(count($scheduleArray) > 0)
            <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-slate-200 print-shadow-none">
                <div class="overflow-x-auto custom-scroll">
                    <table class="w-full min-w-[900px] border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200">
                                <th class="py-3 px-2 w-16 text-xs font-bold text-slate-400 uppercase tracking-wider text-center border-r border-slate-200 sticky left-0 bg-slate-50 z-10">
                                    Hora
                                </th>
                                @foreach($weekDays as $day)
                                    <th class="py-4 px-2 w-1/6 text-sm font-bold text-slate-700 uppercase border-r border-slate-200 last:border-0">
                                        {{ $daysEs[$day] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hoursRange as $hour)
                                <tr class="h-20 group hover:bg-slate-50/50 transition-colors">
                                    <td class="sticky left-0 bg-white group-hover:bg-slate-50 border-r border-slate-200 border-b border-slate-100 p-0 z-10">
                                        <div class="text-center h-full flex flex-col justify-center">
                                            <span class="block text-sm font-bold text-slate-700">{{ sprintf('%02d:00', $hour) }}</span>
                                            <span class="block text-[10px] text-slate-400 font-mono">{{ sprintf('%02d:00', $hour+1) }}</span>
                                        </div>
                                    </td>

                                    @foreach($weekDays as $day)
                                        @php $clase = $calendar[$day][$hour] ?? null; @endphp
                                        
                                        <td class="p-1 border-r border-slate-200 border-b border-slate-100 relative align-top">
                                            @if($clase)
                                                <div onclick="openModal(this)"
                                                     data-details="{{ json_encode($clase) }}"
                                                     class="w-full h-full rounded-lg border-l-4 shadow-sm cursor-pointer transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 group/card flex flex-col justify-between p-2
                                                     {{ $clase['color']['bg'] }} {{ $clase['color']['border'] }}">
                                                    
                                                    <div>
                                                        <div class="text-[11px] font-bold leading-tight {{ $clase['color']['text'] }} uppercase tracking-tight mb-1">
                                                            {{ $clase['name'] }}
                                                        </div>
                                                        <div class="flex items-center gap-1 text-[10px] text-slate-600 font-mono bg-white/60 w-fit px-1 rounded">
                                                            <span>Gpo {{ $clase['group'] }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="mt-1 flex items-center justify-between">
                                                        <span class="text-[9px] text-slate-500 font-medium truncate max-w-[80px]">
                                                            {{ Str::limit($clase['teacher'], 12) }}
                                                        </span>
                                                        <svg class="w-3 h-3 text-slate-400 opacity-0 group-hover/card:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="text-center py-20 bg-white rounded-xl border border-dashed border-slate-300">
                <p class="text-slate-500 text-lg">No se ha generado ningún horario.</p>
                <a href="{{ route('schedule.index', $user->id) }}" class="text-indigo-600 font-bold hover:underline mt-2 inline-block">Intentar de nuevo</a>
            </div>
        @endif
    </div>

    <div id="classModal" class="fixed inset-0 z-50 hidden no-print" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalOverlay"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 scale-95" id="modalPanel">
                    
                    <div class="h-24 bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center relative">
                        <div class="absolute top-4 right-4">
                            <button type="button" onclick="closeModal()" class="text-white/70 hover:text-white bg-black/20 hover:bg-black/40 rounded-full p-1 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="text-6xl text-white/20 font-bold tracking-widest absolute select-none pointer-events-none" id="modalCodeBg">
                            </div>
                        <div class="bg-white rounded-full p-3 shadow-lg -mb-12 relative z-10">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                    </div>

                    <div class="px-6 pt-10 pb-6">
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-slate-900 leading-6" id="modalTitle">Nombre Materia</h3>
                            <p class="text-sm text-slate-500 mt-1" id="modalCode">CODE-123</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center">
                                <span class="block text-xs font-bold text-slate-400 uppercase">Grupo</span>
                                <span class="block font-bold text-slate-800 text-lg" id="modalGroup">A</span>
                            </div>
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-center">
                                <span class="block text-xs font-bold text-slate-400 uppercase">Créditos</span>
                                <span class="block font-bold text-slate-800 text-lg" id="modalCredits">8</span>
                            </div>
                        </div>

                        <div class="mt-4 bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div class="ml-3 w-full">
                                    <h4 class="text-sm font-bold text-indigo-900">Profesor Titular</h4>
                                    <p class="text-sm text-indigo-700 mt-0.5" id="modalTeacher">Nombre Profesor</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 border-t border-slate-100 pt-4 text-center">
                            <span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                <svg class="mr-1.5 h-2 w-2 text-green-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                Horario: <span id="modalTime" class="ml-1">07:00 - 09:00</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('classModal');
        const overlay = document.getElementById('modalOverlay');
        const panel = document.getElementById('modalPanel');

        // Referencias a elementos dentro del modal
        const mTitle = document.getElementById('modalTitle');
        const mCode = document.getElementById('modalCode');
        const mCodeBg = document.getElementById('modalCodeBg');
        const mGroup = document.getElementById('modalGroup');
        const mCredits = document.getElementById('modalCredits');
        const mTeacher = document.getElementById('modalTeacher');
        const mTime = document.getElementById('modalTime');

        function openModal(element) {
            // Leer datos del atributo data-details
            const data = JSON.parse(element.dataset.details);

            // Llenar el modal
            mTitle.innerText = data.name;
            mCode.innerText = data.code;
            mCodeBg.innerText = data.code; 
            mGroup.innerText = data.group;
            mCredits.innerText = data.credits;
            mTeacher.innerText = data.teacher;
            mTime.innerText = data.time_range;

            // Mostrar
            modal.classList.remove('hidden');
            
            // Animación de entrada
            setTimeout(() => {
                overlay.classList.remove('opacity-0');
                panel.classList.remove('opacity-0', 'scale-95');
                panel.classList.add('opacity-100', 'scale-100');
            }, 10);
        }

        function closeModal() {
            // Animación de salida
            overlay.classList.add('opacity-0');
            panel.classList.remove('opacity-100', 'scale-100');
            panel.classList.add('opacity-0', 'scale-95');

            // Ocultar div después de la animación
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        // Cerrar al dar click fuera (Overlay)
        overlay.addEventListener('click', closeModal);

        // Cerrar con tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape" && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    </script>
</body>
</html>