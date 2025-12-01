<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Horario</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8 min-h-screen">
    <div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-md">
        
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Inscripción de Materias</h1>
                <p class="text-gray-600">Alumno: <span class="font-bold text-indigo-600">{{ $user->name }}</span></p>
            </div>
            <a href="{{ route('home') }}" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded transition">
                ← Cambiar Alumno
            </a>
        </div>

        <form action="{{ route('schedule.generate', $user->id) }}" method="POST">
            @csrf

            <div class="bg-indigo-50 p-6 rounded-lg mb-8 border border-indigo-100 flex items-center justify-between">
                <div>
                    <label class="block text-lg font-bold text-indigo-900">Meta de Materias</label>
                    <p class="text-sm text-gray-600 max-w-lg">
                        Elige tus prioritarias abajo. El sistema rellenará los huecos hasta llegar a este número.
                    </p>
                </div>
                <div class="flex items-center">
                    <input type="number" id="target_count" name="target_count" value="6" min="1" max="10" 
                        class="w-20 px-4 py-2 border border-indigo-300 rounded-lg text-2xl font-bold text-center text-indigo-700 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    <span class="ml-2 text-gray-500 font-medium">materias</span>
                </div>
            </div>

            <div class="space-y-8">
                @foreach($subjectsBySemester as $semester => $subjects)
                    <div class="border-b pb-4">
                        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                            <span class="bg-gray-200 text-gray-700 w-8 h-8 rounded-full flex items-center justify-center text-sm mr-2">{{ $semester }}</span>
                            Semestre {{ $semester }}
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($subjects as $subject)
                                @php
                                    $isPassed = in_array($subject->id, $passedIds);
                                    $isFailed = in_array($subject->id, $failedIds);
                                @endphp

                                <label class="subject-card relative flex flex-col p-4 border rounded-lg cursor-pointer transition select-none
                                    {{ $isPassed ? 'bg-green-50 border-green-200 opacity-60 grayscale' : 'hover:shadow-md hover:border-indigo-300 bg-white' }}
                                    {{ $isFailed ? 'bg-red-50 border-red-300 ring-1 ring-red-200' : 'border-gray-200' }}
                                ">
                                    <div class="flex items-start justify-between w-full mb-2">
                                        <span class="font-bold text-gray-800 text-sm leading-tight">{{ $subject->name }}</span>
                                        
                                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                            class="subject-checkbox mt-1 w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                                            {{ $isPassed ? 'disabled checked' : '' }}
                                        >
                                    </div>

                                    <div class="text-xs text-gray-500 mt-auto">
                                        {{ $subject->code }} • {{ $subject->credits }} Créditos
                                        
                                        @if($isFailed) 
                                            <div class="mt-2 text-red-600 font-bold bg-red-100 px-2 py-1 rounded w-max">⚠️ REPROBADA</div> 
                                        @endif
                                        @if($isPassed) 
                                            <div class="mt-2 text-green-700 font-bold">✓ Aprobada</div> 
                                        @endif
                                        
                                        @if(!$isPassed && $subject->prerequisites->isNotEmpty())
                                            <div class="mt-2 pt-2 border-t border-gray-100 text-orange-600">
                                                <span class="font-semibold">Requiere:</span> 
                                                {{ $subject->prerequisites->pluck('name')->join(', ') }}
                                            </div>
                                        @endif
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 sticky bottom-4 bg-white/90 backdrop-blur p-4 shadow-2xl border border-gray-200 text-center rounded-xl z-10">
                <button type="submit" class="bg-indigo-600 text-white px-10 py-3 rounded-lg font-bold text-lg hover:bg-indigo-700 transition shadow-lg transform hover:-translate-y-1">
                    Generar Horario Óptimo
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const limitInput = document.getElementById('target_count');
            const checkboxes = document.querySelectorAll('.subject-checkbox');

            function updateCheckboxes() {
                // 1. Obtener el límite actual (si está vacío asumimos 10)
                const limit = parseInt(limitInput.value) || 10;

                // 2. Contar cuántas ha marcado el usuario
                // IMPORTANTE: Filtramos con ':not(:disabled)' para NO contar las materias ya aprobadas (que están disabled)
                // Solo nos importa contar las que el usuario está eligiendo AHORA.
                const checkedCount = document.querySelectorAll('.subject-checkbox:checked:not(:disabled)').length;

                // 3. Aplicar lógica a cada checkbox
                checkboxes.forEach(cb => {
                    // Si la casilla está deshabilitada por el servidor (ya aprobada), la ignoramos
                    if (cb.disabled && cb.hasAttribute('checked')) return;

                    // Si NO está marcada...
                    if (!cb.checked) {
                        // ... y ya llegamos al límite
                        if (checkedCount >= limit) {
                            cb.disabled = true; // La bloqueamos
                            cb.closest('.subject-card').classList.add('opacity-50', 'cursor-not-allowed'); // Efecto visual gris
                        } else {
                            // Si todavía hay cupo, la desbloqueamos
                            cb.disabled = false;
                            cb.closest('.subject-card').classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }
                });
            }

            // Escuchar cambios en el input numérico y en los checkboxes
            limitInput.addEventListener('input', updateCheckboxes);
            limitInput.addEventListener('change', updateCheckboxes); // Para las flechitas del input number
            checkboxes.forEach(cb => cb.addEventListener('change', updateCheckboxes));

            // Ejecutar al cargar por si hay datos prellenados
            updateCheckboxes();
        });
    </script>
</body>
</html>