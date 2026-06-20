<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    #[Validate('required|min:3|max:100')]
    public string $title = '';

    #[Validate('required|min:20|max:2000')]
    public string $description = '';

    #[Validate('required|numeric|min:0')]
    public string $price = '';

    #[Validate('required|exists:categories,id')]
    public string $category_id = '';

    #[Validate('required|array|min:1|max:5')]
    public array $selectedTags = [];

    #[Validate('required|file|mimes:zip,png,jpg,jpeg,mp3,ttf,pdf|max:51200')]
    public $file;

    #[Validate('nullable|image|mimes:png,jpg,jpeg,webp|max:5120')]
    public $thumbnail;

    #[Validate('nullable|image|mimes:png,jpg,jpeg,webp|max:5120')]
    public $coverImage;

    public function save(): void
    {
        $this->validate();

        $filePath = $this->file->store('products/files', 'local');
        $thumbnailPath = $this->thumbnail?->store('products/thumbnails', 'public');
        $coverPath = $this->coverImage?->store('products/covers', 'public');

        $product = Product::create([
            'title'       => $this->title,
            'description' => $this->description,
            'price'       => $this->price,
            'category_id' => $this->category_id,
            'user_id'     => Auth::id(),
            'file_path'   => $filePath,
            'thumbnail'   => $thumbnailPath ? Storage::url($thumbnailPath) : null,
            'cover_image' => $coverPath ? Storage::url($coverPath) : null,
            'is_active'   => true,
        ]);

        $product->tags()->sync($this->selectedTags);

        $this->redirect(route('my-products.index'), navigate: true);
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

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-white">New product</h1>
            <p class="mt-1 text-sm text-zinc-500">Fill in the details to list your digital asset for sale.</p>
        </div>

        <form wire:submit="save" class="grid gap-8 lg:grid-cols-3">
            {{-- Main fields --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Title --}}
                <div>
                    <flux:input
                        wire:model="title"
                        label="Title"
                        placeholder="e.g. Minimal Icon Pack"
                        description="3–100 characters"
                    />
                    @error('title') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <flux:textarea
                        wire:model="description"
                        label="Description"
                        placeholder="Describe your product in detail..."
                        rows="6"
                        description="20–2000 characters"
                    />
                    @error('description') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Category + Price --}}
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
                        <flux:input
                            wire:model="price"
                            type="number"
                            min="0"
                            step="0.01"
                            label="Price (USD)"
                            placeholder="0.00"
                            description="Set 0 for free"
                        />
                        @error('price') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Tags --}}
                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Tags <span class="text-zinc-600">(1–5)</span></p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($this->tags as $tag)
                            <label wire:key="{{ $tag->id }}" class="cursor-pointer">
                                <input type="checkbox" wire:model="selectedTags" value="{{ $tag->id }}" class="sr-only peer">
                                <span class="inline-block rounded-full border border-zinc-700 px-3 py-1 text-xs text-zinc-400 transition peer-checked:border-white peer-checked:bg-white peer-checked:text-zinc-900 hover:border-zinc-500">
                                    {{ $tag->name }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('selectedTags') <p class="mt-2 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Digital file --}}
                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Digital file <span class="text-zinc-600">(zip, png, jpg, mp3, ttf, pdf — max 50 MB)</span></p>
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed border-zinc-700 px-6 py-8 transition hover:border-zinc-500">
                        <svg class="mb-2 size-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        @if ($file)
                            <span class="text-sm text-zinc-300">{{ $file->getClientOriginalName() }}</span>
                            <span class="mt-1 text-xs text-zinc-600">{{ round($file->getSize() / 1024 / 1024, 2) }} MB</span>
                        @else
                            <span class="text-sm text-zinc-500">Click to upload or drag and drop</span>
                        @endif
                        <input type="file" wire:model="file" accept=".zip,.png,.jpg,.jpeg,.mp3,.ttf,.pdf" class="sr-only">
                    </label>
                    <div wire:loading wire:target="file" class="mt-2 text-xs text-zinc-500">Uploading...</div>
                    @error('file') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Sidebar: images + submit --}}
            <div class="space-y-6">
                {{-- Thumbnail --}}
                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Thumbnail <span class="text-zinc-600">(400×400 recommended)</span></p>
                    <label class="block cursor-pointer overflow-hidden rounded-xl border border-dashed border-zinc-700 transition hover:border-zinc-500">
                        @if ($thumbnail)
                            <img src="{{ $thumbnail->temporaryUrl() }}" class="aspect-[4/3] w-full object-cover">
                        @else
                            <div class="flex aspect-[4/3] items-center justify-center">
                                <svg class="size-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9" /></svg>
                            </div>
                        @endif
                        <input type="file" wire:model="thumbnail" accept="image/*" class="sr-only">
                    </label>
                    @error('thumbnail') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Cover image --}}
                <div>
                    <p class="mb-2 text-sm font-medium text-zinc-300">Cover image <span class="text-zinc-600">(1200×600 recommended)</span></p>
                    <label class="block cursor-pointer overflow-hidden rounded-xl border border-dashed border-zinc-700 transition hover:border-zinc-500">
                        @if ($coverImage)
                            <img src="{{ $coverImage->temporaryUrl() }}" class="aspect-video w-full object-cover">
                        @else
                            <div class="flex aspect-video items-center justify-center">
                                <svg class="size-8 text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3 16.5V19a.75.75 0 00.75.75h16.5A.75.75 0 0021 19v-2.5M3 16.5V7.5A.75.75 0 013.75 6.75h16.5A.75.75 0 0121 7.5v9" /></svg>
                            </div>
                        @endif
                        <input type="file" wire:model="coverImage" accept="image/*" class="sr-only">
                    </label>
                    @error('coverImage') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>

                {{-- Submit --}}
                <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Publish product</span>
                    <span wire:loading wire:target="save">Publishing...</span>
                </flux:button>
            </div>
        </form>
    </flux:main>
</div>
