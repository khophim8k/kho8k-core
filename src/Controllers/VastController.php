<?php

namespace Kho8k\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Backpack\Settings\app\Models\Setting;

class VastController extends Controller
{
    public function uploadVast(Request $request)
    {
        // 1. Validate dữ liệu đầu vào
        $request->validate([
            'vast_content' => 'nullable|string',  // Cho phép null hoặc chuỗi
            'file_name' => 'nullable|string|max:255',
            'time_skip' => 'nullable|integer|min:0',
        ]);

        // 2. Lấy dữ liệu từ request
        $vastContent = $request->input('vast_content');
        $fileName = $request->input('file_name');
        $timeSkip = $request->input('time_skip');

        if (empty($vastContent)) {
            // Nếu vast_content rỗng, reset tất cả giá trị liên quan
            Setting::updateOrCreate(
                ['key' => 'jwplayer_advertising_file'],
                ['value' => null]
            );
            Setting::updateOrCreate(
                ['key' => 'jwplayer_advertising_skipoffset'],
                ['value' => null]
            );

            return response()->json([
                'message' => 'All VAST settings have been reset',
                'file_name' => null,
                'file_url' => null,
                'time_skip' => null,
            ], 200);
        }

        // 3. Xử lý file VAST nếu nội dung không rỗng
        $filePath = 'vast/' . $fileName;

        // Lưu file vào storage
        Storage::disk('public')->put($filePath, $vastContent);

        // Cập nhật đường dẫn file
        $fileUrl = Storage::url($filePath);
        Setting::updateOrCreate(
            ['key' => 'jwplayer_advertising_file'],
            ['value' => $fileUrl]
        );

        // 4. Cập nhật skip offset nếu có
        if ($timeSkip !== null) {
            Setting::updateOrCreate(
                ['key' => 'jwplayer_advertising_skipoffset'],
                ['value' => $timeSkip]
            );
        }

        // 5. Trả về response
        return response()->json([
            'message' => 'VAST file created successfully',
            'file_name' => $fileName,
            'file_url' => $fileUrl,
            'time_skip' => $timeSkip,
        ], 201);
    }
}