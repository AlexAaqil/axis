<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

class ProfileImage extends Component
{
    use WithFileUploads;

    public $image;

    // Validation rules for the image field
    public function rules(): array
    {
        return [
            'image' => 'required|image|max:1024', // Only allow image files with a max size of 1MB
        ];
    }

    // Handle the image upload and update process
    public function updateImage()
    {
        // Validate the image upload
        $this->validate();

        $user = auth()->user();

        try {
            // Delete old image if it exists
            if ($user->image && Storage::disk('public')->exists('user-images/' . $user->image)) {
                Storage::disk('public')->delete('user-images/' . $user->image);
            }

            // Generate a unique filename for the new image
            $filename = 'user_' . $user->id . '_' . time() . '.' . $this->image->getClientOriginalExtension();

            // Store the image in the public storage with the generated filename
            $this->image->storeAs('user-images', $filename, 'public');

            // Update the user's image record in the database with the new filename
            $user->update([
                'image' => $filename,
            ]);

            // Reset the image field and show a success message
            $this->reset('image');
            session()->flash('success', 'Profile image updated successfully.');
            unset($this->avatar);
        } catch (\Exception $e) {
            // Catch any error during the process
            session()->flash('error', 'There was an error uploading your image. Please try again.');
        }
    }

    #[Computed(persist: true)]
    public function avatar(): string
    {
        $user = auth()->user();

        if (!$user || !$user->image) {
            return asset('images/default-avatar.png');
        }

        return asset('storage/user-images/' . $user->image);
    }

    public function download($image)
    {
        $image = auth()->user()->image;
        // return Storage::disk('public')->download($image, 'image.png');

        return response()->download(storage_path('app/public/user-images' . $image), 'image.png');
    }

    public function render()
    {
        return view('livewire.auth.profile-image');
    }
}
