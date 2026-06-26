<?php

use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void { $this->resetPage(); }

    public function toggleActive(string $id): void
    {
        $user = User::findOrFail($id);
        $user->update(['is_active' => ! $user->is_active]);
        unset($this->users);
    }

    public function toggleAdmin(string $id): void
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) { return; }
        $user->update(['is_admin' => ! $user->is_admin]);
        unset($this->users);
    }

    #[Computed]
    public function users()
    {
        return User::when($this->search, fn ($q) => $q->where(function ($q) {
            $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($this->search).'%'])
              ->orWhereRaw('LOWER(email) LIKE ?', ['%'.strtolower($this->search).'%']);
        }))->withCount('purchases', 'products')->latest()->paginate(20);
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">Users</h1>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search..." icon="magnifying-glass" clearable size="sm" class="w-56" />
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-800">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-800 bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">User</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Balance</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Products</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Purchases</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-widest text-zinc-500">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-widest text-zinc-500">Role</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                @forelse ($this->users as $user)
                    <tr wire:key="{{ $user->id }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-8 w-8 shrink-0 overflow-hidden rounded-full bg-zinc-700">
                                    @if ($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}" alt="" class="block h-8 w-8 object-cover">
                                    @else
                                        <div class="flex h-8 w-8 items-center justify-center text-xs font-medium text-white">{{ $user->initials() }}</div>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-zinc-200">{{ $user->name }}</p>
                                    <p class="text-xs text-zinc-600">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right text-zinc-400">${{ number_format($user->balance, 2) }}</td>
                        <td class="px-4 py-3 text-right text-zinc-400">{{ $user->products_count }}</td>
                        <td class="px-4 py-3 text-right text-zinc-400">{{ $user->purchases_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive('{{ $user->id }}')" class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium transition
                                {{ $user->is_active ? 'bg-emerald-500/10 text-emerald-400 hover:bg-red-500/10 hover:text-red-400' : 'bg-zinc-800 text-zinc-500 hover:bg-emerald-500/10 hover:text-emerald-400' }}">
                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($user->id !== auth()->id())
                                <button wire:click="toggleAdmin('{{ $user->id }}')" class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium transition
                                    {{ $user->is_admin ? 'bg-violet-500/10 text-violet-400 hover:bg-zinc-800 hover:text-zinc-400' : 'bg-zinc-800 text-zinc-600 hover:bg-violet-500/10 hover:text-violet-400' }}">
                                    {{ $user->is_admin ? 'Admin' : 'User' }}
                                </button>
                            @else
                                <span class="inline-flex rounded-full bg-violet-500/10 px-2 py-0.5 text-xs font-medium text-violet-400">You</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-zinc-600">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-zinc-600">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $this->users->links() }}</div>
</div>
