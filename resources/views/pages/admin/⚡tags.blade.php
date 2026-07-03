<?php

use App\Models\Tag;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts.admin')] class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $newName = '';

    public ?string $editingId = null;
    public string $editingName = '';
    public ?string $confirmingDelete = null;

    public function create(): void
    {
        $this->validate(['newName' => 'required|string|max:60|unique:tags,name']);
        Tag::create(['name' => $this->newName]);
        $this->newName = '';
        unset($this->tags);
    }

    public function startEdit(string $id): void
    {
        $this->editingId = $id;
        $this->editingName = Tag::findOrFail($id)->name;
    }

    public function saveEdit(): void
    {
        $this->validate(['editingName' => 'required|string|max:60|unique:tags,name,'.$this->editingId]);
        Tag::findOrFail($this->editingId)->update(['name' => $this->editingName]);
        $this->editingId = null;
        unset($this->tags);
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function delete(string $id): void
    {
        if ($this->confirmingDelete !== $id) {
            $this->confirmingDelete = $id;
            return;
        }
        Tag::findOrFail($id)->delete();
        $this->confirmingDelete = null;
        unset($this->tags);
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    public function updatedSearch(): void { $this->resetPage(); }

    #[Computed]
    public function tags()
    {
        return Tag::withCount('products')
            ->when($this->search, fn ($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%'.strtolower($this->search).'%']))
            ->orderBy('name')
            ->paginate(15);
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">Tags</h1>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search..." icon="magnifying-glass" clearable size="sm" class="w-56" />
    </div>

    <form wire:submit="create" class="mb-6 flex gap-3">
        <div class="flex-1">
            <flux:input wire:model="newName" placeholder="New tag name..." />
            @error('newName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>
        <flux:button type="submit" variant="primary">Add tag</flux:button>
    </form>

    <div class="overflow-hidden rounded-xl border border-zinc-800">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-800 bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-widest text-zinc-500">Slug</th>
                    <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-widest text-zinc-500">Products</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800 bg-zinc-950">
                @forelse ($this->tags as $tag)
                    <tr wire:key="{{ $tag->id }}">
                        <td class="px-4 py-3">
                            @if ($editingId === $tag->id)
                                <div class="flex items-center gap-2">
                                    <flux:input wire:model="editingName" size="sm" class="w-48" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit" autofocus />
                                    <flux:button wire:click="saveEdit" size="sm" variant="primary">Save</flux:button>
                                    <flux:button wire:click="cancelEdit" size="sm">Cancel</flux:button>
                                </div>
                            @else
                                <span class="font-medium text-zinc-200">{{ $tag->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-600">{{ $tag->slug }}</td>
                        <td class="px-4 py-3 text-right text-zinc-400">{{ $tag->products_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($confirmingDelete === $tag->id)
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-xs text-zinc-500">Delete?</span>
                                    <flux:button wire:click="delete('{{ $tag->id }}')" size="sm" variant="danger">Yes, delete</flux:button>
                                    <flux:button wire:click="cancelDelete" size="sm">Cancel</flux:button>
                                </div>
                            @else
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button wire:click="startEdit('{{ $tag->id }}')" size="sm" icon="pencil" />
                                    <flux:button wire:click="delete('{{ $tag->id }}')" size="sm" icon="trash" variant="ghost" class="text-zinc-600 hover:text-red-400" />
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-zinc-600">No tags yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $this->tags->links() }}</div>
</div>
