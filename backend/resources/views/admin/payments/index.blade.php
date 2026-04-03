<x-layouts.admin title="Payments">
    <x-slot:header>
        Payments
    </x-slot:header>

    @if ($payments->isEmpty())
        <x-card variant="subtle" class="text-center py-16">
            <x-heroicon-o-banknotes class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-400">No payments yet</p>
            <p class="text-sm text-gray-400 mt-1">Payments appear when organizations complete transactions.</p>
        </x-card>
    @else
        <div class="space-y-3">
            @foreach ($payments as $payment)
                @php
                    $statusColor = match($payment->status) {
                        'paid', 'completed' => 'bg-emerald-100 text-emerald-700',
                        'pending' => 'bg-yellow-100 text-yellow-700',
                        'failed' => 'bg-red-100 text-red-700',
                        'refunded' => 'bg-blue-100 text-blue-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <x-card class="p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center shrink-0 mt-0.5">
                                <x-heroicon-o-banknotes class="w-5 h-5 text-gray-400" />
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-semibold text-gray-900">
                                    {{ $payment->organization->name ?? 'Unknown Org' }}
                                </h2>
                                <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-1.5">
                                    <span class="font-mono font-semibold">{{ strtoupper($payment->currency ?? 'MYR') }} {{ number_format($payment->amount, 2) }}</span>
                                    @if ($payment->method)
                                        <span class="text-gray-300">·</span>
                                        {{ ucfirst($payment->method) }}
                                    @endif
                                </p>
                                <div class="text-xs text-gray-400 mt-1">
                                    @if ($payment->reference_number)
                                        <p>Ref: {{ $payment->reference_number }}</p>
                                    @endif
                                    <p>
                                        {{ $payment->paid_at?->format('d M Y, H:i') ?? 'Not paid' }}
                                        @if ($payment->paid_at)
                                            <span class="text-gray-300 mx-0.5">·</span>
                                            {{ $payment->paid_at->diffForHumans() }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="shrink-0">
                            <span class="text-xs font-medium rounded-full px-2.5 py-1 {{ $statusColor }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $payments->links() }}
        </div>
    @endif
</x-layouts.admin>
