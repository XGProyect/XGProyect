<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\EncrypterRequest;
use App\Services\AdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EncrypterController extends BaseController
{
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    public function index(): View
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        return view('admin.encrypter', [
            'unencrypted' => session('unencrypted', ''),
            'encrypted' => session('encrypted', ''),
        ]);
    }

    public function encrypt(EncrypterRequest $request): RedirectResponse
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $unencrypted = (string) $request->input('unencrypted');

        return redirect()->route('admin.encrypter')
            ->with('unencrypted', $unencrypted)
            ->with('encrypted', Hash::make($unencrypted));
    }
}
