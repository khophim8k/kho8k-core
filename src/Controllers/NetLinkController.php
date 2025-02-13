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

    public function netLink(Request $request)
    {
        // Kiểm tra đầu vào
        $validator = Validator::make($request->all(), [
            'url' => 'nullable|array', // url là mảng
            'min' => 'nullable|integer',
            'max' => 'nullable|integer',
            'active' => 'required|boolean', // Bắt buộc có active, phải là true hoặc false
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        $theme = Theme::where('active', 1)->first();
        if (!$theme) {
            return response()->json([
                'status' => 'error',
                'message' => 'Active theme not found.',
            ], 404);
        }
        $value = is_array($theme->value) ? $theme->value : json_decode($theme->value, true);

        // Nếu "active" = false -> Xóa script, nếu "active" = true -> Tạo script
        if ($request->input('active')) {

            $redirectLinks = json_encode($request->input('url', []));
            $maxClick = $request->input('max', 0);
            $minClick = $request->input('min', 0);

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

            $value['additional_footer_js'] = $newScript;
        } else {
            $value['additional_footer_js'] = ''; // Xóa script nếu active = false
        }

        $theme->value = $value;
        $theme->save();

        Artisan::call('optimize:clear');

        return response()->json([
            'message' => $request->input('active') ? 'Script activated successfully' : 'Script deactivated successfully'
        ], 201);
    }
}
