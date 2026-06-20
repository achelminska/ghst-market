<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Validate('required|numeric|min:1|max:10000')]
    public string $amount = '';

    public bool $topped = false;

    public function topUp(): void
    {
        $this->validate();

        Auth::user()->increment('balance', (float) $this->amount);

        $this->amount = '';
        $this->topped = true;
        unset($this->transactions);
    }

    #[Computed]
    public function transactions()
    {
        $userId = Auth::id();

        $purchases = Auth::user()
            ->purchases()
            ->with('product')
            ->latest('purchased_at')
            ->get()
            ->map(fn ($p) => [
                'date'        => $p->purchased_at,
                'description' => 'Purchased: '.$p->product->title,
                'amount'      => -$p->price_paid,
                'type'        => 'out',
            ]);

        $sales = \App\Models\Purchase::whereHas('product', fn ($q) => $q->where('user_id', $userId))
            ->with('product')
            ->latest('purchased_at')
            ->get()
            ->map(fn ($p) => [
                'date'        => $p->purchased_at,
                'description' => 'Sale: '.$p->product->title,
                'amount'      => +$p->price_paid,
                'type'        => 'in',
            ]);

        return $purchases->merge($sales)->sortByDesc('date')->values();
    }
};
?>

<div>
    <flux:main>
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">Wallet</h1>
            <p class="mt-1 text-sm text-zinc-500">Manage your virtual balance.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Balance + top-up --}}
            <div class="space-y-4">
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                    <p class="text-xs font-medium uppercase tracking-widest text-zinc-500">Current balance</p>
                    <p class="mt-3 text-4xl font-bold text-white">${{ number_format(Auth::user()->balance, 2) }}</p>
                </div>

                <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-6">
                    <p class="mb-4 text-sm font-semibold text-white">Add funds</p>

                    @if ($topped)
                        <div class="mb-4 rounded-xl bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
                            Funds added successfully!
                        </div>
                    @endif

                    <form wire:submit="topUp" class="space-y-4">
                        <div>
                            <flux:input
                                wire:model="amount"
                                type="number"
                                min="1"
                                max="10000"
                                step="0.01"
                                placeholder="0.00"
                                label="Amount (USD)"
                            />
                            @error('amount')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-3 gap-2">
                            @foreach ([10, 25, 50, 100, 200, 500] as $preset)
                                <button
                                    type="button"
                                    wire:click="$set('amount', '{{ $preset }}')"
                                    class="rounded-lg border border-zinc-700 py-1.5 text-xs font-medium text-zinc-400 transition hover:border-zinc-500 hover:text-white"
                                >
                                    ${{ $preset }}
                                </button>
                            @endforeach
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-xl bg-white py-2.5 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-100"
                        >
                            Add funds
                        </button>
                    </form>
                </div>
            </div>

            {{-- Transaction history --}}
            <div class="lg:col-span-2">
                <h2 class="mb-4 text-sm font-semibold text-white">Transaction history</h2>

                @if ($this->transactions->isEmpty())
                    <div class="rounded-2xl border border-zinc-800 bg-zinc-900 py-12 text-center">
                        <p class="text-sm text-zinc-500">No transactions yet.</p>
                    </div>
                @else
                    <div class="overflow-hidden rounded-2xl border border-zinc-800">
                        <table class="w-full text-sm">
                            <thead class="border-b border-zinc-800 bg-zinc-900">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Description</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                                @foreach ($this->transactions as $tx)
                                    <tr class="transition hover:bg-zinc-900/50">
                                        <td class="px-4 py-3 text-zinc-500">
                                            {{ $tx['date']->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-3 text-zinc-300">
                                            {{ $tx['description'] }}
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold {{ $tx['type'] === 'in' ? 'text-emerald-400' : 'text-red-400' }}">
                                            {{ $tx['type'] === 'in' ? '+' : '-' }}${{ number_format(abs($tx['amount']), 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </flux:main>
</div>
