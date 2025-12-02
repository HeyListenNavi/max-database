<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripción Inteligente</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos limpios */
        .card-blocked { background-color: #f3f4f6; opacity: 0.6; pointer-events: none; border: 1px dashed #cbd5e1; }
        .card-passed { background-color: #ecfdf5; border-color: #10b981; opacity: 0.8; }
        
        /* Checkbox Customizado */
        /* Cuando el input 'peer' está checado, el div hermano cambia */
        .peer:checked + .custom-checkbox-container {
            background-color: #eff6ff; /* blue-50 */
            border-color: #4f46e5;     /* indigo-600 */
            box-shadow: 0 0 0 1px #4f46e5;
        }
        /* Mostrar la palomita cuando está checado */
        .peer:checked + .custom-checkbox-container .check-icon {
            opacity: 1;
            transform: scale(1);
        }
        /* Estilo deshabilitado */
        .peer:disabled + .custom-checkbox-container {
            background-color: #f9fafb;
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 font-sans">

    <nav class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center">
            <div class="flex items-center font-bold text-xl text-slate-800">
                <span class="bg-indigo-600 text-white rounded-lg p-1.5 mr-2 text-sm">S</span>
                Sistema Escolar
            </div>
            <div class="flex items-center gap-4 text-sm">
                <span class="text-slate-500">{{ $user->name }}</span>
                <a href="{{ route('home') }}" class="text-indigo-600 font-medium hover:underline">Salir</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        
        <form action="{{ route('schedule.generate', $user->id) }}" method="POST" id="scheduleForm">
            @csrf

            <div class="flex flex-col lg:flex-row gap-8">
                
                <div class="flex-1">
                    <div class="mb-6">
                        <h1 class="text-3xl font-bold text-slate-900">Oferta Académica</h1>
                        <p class="text-slate-500">Selecciona tu grupo preferido. <span class="text-indigo-600 font-medium">El sistema detectará choques automáticamente.</span></p>
                    </div>

                    <div class="space-y-12">
                        @foreach($subjectsBySemester as $semester => $subjects)
                            <div class="relative pl-6 border-l-4 border-indigo-100">
                                <span class="absolute -left-[14px] top-0 bg-white border-2 border-indigo-100 text-indigo-600 font-bold rounded-full w-6 h-6 flex items-center justify-center text-xs">
                                    {{ $semester }}
                                </span>
                                <h2 class="text-lg font-bold text-slate-700 mb-4 uppercase tracking-wide">Semestre {{ $semester }}</h2>
                                
                                <div class="grid grid-cols-1 gap-5">
                                    @foreach($subjects as $subject)
                                        @php
                                            $isPassed = in_array($subject->id, $passedIds);
                                            $isFailed = in_array($subject->id, $failedIds);
                                            
                                            // Lógica de Prerrequisitos
                                            $missingPrereqs = $subject->prerequisites->filter(function($prereq) use ($passedIds) {
                                                return !in_array($prereq->id, $passedIds);
                                            });
                                            $isBlocked = $missingPrereqs->isNotEmpty() && !$isPassed;
                                        @endphp

                                        <div class="bg-white rounded-xl border shadow-sm transition-all overflow-hidden
                                            {{ $isPassed ? 'card-passed' : '' }}
                                            {{ $isBlocked ? 'card-blocked' : 'border-slate-200' }}
                                            {{ $isFailed ? 'ring-2 ring-red-100 border-red-200' : '' }}
                                        ">
                                            <div class="p-4 flex justify-between items-start bg-slate-50/50 border-b border-slate-100">
                                                <div>
                                                    <div class="flex items-center gap-2">
                                                        <h3 class="font-bold text-slate-900">{{ $subject->name }}</h3>
                                                        @if($isFailed) <span class="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded">REPROBADA</span> @endif
                                                    </div>
                                                    <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $subject->code }} • {{ $subject->credits }} Créditos</p>
                                                </div>
                                                
                                                @if($isPassed)
                                                    <span class="text-green-600 font-bold text-xs bg-green-50 px-2 py-1 rounded border border-green-100">Aprobada</span>
                                                @elseif($isBlocked)
                                                    <div class="text-right">
                                                        <span class="text-slate-400 text-xs font-bold bg-slate-100 px-2 py-1 rounded">Bloqueada</span>
                                                        <p class="text-[10px] text-red-500 mt-1 font-medium">Falta: {{ $missingPrereqs->pluck('name')->join(', ') }}</p>
                                                    </div>
                                                @endif
                                            </div>

                                            @if(!$isPassed && !$isBlocked)
                                                <div class="p-3 space-y-2">
                                                    @forelse($subject->sections as $section)
                                                        @php
                                                            // JSON de horario para JS
                                                            $scheduleData = $section->schedules->map(function($s) {
                                                                return [
                                                                    'day' => strtolower($s->day_of_week),
                                                                    'start' => (int)substr($s->start_time, 0, 2)*60 + (int)substr($s->start_time, 3, 2),
                                                                    'end' => (int)substr($s->end_time, 0, 2)*60 + (int)substr($s->end_time, 3, 2),
                                                                ];
                                                            });
                                                        @endphp

                                                        <label class="block relative cursor-pointer group select-none">
                                                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" 
                                                                class="subject-check peer sr-only"
                                                                data-subject-id="{{ $subject->id }}"
                                                                data-subject-name="{{ $subject->name }}"
                                                                data-schedule="{{ $scheduleData->toJson() }}">
                                                            
                                                            <div class="custom-checkbox-container p-3 rounded-lg border border-slate-200 bg-white transition-all hover:border-indigo-300 flex justify-between items-center">
                                                                <div>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="text-sm font-bold text-slate-800">Grupo {{ $section->group_code }}</span>
                                                                        <span class="text-xs text-slate-500">| {{ $section->teacher->name }}</span>
                                                                    </div>
                                                                    <div class="text-xs text-indigo-600 mt-1 font-medium flex flex-wrap gap-2">
                                                                        @foreach($section->schedules as $time)
                                                                            <span class="bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100">
                                                                                {{ substr($time->day_of_week, 0, 3) }} {{ \Carbon\Carbon::parse($time->start_time)->format('H:i') }}-{{ \Carbon\Carbon::parse($time->end_time)->format('H:i') }}
                                                                            </span>
                                                                        @endforeach
                                                                    </div>
                                                                    <div class="conflict-msg hidden text-[11px] text-red-600 font-bold mt-1.5 flex items-center bg-red-50 p-1 rounded w-fit">
                                                                        ⚠️ <span class="conflict-text ml-1"></span>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </label>
                                                    @empty
                                                        <div class="text-center py-2 text-xs text-slate-400 italic">No hay grupos disponibles.</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:w-80 xl:w-96 flex-shrink-0">
                    <div class="sticky top-24 bg-white rounded-xl shadow-xl border border-slate-200 overflow-hidden ring-1 ring-slate-900/5">
                        
                        <div class="bg-slate-900 p-5 text-white">
                            <h2 class="font-bold text-lg">Tu Carga Académica</h2>
                            <p class="text-slate-400 text-xs mt-1">Configura tu meta y selecciona prioridades.</p>
                        </div>
                        
                        <div class="p-5">
                            
                            <div class="mb-6">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">
                                    ¿Cuántas materias quieres? (Máx 8)
                                </label>
                                <div class="flex items-center">
                                    <input type="number" name="target_count" id="target_count_input" value="6" min="1" max="8"
                                        class="w-full text-center font-bold text-xl border-2 border-slate-200 rounded-lg py-2 focus:border-indigo-500 focus:ring-0 text-indigo-700 outline-none transition">
                                </div>
                            </div>

                            <div class="flex justify-between items-center mb-4 text-sm">
                                <span class="text-slate-600 font-medium">Seleccionadas:</span>
                                <span class="font-bold text-slate-900"><span id="counter" class="text-indigo-600 text-lg">0</span> / <span id="limit-display">6</span></span>
                            </div>

                            <div id="selected-list" class="space-y-2 mb-6 max-h-60 overflow-y-auto custom-scroll pr-1">
                                <p class="text-xs text-slate-400 text-center italic py-4">No has seleccionado materias.</p>
                            </div>

                            <div id="limit-warning" class="hidden bg-orange-50 text-orange-800 text-xs p-3 rounded-lg mb-4 border border-orange-200 font-medium flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                <span>Llegaste a tu meta. Aumenta el número arriba si quieres agregar más.</span>
                            </div>

                            <button type="submit" id="submit-btn" class="w-full bg-indigo-600 text-white py-3.5 rounded-lg font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 flex justify-center items-center group disabled:opacity-50 disabled:cursor-not-allowed">
                                Generar Horario
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ELEMENTOS
            const targetInput = document.getElementById('target_count_input');
            const limitDisplay = document.getElementById('limit-display');
            const checkboxes = document.querySelectorAll('.subject-check');
            const counterEl = document.getElementById('counter');
            const selectedListEl = document.getElementById('selected-list');
            const limitWarning = document.getElementById('limit-warning');
            const ABSOLUTE_MAX = 8; // Tope del sistema

            // --- 1. DETECCIÓN DE CHOQUES ---
            function findTimeConflict(scheduleA, selections) {
                for (const selection of selections) {
                    for (const blockA of scheduleA) {
                        for (const blockB of selection.schedule) {
                            if (blockA.day === blockB.day) {
                                // Fórmula de intersección: (StartA < EndB) && (EndA > StartB)
                                if (blockA.start < blockB.end && blockA.end > blockB.start) {
                                    return selection.name; 
                                }
                            }
                        }
                    }
                }
                return null;
            }

            // --- 2. ACTUALIZACIÓN VISUAL ---
            function updateState() {
                // 2a. Leer el límite dinámico del usuario
                let userLimit = parseInt(targetInput.value) || 1;
                if (userLimit > ABSOLUTE_MAX) userLimit = ABSOLUTE_MAX; 
                if (userLimit < 1) userLimit = 1;
                
                // Actualizar visualización del límite
                limitDisplay.innerText = userLimit;

                // 2b. Recolectar Selecciones Activas
                const checkedBoxes = document.querySelectorAll('.subject-check:checked');
                const selections = [];
                const uniqueSubjects = new Set();

                checkedBoxes.forEach(cb => {
                    uniqueSubjects.add(cb.dataset.subjectId);
                    selections.push({
                        id: cb.value,
                        subjectId: cb.dataset.subjectId,
                        name: cb.dataset.subjectName,
                        schedule: JSON.parse(cb.dataset.schedule),
                        domElement: cb
                    });
                });

                const currentCount = uniqueSubjects.size;
                counterEl.innerText = currentCount;

                // 2c. Renderizar Lista Lateral
                if (currentCount > 0) {
                    selectedListEl.innerHTML = selections.map(s => `
                        <div class="flex justify-between items-center text-xs bg-slate-50 p-2 rounded border border-slate-200 group hover:border-red-200 transition">
                            <span class="font-semibold text-slate-700 truncate w-40" title="${s.name}">${s.name}</span>
                            <button type="button" class="text-slate-400 hover:text-red-500 font-bold px-2" onclick="document.querySelector('input[value=&quot;${s.id}&quot;]').click()">✕</button>
                        </div>
                    `).join('');
                } else {
                    selectedListEl.innerHTML = '<p class="text-xs text-slate-400 text-center italic py-4">No has seleccionado materias.</p>';
                }

                // 2d. Verificar Límite Alcanzado
                const limitReached = currentCount >= userLimit;
                if(limitReached) limitWarning.classList.remove('hidden');
                else limitWarning.classList.add('hidden');

                // 2e. Recorrer TODOS los inputs para Validar (Bloquear/Desbloquear)
                checkboxes.forEach(cb => {
                    const isChecked = cb.checked;
                    const subjectId = cb.dataset.subjectId;
                    const mySchedule = JSON.parse(cb.dataset.schedule);
                    
                    // Elementos visuales hermanos
                    const container = cb.nextElementSibling; // El div custom-checkbox-container
                    const conflictMsg = container.querySelector('.conflict-msg');
                    const conflictText = container.querySelector('.conflict-text');

                    // --- RESETEO INICIAL ---
                    cb.disabled = false;
                    container.classList.remove('opacity-50', 'cursor-not-allowed', 'bg-slate-100');
                    conflictMsg.classList.add('hidden');

                    // --- REGLA 1: CHOQUES ---
                    // Solo validamos choque si NO estoy checado (conmigo mismo no choco)
                    if (!isChecked) {
                        const conflictName = findTimeConflict(mySchedule, selections);
                        if (conflictName) {
                            cb.disabled = true;
                            container.classList.add('opacity-50', 'cursor-not-allowed', 'bg-slate-100');
                            conflictMsg.classList.remove('hidden');
                            conflictText.innerText = "Choca con " + conflictName;
                            return; // Ya está bloqueado, pasamos al siguiente
                        }
                    }

                    // --- REGLA 2: LÍMITE ALCANZADO ---
                    // Si llegamos al límite, bloqueo todo lo que NO sea de mis materias ya seleccionadas
                    if (limitReached && !uniqueSubjects.has(subjectId)) {
                        cb.disabled = true;
                        container.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                });
            }

            // --- 3. EVENT HANDLERS ---
            
            // Cambio en Checkboxes
            function handleCheck(e) {
                const clicked = e.target;
                // Radio Button Behavior: Si marco Grupo A, desmarco Grupo B de la misma materia
                if (clicked.checked) {
                    const subId = clicked.dataset.subjectId;
                    const siblings = document.querySelectorAll(`.subject-check[data-subject-id="${subId}"]`);
                    siblings.forEach(sib => {
                        if (sib !== clicked) sib.checked = false;
                    });
                }
                updateState();
            }

            // Cambio en Input de Meta
            targetInput.addEventListener('input', updateState);
            targetInput.addEventListener('change', updateState);

            // Asignación de eventos única
            checkboxes.forEach(cb => cb.addEventListener('change', handleCheck));

            // Ejecución inicial
            updateState();
        });
    </script>
</body>
</html>