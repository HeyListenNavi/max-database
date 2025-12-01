<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Horario Oficial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ccc; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8 min-h-screen text-gray-800">

    {{-- LÓGICA DE PROCESAMIENTO CORREGIDA --}}
    @php
        // 1. Convertir todo a array para evitar problemas de objetos
        $scheduleArray = json_decode(json_encode($schedule), true);
        $totalClasesEncontradas = 0;

        // 2. Configuración de horas y días
        $startHour = 6;
        $endHour = 22;
        $hoursRange = range($startHour, $endHour);
        
        $weekDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $daysEs = [
            'monday' => 'Lunes', 'tuesday' => 'Martes', 'wednesday' => 'Miércoles', 
            'thursday' => 'Jueves', 'friday' => 'Viernes', 'saturday' => 'Sábado', 'sunday' => 'Domingo'
        ];

        // 3. Inicializar matriz vacía
        $calendar = [];
        foreach ($weekDays as $day) {
            foreach ($hoursRange as $h) {
                $calendar[$day][$h] = null;
            }
        }

        // 4. Llenar matriz
        foreach ($scheduleArray as $section) {
            if (!empty($section['schedules']) && is_array($section['schedules'])) {
                foreach ($section['schedules'] as $time) {
                    $totalClasesEncontradas++;

                    // Datos crudos y normalización
                    $dayRaw = $time['day_of_week'] ?? '';
                    $startRaw = $time['start_time'] ?? '00:00';
                    $endRaw = $time['end_time'] ?? '00:00';

                    $dayKey = strtolower(trim($dayRaw));
                    $hStart = (int) substr($startRaw, 0, 2); 
                    $hEnd   = (int) substr($endRaw, 0, 2);   

                    // Llenado de celdas
                    for ($h = $hStart; $h < $hEnd; $h++) {
                        // CORRECCIÓN AQUÍ: Usamos array_key_exists en lugar de isset
                        if (array_key_exists($dayKey, $calendar) && array_key_exists($h, $calendar[$dayKey])) {
                            
                            $calendar[$dayKey][$h] = [
                                'name'    => $section['subject']['name'] ?? 'Materia',
                                'code'    => $section['subject']['code'] ?? '---',
                                'group'   => $section['group_code'] ?? '?',
                                'teacher' => $section['teacher']['name'] ?? 'Profesor',
                                'color'   => 'bg-indigo-100 border-indigo-300 text-indigo-900'
                            ];
                        }
                    }
                }
            }
        }
    @endphp

    <div class="max-w-7xl mx-auto">
        <div class="mb-6 flex justify-between items-end no-print">
            <div>
                <h1 class="text-3xl font-bold text-indigo-900">Horario Oficial</h1>
                <p class="text-gray-600">Alumno: <span class="font-bold">{{ $user->name }}</span></p>
                <p class="text-xs text-gray-400 mt-1">
                    Materias: {{ count($scheduleArray) }} | Bloques: {{ $totalClasesEncontradas }}
                </p>
            </div>
            <div class="space-x-4">
                <a href="{{ route('schedule.index', $user->id) }}" class="text-indigo-600 font-semibold hover:underline">← Reintentar</a>
                <a href="{{ route('home') }}" class="text-gray-500 font-semibold hover:underline border-l pl-4 border-gray-300">Salir</a>
            </div>
        </div>

        @if(!empty($warnings))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded shadow-sm no-print">
                <h3 class="text-sm font-bold text-yellow-800 uppercase mb-2">Advertencias</h3>
                <ul class="text-sm text-yellow-700 space-y-1 list-disc list-inside">
                    @foreach($warnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(count($scheduleArray) > 0)
            <div class="bg-white shadow-xl rounded-lg overflow-hidden border border-gray-200 print-shadow-none">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] border-collapse table-fixed">
                        <thead>
                            <tr class="bg-gray-800 text-white text-sm uppercase tracking-wider">
                                <th class="py-3 px-2 w-16 border-r border-gray-700">Hora</th>
                                @foreach($weekDays as $day)
                                    <th class="py-3 px-2 border-r border-gray-700 last:border-0">
                                        {{ $daysEs[$day] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @foreach($hoursRange as $hour)
                                <tr class="border-b border-gray-200 hover:bg-gray-50 h-16">
                                    <td class="py-2 px-2 text-center font-bold text-gray-500 border-r border-gray-200 bg-gray-50 text-xs align-middle">
                                        {{ sprintf('%02d:00', $hour) }}
                                    </td>

                                    @foreach($weekDays as $day)
                                        @php $clase = $calendar[$day][$hour] ?? null; @endphp
                                        
                                        <td class="p-1 border-r border-gray-200 align-top relative">
                                            @if($clase)
                                                <div class="{{ $clase['color'] }} w-full h-full rounded p-1.5 border shadow-sm flex flex-col justify-between overflow-hidden cursor-pointer hover:shadow-md transition">
                                                    <div>
                                                        <div class="font-bold leading-tight text-[11px] uppercase mb-0.5">{{ $clase['name'] }}</div>
                                                        <div class="text-[10px] text-indigo-800 font-mono font-bold">{{ $clase['code'] }} • Gpo {{ $clase['group'] }}</div>
                                                    </div>
                                                    <div class="text-[9px] text-gray-600 truncate mt-1">
                                                        {{ Str::limit($clase['teacher'], 18) }}
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

            <div class="mt-8 text-center pb-12 no-print">
                <button onclick="window.print()" class="bg-gray-800 text-white px-8 py-3 rounded-full font-bold hover:bg-gray-900 shadow-lg transition flex items-center mx-auto gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir Horario
                </button>
            </div>

        @else
            <div class="bg-white p-12 rounded-lg shadow text-center border border-gray-200 mt-10">
                <p class="text-gray-500 text-lg">No se pudo generar un horario válido.</p>
                <a href="{{ route('schedule.index', $user->id) }}" class="text-indigo-600 font-bold hover:underline">Intentar de nuevo</a>
            </div>
        @endif
    </div>
</body>
</html>