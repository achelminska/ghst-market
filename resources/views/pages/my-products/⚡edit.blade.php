<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public Product $product;

    public string $title = '';
    public string $description = '';
    public string $price = '';
    public string $category_id = '';
    public array $selectedTags = [];
    public $file;
    public $thumbnail;
    public $coverImage;

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $this->title = $this->product->title;
        $this->description = $this->product->description;
        $this->price = $this->product->price;
        $this->category_id = $this->product->category_id;
        $this->selectedTags = $this->product->tags()->pluck('id')->map(fn ($id) => (string) $id)->all();
    }

    public function save(): void
    {
        $this->validate([
            'title'        => 'required|min:3|max:100',
            'description'  => 'required|min:20|max:2000',
            'price'        => 'required|numeric|min:0',
            'category_id'  => 'required|exists:categories,id',
            'selectedTags' => 'required|array|min:1|max:5',
            'file'         => 'nullable|file|max:102400',
            'thumbnail'    => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
            'coverImage'   => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $data = [
            'title'       => $this->title,
            'description' => $this->description,
            'price'       => $this->price,
            'category_id' => $this->category_id,
        ];

        if ($this->file) {
            if ($this->product->file_path) {
                Storage::disk('local')->delete($this->product->file_path);
            }
            $data['file_path'] = $this->file->store('products/files', 'local');
        }

        if ($this->thumbnail) {
            $data['thumbnail'] = Storage::url($this->thumbnail->store('products/thumbnails', 'public'));
        }

        if ($this->coverImage) {
            $data['cover_image'] = Storage::url($this->coverImage->store('products/covers', 'public'));
        }

        $this->product->update($data);
        $this->product->tags()->sync($this->selectedTags);

        $this->redirect(route('my-products.index'), navigate: true);
    }

    public function toggleActive(): void
    {
        $this->product->update(['is_active' => ! $this->product->is_active]);
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function tags()
    {
        return Tag::orderBy('name')->get();
    }
};
?>

<div>
    <flux:main>
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('my-products.index') }}" wire:navigate class="inline-flex items-center gap-1.5 text-sm text-zinc-500 transition-colors hover:text-white">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                My products
            </a>
        </div>

        <div class="mb-8 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Edit product</h1>
                <p class="mt-1 text-sm text-zinc-500">{{ $this->product->title }}</p>
            </div>

            <button
                wire:click="toggleActive"
                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium transition
                    {{ $this->product->is_active
                        ? 'border-emerald-800 text-emerald-400 hover:border-red-800 hover:text-red-400'
                        : 'border-zinc-700 text-zinc-500 hover:border-emerald-800 hover:text-emerald-400' }}"
            >
                <span class="size-2 rounded-full {{ $this->product->is_active ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span>
                {{ $this->product->is_active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}
            </button>
        </div>

        <form wire:submit="save" class="grid gap-8 lg:grid-cols-3">
            {{-- Main fields --}}
            <div class="space-y-6 lg:col-span-2">
                <div>
                    <flux:input wire:model="title" label="Title" description="3–100 characters" />
                    @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <flux:textarea wire:model="description" label="Description" rows="6" description="20–2000 characters" />
                    @error('description') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <flux:select wire:model="category_id" label="Category" placeholder="Select category">
                            @foreach ($this->categories as $cat)
                                <flux:select.option wire:key="{{ $cat->id }}" value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        @error('category_id') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <flux:input wire:model="price" type="number" min="0" step="0.01" label="Price (USD)" description="Set 0 for free" />
                        @error('price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Tags <span class="text-zinc-600">(1–5)</span></p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->tags as $tag)
                            <label wire:key="{{ $tag->id }}" class="cursor-pointer">
                                <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}" @checked(in_array((string) $tag->id, $selectedTags)) class="sr-only peer">
                                <span class="inline-block rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400 transition peer-checked:border-white peer-checked:bg-white peer-checked:text-zinc-900 hover:border-zinc-500">
                                    {{ $tag->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedTags') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">
                        Digital file
                        <span class="text-zinc-600">(zip, epub, pdf, mp3, ttf, psd i inne — max 100 MB; leave empty to keep current)</span>
                    </p>
                    @if ($this->product->file_path && !$file)
                        <p class="mb-2 text-xs text-zinc-600">
                            Current: {{ basename($this->product->file_path) }}
                        </p>
                    @endif
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-zinc-700 px-6 py-8 transition hover:border-zinc-500">
                        <svg class="mb-2 size-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        @if ($file)
                            <span class="text-sm text-zinc-300">{{ $file->getClientOriginalName() }}</span>
                            <span class="mt-1 text-xs text-zinc-600">{{ round($file->getSize() / 1024 / 1024, 2) }} MB</span>
                        @else
                            <span class="text-sm text-zinc-500">Click to replace file</span>
                        @endif
                        <input type="file" wire:model="file" class="sr-only">
                    </label>
                    @error('file') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Sidebar: images + submit --}}
            <div class="space-y-6">
                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Thumbnail</p>
                    <label class="block cursor-pointer overflow-hidden rounded-xl border border-dashed border-zinc-700 transition hover:border-zinc-500">
                        @if ($thumbnail)
                            <img src="{{ $thumbnail->temporaryUrl() }}" class="aspect-[4/3] w-full object-cover">
                        @elseif ($this->product->thumbnail)
                            <img src="{{ $this->product->thumbnail }}" class="aspect-[4/3] w-full object-cover">
                        @else
                            <div class="flex aspect-[4/3] items-center justify-center">
                                <svg class="size-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9"/></svg>
                            </div>
                        @endif
                        <input type="file" wire:model="thumbnail" accept="image/*" class="sr-only">
                    </label>
                    @error('thumbnail') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Page background <span class="text-zinc-600">(tileable image, repeats behind product page)</span></p>
                    <label class="block cursor-pointer overflow-hidden rounded-xl border border-dashed border-zinc-700 transition hover:border-zinc-500">
                        @if ($coverImage)
                            <img src="{{ $coverImage->temporaryUrl() }}" class="aspect-video w-full object-cover">
                        @elseif ($this->product->cover_image)
                            <img src="{{ $this->product->cover_image }}" class="aspect-video w-full object-cover">
                        @else
                            <div class="flex aspect-video items-center justify-center">
                                <svg class="size-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9"/></svg>
                            </div>
                        @endif
                        <input type="file" wire:model="coverImage" accept="image/*" class="sr-only">
                    </label>
                    @error('coverImage') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </flux:button>

                <a href="{{ route('products.show', $this->product->slug) }}" wire:navigate class="block text-center text-sm text-zinc-600 transition hover:text-zinc-400">
                    View public page →
                </a>
            </div>
        </form>
    </flux:main>
</div>
