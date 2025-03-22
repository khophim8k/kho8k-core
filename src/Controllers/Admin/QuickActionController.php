<?php

namespace Kho8k\Core\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\File;

class QuickActionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete_cache()
    {
        Artisan::call('optimize:clear');
        Alert::success("Xóa cache thành công")->flash();
        return back();
    }

    /**
     * Update CMS using composer update
     *
     * @return \Illuminate\Http\Response
     */
    public function updateCMS()
    {
        $output = [];
        $return_var = 0;
        $projectRoot = base_path(); // Get the root path of the project
        exec("cd $projectRoot && composer update 2>&1", $output, $return_var);

        if ($return_var === 0) {
            Alert::success("Cập nhật CMS thành công")->flash();
        } else {
            $error_message = implode("\n", $output);
            Alert::error("Cập nhật CMS thất bại: " . $error_message)->flash();
        }

        return back();
    }

    /**
     * Xóa storage trong public và tạo symbolic link lại
     *
     * @return \Illuminate\Http\Response
     */
    public function reLinkStorage()
    {
        // Xóa symbolic link cũ nếu có
        if (File::exists(public_path('storage'))) {
            File::deleteDirectory(public_path('storage'));
        }
        // Tạo symbolic link mới
        Artisan::call('storage:link');

        Alert::success("Đã xóa storage và tạo symbolic link lại thành công")->flash();
        return back();
    }
    public function refile()
    {
        // Tạo symbolic link mới
        Artisan::call('backpack:filemanager:install');

        Alert::success("Đã cài filemanager thành công")->flash();
        return back();
    }
}
