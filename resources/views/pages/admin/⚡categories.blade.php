<?php

use App\Models\Category;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new #[Layout('layouts.admin')] class extends Component
{
    #[Validate('required|string|max:60|unique:categories,name')]
    public string $newName = '';

    public ?string $editingId = null;
    public string $editingName = '';
    public ?string $confirmingDelete = null;

    public function create(): void
    {
        $this->validate();
        Category::create(['name' => $this->newName]);
        $this->newName = '';
        unset($this->categories);
    }

    public function startEdit(string $id): void
    {
        $this->editingId = $id;
        $this->editingName = Category::findOrFail($id)->name;
    }

    public function saveEdit(): void
    {
        $this->validateOnly('editingName', [
            'editingName' => 'required|string|max:60|unique:categories,name,'.$this->editingId,
        ]);
        Category::findOrFail($this->editingId)->update(['name' => $this->editingName]);
        $this->editingId = null;
        unset($this->categories);
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
        Category::findOrFail($id)->delete();
        $this->confirmingDelete = null;
        unset($this->categories);
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = null;
    }

    #[Computed]
    public function categories()
    {
        return Category::withCount('products')->orderBy('name')->get();
    }
};
?>

<div>
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-white">Categories</h1>
        <span class="text-sm text-zinc-500">{{ $this->categories->count() }} total</span>
    </div>

    {{-- Add new --}}
    <form wire:submit="create" class="mb-6 flex gap-3">
        <div class="flex-1">
            <flux:input wire:model="newName" placeholder="New category name..." />
            @error('newName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>
        <flux:button type="submit" variant="primary">Add category</flux:button>
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
                @forelse ($this->categories as $category)
                    <tr wire:key="{{ $category->id }}">
                        <td class="px-4 py-3">
                            @if ($editingId === $category->id)
                                <div class="flex items-center gap-2">
                                    <flux:input wire:model="editingName" size="sm" class="w-48" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit" autofocus />
                                    <flux:button wire:click="saveEdit" size="sm" variant="primary">Save</flux:button>
                                    <flux:button wire:click="cancelEdit" size="sm">Cancel</flux:button>
                                </div>
                            @else
                                <span class="font-medium text-zinc-200">{{ $category->name }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-600">{{ $category->slug }}</td>
                        <td class="px-4 py-3 text-right text-zinc-400">{{ $category->products_count }}</td>
                        <td class="px-4 py-3 text-right">
                            @if ($confirmingDelete === $category->id)
                                <div class="flex items-center justify-end gap-2">
                                    <span class="text-xs text-zinc-500">Delete?</span>
                                    <flux:button wire:click="delete('{{ $category->id }}')" size="sm" variant="danger">Yes, delete</flux:button>
                                    <flux:button wire:click="cancelDelete" size="sm">Cancel</flux:button>
                                </div>
                            @else
                                <div class="flex items-center justify-end gap-2">
                                    <flux:button wire:click="startEdit('{{ $category->id }}')" size="sm" icon="pencil" />
                                    <flux:button wire:click="delete('{{ $category->id }}')" size="sm" icon="trash" variant="ghost" class="text-zinc-600 hover:text-red-400" />
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-12 text-center text-zinc-600">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
