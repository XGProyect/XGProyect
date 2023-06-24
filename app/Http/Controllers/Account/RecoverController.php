<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Requests\RecoverRequest;
use App\Mail\Recover;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Xgp\App\Libraries\Functions;

class RecoverController extends BaseController
{
    public function __construct(private SettingsService $settingsService)
    {
    }

    public function __invoke(): View|Factory
    {
        return view(
            'account.recover',
            [
                'gameName' => $this->settingsService->getString('game_name'),
            ]
        );
    }

    public function recover(RecoverRequest $request): Response
    {
        // The incoming request is valid...
        $email = $request->validated()['email'];
        $username = User::where(['email' => $email])->first();

        if ($username !== null) {
            $newPassword = Functions::generatePassword();

            Mail::to($email)->send(new Recover($newPassword));

            User::where('email', $email)->update(['password' => Functions::hash($newPassword)]);

            return back()->with('message', __('account/recover.re_sent'));
        }

        return back()->with('message', __('account/recover.re_error'));
    }
}
