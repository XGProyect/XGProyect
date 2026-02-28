<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BackupRequest;
use App\Services\Admin\BackupService;
use App\Services\AdministrationService;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
        private readonly BackupService $backupService,
        private readonly FilesystemFactory $storage,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.backup', [
            'auto_backup' => $this->backupService->isAutoBackupEnabled(),
            'backup_list' => $this->backupService->getBackupList(),
        ]);
    }

    public function save(BackupRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->backupService->saveSettings($request->has('auto_backup'));

        session()->flash('success', __('admin/backup.bku_settings_saved'));

        return redirect()->route('admin.backup');
    }

    public function create(): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->backupService->createBackup();

        session()->flash('success', __('admin/backup.bku_created'));

        return redirect()->route('admin.backup');
    }

    public function download(string $file): StreamedResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        abort_unless($this->backupService->isValidFileName($file), 404);

        $path = $this->backupService->filePath($file);
        $disk = $this->storage->disk($this->backupService->diskName());
        assert($disk instanceof FilesystemAdapter);

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, $file);
    }

    public function destroy(string $file): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        abort_unless($this->backupService->isValidFileName($file), 404);

        $this->backupService->deleteBackup($file);

        session()->flash('success', __('admin/backup.bku_deleted'));

        return redirect()->route('admin.backup');
    }
}
