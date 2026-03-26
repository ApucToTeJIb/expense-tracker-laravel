@php
$categoryTotals = [];
foreach ($expenses as $expense) {
$cat = $expense->category;
if (!isset($categoryTotals[$cat])) {
$categoryTotals[$cat] = 0;
}
$categoryTotals[$cat] += $expense->amount;
}

$totalAmount = array_sum($categoryTotals);

$chartLabels = json_encode(array_keys($categoryTotals));
$chartData = json_encode(array_values($categoryTotals));

$categoryColors = [
'Еда' => '#6366f1',
'Транспорт' => '#f59e0b',
'Развлечения' => '#10b981',
'Жилье' => '#3b82f6',
'Прочее' => '#ec4899',
];
$chartColors = json_encode(array_map(
fn($cat) => $categoryColors[$cat] ?? '#94a3b8',
array_keys($categoryTotals)
));

$sortedExpenses = $expenses->sortByDesc('spent_at');

function formatMoney($amount) {
return number_format($amount, 0, '.', ' ') . ' ₸';
}

$categoryIcons = [
'Еда' => '🍔',
'Транспорт' => '🚗',
'Развлечения' => '🎭',
'Жилье' => '🏠',
'Прочее' => '📦',
];
@endphp

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Учёт расходов</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #0f172a;
        }

        .glass {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(12px);
        }

        .input-field {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #f1f5f9;
            transition: all 0.2s;
        }

        .input-field:focus {
            outline: none;
            border-color: #6366f1;
            background: rgba(99, 102, 241, 0.08);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        .input-field option {
            background: #1e293b;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: transform 0.2s, border-color 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            border-color: rgba(99, 102, 241, 0.4);
        }

        .table-row:hover td {
            background: rgba(99, 102, 241, 0.06);
        }

        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
    </style>
</head>

<body class="min-h-screen text-slate-200 font-sans">

    <div class="max-w-7xl mx-auto px-4 py-10 space-y-8">

        <!-- header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">💸 Мои расходы</h1>
                <p class="text-slate-400 mt-1 text-sm">Отслеживайте и анализируйте свои траты</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-500 uppercase tracking-widest">Всего потрачено</p>
                <p class="text-2xl font-bold text-indigo-400">{{ formatMoney($totalAmount) }}</p>
            </div>
        </div>

        <!-- form -->
        <div class="glass rounded-2xl p-6">
            <h2 class="text-lg font-semibold text-white mb-5 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-500/20 rounded-lg flex items-center justify-center text-indigo-400 text-sm">+</span>
                Новый расход
            </h2>

            @if ($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="lg:col-span-1">
                        <label class="block text-xs text-slate-400 mb-1.5 uppercase tracking-wider">Название</label>
                        <input
                            type="text"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="Кофе, такси…"
                            required
                            class="input-field w-full rounded-xl px-4 py-2.5 text-sm placeholder-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 uppercase tracking-wider">Сумма (₸)</label>
                        <input
                            type="number"
                            name="amount"
                            value="{{ old('amount') }}"
                            placeholder="Введите сумму"
                            min="0"
                            step="0.01"
                            required
                            class="input-field w-full rounded-xl px-4 py-2.5 text-sm placeholder-slate-500">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 uppercase tracking-wider">Категория</label>
                        <select name="category" required class="input-field w-full rounded-xl px-4 py-2.5 text-sm">
                            <option value="" disabled {{ old('category') ? '' : 'selected' }}>Выберите…</option>
                            @foreach (['Еда','Транспорт','Развлечения','Жилье','Прочее'] as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                {{ ($categoryIcons[$cat] ?? '') }} {{ $cat }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-400 mb-1.5 uppercase tracking-wider">Дата</label>
                        <input
                            type="date"
                            name="spent_at"
                            value="{{ old('spent_at', date('Y-m-d')) }}"
                            required
                            class="input-field w-full rounded-xl px-4 py-2.5 text-sm">
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-500 active:bg-indigo-700
                           text-white font-medium rounded-xl text-sm transition-all
                           shadow-lg shadow-indigo-900/30 hover:shadow-indigo-700/40">
                        Добавить расход →
                    </button>
                </div>
            </form>
        </div>

        <!-- analytics -->
        @if ($totalAmount > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Stat cards --}}
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-widest">Аналитика по категориям</h2>

                {{-- Total --}}
                <div class="stat-card rounded-2xl px-5 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📊</span>
                        <div>
                            <p class="text-xs text-slate-400">Всего расходов</p>
                            <p class="font-semibold text-white">{{ $expenses->count() }} записей</p>
                        </div>
                    </div>
                    <span class="text-xl font-bold text-indigo-400">{{ formatMoney($totalAmount) }}</span>
                </div>

                {{-- Per category --}}
                @foreach ($categoryTotals as $cat => $sum)
                @php $pct = $totalAmount > 0 ? round($sum / $totalAmount * 100) : 0; @endphp
                <div class="stat-card rounded-2xl px-5 py-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="text-lg">{{ $categoryIcons[$cat] ?? '📦' }}</span>
                            <span class="text-sm font-medium text-slate-200">{{ $cat }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-white">{{ formatMoney($sum) }}</span>
                            <span class="text-xs text-slate-500 ml-2">{{ $pct }}%</span>
                        </div>
                    </div>
                    <div class="h-1.5 bg-white/5 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-700"
                            style="width: {{ $pct }}%; background: {{ $categoryColors[$cat] ?? '#94a3b8' }}"></div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pie Chart --}}
            <div class="glass rounded-2xl p-5 flex flex-col">
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-widest mb-4">Диаграмма</h2>
                <div class="flex-1 flex items-center justify-center min-h-[240px]">
                    <canvas id="expenseChart" width="400" height="400"></canvas>
                </div>
            </div>

        </div>
        @endif

        <!-- table -->
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-400 uppercase tracking-widest">История расходов</h2>
                <span class="text-xs text-slate-500">{{ $expenses->count() }} записей</span>
            </div>

            @if ($expenses->isEmpty())
            <div class="py-16 text-center">
                <p class="text-4xl mb-3">🪹</p>
                <p class="text-slate-400">Расходов пока нет. Добавьте первый!</p>
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-white/5">
                            <th class="text-left px-6 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium">Дата</th>
                            <th class="text-left px-6 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium">Название</th>
                            <th class="text-left px-6 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium">Категория</th>
                            <th class="text-right px-6 py-3 text-xs text-slate-500 uppercase tracking-wider font-medium">Сумма</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($sortedExpenses as $expense)
                        <tr class="table-row transition-colors">
                            <td class="px-6 py-3.5 text-slate-400 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($expense->spent_at)->format('d.m.Y') }}
                            </td>
                            <td class="px-6 py-3.5 text-white font-medium">{{ $expense->title }}</td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium"
                                    style="background: {{ ($categoryColors[$expense->category] ?? '#94a3b8') }}22;
                                               color:      {{ $categoryColors[$expense->category]  ?? '#94a3b8' }};">
                                    {{ $categoryIcons[$expense->category] ?? '📦' }}
                                    {{ $expense->category }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-right font-bold text-white whitespace-nowrap">
                                {{ formatMoney($expense->amount) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-white/10">
                            <td colspan="3" class="px-6 py-4 text-sm text-slate-400 font-medium">Итого</td>
                            <td class="px-6 py-4 text-right text-indigo-400 font-bold text-base">
                                {{ formatMoney($totalAmount) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>

    </div>

    <!-- chart.js -->
    @if ($totalAmount > 0)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('expenseChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo $chartLabels; ?>,
                    datasets: [{
                        data: <?php echo $chartData; ?>,
                        backgroundColor: <?php echo $chartColors; ?>,
                        borderWidth: 2,
                        borderColor: 'rgba(15,23,42,0.8)',
                        hoverBorderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true,
                    cutout: '62%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#94a3b8',
                                padding: 14,
                                font: {
                                    size: 12
                                },
                                boxWidth: 10,
                                boxHeight: 10,
                                borderRadius: 3,
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15,23,42,0.95)',
                            borderColor: 'rgba(255,255,255,0.08)',
                            borderWidth: 1,
                            titleColor: '#f1f5f9',
                            bodyColor: '#94a3b8',
                            padding: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.parsed;
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = Math.round(val / total * 100);
                                    const formatted = new Intl.NumberFormat('ru-RU').format(val);
                                    return `  ${formatted} ₸  (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endif

</body>

</html>