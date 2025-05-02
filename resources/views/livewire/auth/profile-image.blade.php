<section>
    <x-livewire-notifications />
    
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Image') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Update Your Profile Image.') }}
        </p>
    </header>

    <form wire:submit.prevent="updateImage" class="mt-6 space-y-6">
        <div class="image">
            <img src="{{ $this->avatar }}" alt="User Image" width="300" class="rounded-md">
        </div>

        <div>
            <x-input-label for="image" :value="__('Profile Image')" />
            <input type="file" wire:model="image" name="image" class="mt-1 block w-full" />

            <!-- Image Preview -->
            @if($image)
                <img src="{{ $image->temporaryUrl() }}" alt="Image preview" class="w-18 h-18 rounded-md">
            @endif

            <!-- Display validation error messages for image upload -->
            <x-input-error field="image" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </div>
    </form>
</section>
