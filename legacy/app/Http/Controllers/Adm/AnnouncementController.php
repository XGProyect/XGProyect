<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Mail\SentMessage;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Xgp\App\Core\Enumerators\MessagesEnumerator;
use Xgp\App\Core\Enumerators\UserRanksEnumerator as UserRanks;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class AnnouncementController extends BaseController
{
    private array $user = [];

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->user = Users::getInstance()->getUserData();

        $this->runAction();

        Template::legacyView(
            'admin.announcement',
            $this->buildColorPicker()
        );
    }

    private function runAction(): void
    {
        $action = filter_input_array(
            INPUT_POST,
            [
                'subject' => FILTER_UNSAFE_RAW,
                'color-picker' => [
                    'filter' => FILTER_CALLBACK,
                    'options' => [$this, 'isValidColor'],
                ],
                'message' => FILTER_UNSAFE_RAW,
                'mail' => FILTER_UNSAFE_RAW,
                'text' => [
                    'filter' => FILTER_UNSAFE_RAW,
                    'options' => ['min_range' => 1, 'max_range' => 5000],
                ],
            ],
            false
        );

        if ($action) {
            if (isset($action['text']) && $action['text'] != '') {
                if (isset($action['message'])) {
                    $this->doMessageAction($action);
                }

                if (isset($action['mail'])) {
                    $this->doEmailAction($action);
                }
            } else {
                session()->flash('warning', __('admin/announcement.an_not_sent'));
            }
        }
    }

    private function doMessageAction(array $post): void
    {
        $players = DB::table('users')->get(['id', 'name', 'email']);

        if (isset($post['color-picker'])) {
            $color = $post['color-picker'];
        } else {
            $color = $this->getMessageColor()[$this->user['authlevel']];
        }

        $level = __('admin/global.user_level')[$this->user['authlevel']];
        $time = time();

        $from = Format::customColor($level, $color);
        $subject = Format::customColor(($post['subject'] ?? __('admin/announcement.an_none')), $color);
        $message = Format::customColor($post['text'], $color);

        foreach ($players as $player) {
            Functions::sendMessage(
                (int) $player->id,
                (int) $this->user['id'],
                $time,
                MessagesEnumerator::GENERAL,
                $from,
                $subject,
                strtr($message, ['%player%' => Format::strongText($player->name)]),
                true
            );
        }

        session()->flash('success', __('admin/announcement.an_sent'));
    }

    private function doEmailAction(array $post): void
    {
        $players = DB::table('users')->get(['id', 'name', 'email']);
        $sentCount = 0;
        $results = [];

        foreach ($players as $player) {
            $result = Mail::to($player->email, $player->name)->send(new \App\Mail\Announcement(
                $post['subject'],
                strtr($post['text'], ['%player%' => Format::strongText($player->name)])
            ));

            $results[] = $player->name . ': ' . ($result instanceof SentMessage ? __('admin/announcement.an_email_sent') : __('admin/announcement.an_email_failed'));

            // 20 per row
            if ($sentCount % 20 == 0) {
                sleep(1); // wait, prevent flooding
            }

            $sentCount++;
        }

        session()->flash(
            'info',
            strtr(
                __('admin/announcement.an_delivery_result'),
                ['%s' => join('<br>', $results)]
            )
        );
    }

    private function buildColorPicker(): array
    {
        $colors_list = [];

        foreach (Format::getHTMLColorsNameList() as $color) {
            $colors_list[] = [
                'color' => $color,
            ];
        }

        return [
            'colors' => $colors_list,
        ];
    }

    private function isValidColor(string $color): string
    {
        if (in_array($color, Format::getHTMLColorsNameList())) {
            return $color;
        }

        return '';
    }

    private function getMessageColor(): array
    {
        return [
            UserRanks::GO => 'yellow',
            UserRanks::SGO => 'skyblue',
            UserRanks::ADMIN => 'red',
        ];
    }
}
