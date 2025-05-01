<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateController extends Controller
{
    protected $updater;

    public function __construct(UpdaterManager $updater)
    {
        $this->updater = $updater;
    }

    public function index()
    {
        try {
            $isNewVersionAvailable = $this->updater->source()->isNewVersionAvailable();
            $currentVersion = $this->updater->source()->getVersionInstalled();
            $newVersion = null;
            
            if ($isNewVersionAvailable) {
                $newVersion = $this->updater->source()->getVersionAvailable();
            }

            return view('tenant.updates.index', compact('isNewVersionAvailable', 'currentVersion', 'newVersion'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error checking for updates: ' . $e->getMessage());
        }
    }

    public function check()
    {
        try {
            $isNewVersionAvailable = $this->updater->source()->isNewVersionAvailable();
            $currentVersion = $this->updater->source()->getVersionInstalled();
            $newVersion = null;
            
            if ($isNewVersionAvailable) {
                $newVersion = $this->updater->source()->getVersionAvailable();
            }

            return response()->json([
                'hasUpdate' => $isNewVersionAvailable,
                'currentVersion' => $currentVersion,
                'newVersion' => $newVersion
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update()
    {
        try {
            if ($this->updater->source()->isNewVersionAvailable()) {
                // Create a backup before updating
                // Artisan::call('backup:run');

                // Start the update
                $this->updater->source()->update();

                return redirect()->back()->with('success', 'System has been updated successfully!');
            }

            return redirect()->back()->with('info', 'No updates available.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }
} 