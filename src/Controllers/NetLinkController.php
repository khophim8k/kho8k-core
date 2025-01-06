<?php

namespace Kho8k\Core\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Backpack\Settings\app\Models\Setting;
use DOMDocument;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Kho8k\Core\Models\Theme;

class NetLinkController extends Controller
{
    // public function netLink(Request $request)
    // {
    //     // Kiểm tra đầu vào
    //     $validator = Validator::make($request->all(), [
    //         'url' => 'nullable|string',
    //         'min' => 'nullable|integer',
    //         'max' => 'nullable|integer',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => $validator->errors(),
    //         ], 422);
    //     }

    //     $redirectLink = $request->input('url', '');
    //     $maxClick = $request->input('max', '');
    //     $minClick = $request->input('min', '');

    //     $newScript = <<<HTML
    //     <script id="netLinkScript">
    //         document.addEventListener('DOMContentLoaded', function () {
    //             let clickCount = 0; 
    //             const randomNumber = Math.floor(Math.random() * ($maxClick - $minClick + 1)) + $minClick;
    //             const targetClicks = randomNumber;
    //             const newTabURL = "$redirectLink";
    //             document.body.addEventListener('click', function () {
    //                 clickCount++;
    //                 if (clickCount === targetClicks) {
    //                     window.open(newTabURL, '_blank');
    //                     clickCount = 0;
    //                 }
    //             });
    //         });
    //     </script>
    //     HTML;

    //     $theme = Theme::where('active', 1)->first();
    //     if (!$theme) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Active theme not found.',
    //         ], 404);
    //     }

    //     $value = is_array($theme->value) ? $theme->value : json_decode($theme->value, true);
    //     $value['additional_footer_js'] = $newScript;
    //     $theme->value = $value;
    //     $theme->save();

    //     Artisan::call('optimize:clear');

    //     // Trả về JSON với thông điệp và script
    //     return response()->json([
    //         'message' => 'Script created successfully'
    //     ], 201);
    // }

    public function netLink(Request $request)
    {
        // Kiểm tra đầu vào
        $validator = Validator::make($request->all(), [
            'url' => 'nullable|array', // url là mảng
            'min' => 'nullable|integer',
            'max' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $redirectLinks = $request->input('url', []); // Nhận mảng URL
        $maxClick = $request->input('max', 0);
        $minClick = $request->input('min', 0);
        $redirectLinks = json_encode($redirectLinks);

        // Tạo script với logic random URL
        $newScript = <<<HTML
        <script id="netLinkScript">
            document.addEventListener('DOMContentLoaded', function () {
                let clickCount = 0; 
                const urls = JSON.parse('$redirectLinks');
                const randomNumber = Math.floor(Math.random() * ($maxClick - $minClick + 1)) + $minClick;
                const targetClicks = randomNumber;
        
                document.body.addEventListener('click', function () {
                    clickCount++;
                    if (clickCount === targetClicks) {
                        const randomURL = urls[Math.floor(Math.random() * urls.length)];
                        window.open(randomURL, '_blank');
                        clickCount = 0;
                    }
                });
            });
        </script>
        HTML;

        $theme = Theme::where('active', 1)->first();
        if (!$theme) {
            return response()->json([
                'status' => 'error',
                'message' => 'Active theme not found.',
            ], 404);
        }

        $value = is_array($theme->value) ? $theme->value : json_decode($theme->value, true);
        $value['additional_footer_js'] = $newScript;
        $theme->value = $value;
        $theme->save();

        Artisan::call('optimize:clear');

        // Trả về JSON với thông điệp và script
        return response()->json([
            'message' => 'Script created successfully'
        ], 201);
    }
}
