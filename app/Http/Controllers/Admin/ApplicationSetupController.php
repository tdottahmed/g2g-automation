<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationSetupController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('permission:view applicationSettings')->only('index');
    //     $this->middleware('permission:create applicationSettings')->only('update');
    // }

    public function index()
    {
        $applicationSetup = ApplicationSetup::get();
        return view('admin.application-setup.index', compact('applicationSetup'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->has('scheduler_windows')) {
                ApplicationSetup::updateOrCreate(
                    ['type' => 'scheduler_windows'],
                    ['value' => json_encode($request->scheduler_windows)]
                );
            }

            if ($request->has('schedule_days')) {
                ApplicationSetup::updateOrCreate(
                    ['type' => 'schedule_days'],
                    ['value' => implode(',', $request->schedule_days)]
                );
            }

            if ($request->has('schedule_interval_minutes')) {
                ApplicationSetup::updateOrCreate(
                    ['type' => 'schedule_interval_minutes'],
                    ['value' => $request->schedule_interval_minutes]
                );
            }

            // Handle organization info
            $data = $request->only(['app_name', 'app_email', 'app_phone', 'app_address']);

            foreach ($data as $type => $value) {
                ApplicationSetup::updateOrCreate(
                    ['type' => $type],
                    ['value' => $value]
                );
            }

            // Handle file uploads
            $fileTypes = ['app_logo', 'app_favicon', 'login_banner'];

            foreach ($fileTypes as $fileType) {
                if ($request->has($fileType)) {
                    $filePath = filepondUpload($request->$fileType, 'organization');
                    if ($filePath) {
                        ApplicationSetup::updateOrCreate(
                            ['type' => $fileType],
                            ['value' => $filePath]
                        );
                    }
                }
            }

            DB::commit();

            return redirect()
                ->route('applicationSetup.index')
                ->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Failed to update settings: ' . $e->getMessage())
                ->withInput();
        }
    }
}
