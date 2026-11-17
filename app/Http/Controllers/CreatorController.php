<?php

namespace App\Http\Controllers;

use App\Models\Creator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreatorController extends Controller
{
    public function index(): View
    {
        return view('admin.creators', ['creators' => Creator::orderBy('position')->get()]);
    }

    public function update(Request $request, Creator $creator): RedirectResponse
    {
        $request->validate(['photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:min_width=100,min_height=100,max_width=6000,max_height=6000']], ['photo.required' => 'Pilih foto terlebih dahulu.', 'photo.mimes' => 'Gunakan foto JPG, PNG, atau WebP.', 'photo.max' => 'Ukuran foto maksimal 4 MB.']);
        $old = $creator->photo_path;
        $path = $request->file('photo')->store('creator-photos', 'local');
        $creator->update(['photo_path' => $path]);
        if ($old) {
            Storage::disk('local')->delete($old);
        }

        return back()->with('success', 'Foto '.$creator->name.' sudah diperbarui.');
    }

    public function photo(Creator $creator): StreamedResponse
    {
        abort_unless($creator->photo_path && Storage::disk('local')->exists($creator->photo_path), 404);

        return Storage::disk('local')->response($creator->photo_path, null, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'public, max-age=3600', 'Content-Security-Policy' => "default-src 'none'"]);
    }
}
