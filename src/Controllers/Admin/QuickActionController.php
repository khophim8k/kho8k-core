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
        // Cấp quyền cho mọi user truy cập vào thư mục public
        $publicPath = public_path();
        exec("chmod -R 0777 $publicPath");

        // Chạy lệnh composer update
        $process = new \Symfony\Component\Process\Process(['composer', 'update']);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(3600); // Cho phép chạy lâu (1 giờ)

        try {
            $process->mustRun();
            $output = $process->getOutput();
            Alert::success("Composer update thành công")->flash();
            return back()->with('output', $output);
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
            $exitCode = $process->getExitCode();
            $errorOutput = $process->getErrorOutput();
            Alert::error("Composer update thất bại")->flash();
            return response("Command executed with exit code: $exitCode<br><pre>$errorOutput</pre>");
        }
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
        // Cấp quyền cho mọi user truy cập vào thư mục public
        $publicPath = public_path();
        exec("chmod -R 0777 $publicPath");

        // Tạo symbolic link mới
        $process = new \Symfony\Component\Process\Process(['php', 'artisan', 'backpack:filemanager:install']);
        $process->setWorkingDirectory(base_path());

        try {
            $process->mustRun();
            Alert::success("Đã cài filemanager thành công")->flash();
        } catch (\Symfony\Component\Process\Exception\ProcessFailedException $exception) {
            Alert::error("Cài filemanager thất bại: " . $exception->getMessage())->flash();
        }

        return back();
    }
}
