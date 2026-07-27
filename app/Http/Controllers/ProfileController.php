<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'signature' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:4096'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $tempPath = $file->getRealPath();

            // 1. Load image using GD
            $info = getimagesize($tempPath);
            $mime = $info['mime'] ?? '';

            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $sourceImg = imagecreatefromjpeg($tempPath);
                    break;
                case 'image/png':
                    $sourceImg = imagecreatefrompng($tempPath);
                    break;
                case 'image/webp':
                    $sourceImg = imagecreatefromwebp($tempPath);
                    break;
                case 'image/gif':
                    $sourceImg = imagecreatefromgif($tempPath);
                    break;
                default:
                    return back()->with('error', 'ประเภทไฟล์รูปภาพไม่รองรับ');
            }

            if (!$sourceImg) {
                return back()->with('error', 'ไม่สามารถประมวลผลรูปภาพลายเซ็นได้');
            }

            // Get dimensions
            $width = imagesx($sourceImg);
            $height = imagesy($sourceImg);

            // 2. Create target transparent image
            $targetImg = imagecreatetruecolor($width, $height);
            imagealphablending($targetImg, false);
            imagesavealpha($targetImg, true);

            // Create transparent color fill
            $transparentColor = imagecolorallocatealpha($targetImg, 0, 0, 0, 127);
            imagefill($targetImg, 0, 0, $transparentColor);

            // 3. Process pixels to remove background
            // Thresholds:
            // Y >= 210 -> Fully transparent background
            // Y <= 130 -> Fully opaque ink
            // In between -> Interploated smooth edge
            $t1 = 130;
            $t2 = 210;

            for ($x = 0; $x < $width; $x++) {
                for ($y = 0; $y < $height; $y++) {
                    $colorIndex = imagecolorat($sourceImg, $x, $y);
                    $colors = imagecolorsforindex($sourceImg, $colorIndex);

                    $r = $colors['red'];
                    $g = $colors['green'];
                    $b = $colors['blue'];
                    $originalAlpha = $colors['alpha'] ?? 0;

                    // Calculate brightness (Luma Y)
                    $brightness = 0.299 * $r + 0.587 * $g + 0.114 * $b;

                    if ($brightness >= $t2) {
                        // Fully transparent background pixel
                        $alpha = 127;
                    } elseif ($brightness <= $t1) {
                        // Keep original opacity
                        $alpha = $originalAlpha;
                    } else {
                        // Interpolate opacity smoothly
                        $ratio = ($brightness - $t1) / ($t2 - $t1);
                        $alpha = round(127 * $ratio);
                        // Ensure it's not more transparent than the original PNG pixel
                        if ($alpha < $originalAlpha) {
                            $alpha = $originalAlpha;
                        }
                    }

                    // Set pixel in target image
                    $pixelColor = imagecolorallocatealpha($targetImg, $r, $g, $b, $alpha);
                    imagesetpixel($targetImg, $x, $y, $pixelColor);
                }
            }

            // Delete old signature if exists
            if ($user->signature) {
                Storage::disk('public')->delete($user->signature);
            }

            // 4. Save processed image as PNG
            $filename = 'signature_' . $user->id . '_' . time() . '.png';
            $storageDir = storage_path('app/public/signatures');
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            $outputPath = $storageDir . '/' . $filename;
            imagepng($targetImg, $outputPath);

            // Free resources
            imagedestroy($sourceImg);
            imagedestroy($targetImg);

            $user->signature = 'signatures/' . $filename;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('success', 'อัปเดตข้อมูลโปรไฟล์และภาพลายเซ็น (ลบพื้นหลังเรียบร้อย) สำเร็จ');
    }
}
