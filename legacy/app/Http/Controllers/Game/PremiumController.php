<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Services\FormatService;
use App\Services\Game\Formulas\OfficerService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\OfficiersEnumerator as OE;
use Xgp\App\Core\Objects;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Officier;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class PremiumController extends BaseController
{
    public const MODULE_ID = 15;

    private array $user = [];
    private Officier $officierModel;
    private Objects $objects;

    public function __construct(
        private FormatService $formatService,
        private OfficerService $officerService
    ) {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->officierModel = new Officier();
        $this->objects = new Objects();

        $this->runAction();

        Template::legacyView(
            'premium.view',
            [
                'premium_pay_url' => app(SettingsService::class)->getString('premium_url') ?: 'game.php?page=premium',
                'officier_list' => $this->buildOfficiersList(),
            ]
        );
    }

    private function runAction(): void
    {
        $data = filter_input_array(INPUT_GET, [
            'offi' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => [
                    'min_range' => OE::PREMIUM_OFFICIER_COMMANDER,
                    'max_range' => OE::PREMIUM_OFFICIER_TECHNOCRAT,
                ],
            ],
            'time' => [
                'filter' => FILTER_UNSAFE_RAW,
            ],
        ]);

        if (in_array($data['offi'], $this->objects->getObjectsList('officier')) && in_array($data['time'], ['week', 'month'])) {
            $time = 'darkmatter_' . $data['time'];
            $set_time = (($time == 'darkmatter_month') ? (ONE_MONTH * 3) : ONE_WEEK);

            if ($this->isOfficierAccesible($data['offi'], $time)) {
                $price = $this->getOfficierPrice($data['offi'], $time);
                $officier = $this->objects->getObjects($data['offi']);

                if ($this->officerService->isOfficerActive((int) $this->user[$officier], time())) {
                    $time_to_add = $this->user[$officier] + $set_time;
                } else {
                    $time_to_add = time() + $set_time;
                }

                $this->officierModel->setPremium($this->user['id'], $price, $officier, $time_to_add);

                Functions::redirect('game.php?page=premium');
            }
        }
    }

    private function buildOfficiersList(): array
    {
        $allowed_items = [
            OE::PREMIUM_OFFICIER_COMMANDER,
            OE::PREMIUM_OFFICIER_ADMIRAL,
            OE::PREMIUM_OFFICIER_ENGINEER,
            OE::PREMIUM_OFFICIER_GEOLOGIST,
            OE::PREMIUM_OFFICIER_TECHNOCRAT,
        ];

        $officiers_list = [];

        foreach ($allowed_items as $item_id) {
            $officiers_list[] = $this->setOfficier($item_id);
        }

        return $officiers_list;
    }

    private function setOfficier(int $item_id): array
    {
        $item_to_parse = [];
        $item_to_parse['status'] = $this->setOfficierStatusWithFormat($item_id);
        $item_to_parse['name'] = __('game/officier.officiers')[$item_id]['name'];
        $item_to_parse['description'] = __('game/officier.officiers')[$item_id]['description'];
        $item_to_parse['benefits'] = __('game/officier.officiers')[$item_id]['benefits'];
        $item_to_parse['month_price'] = $this->formatService->prettyNumber($this->getOfficierPrice($item_id, 'darkmatter_month'));
        $item_to_parse['week_price'] = $this->formatService->prettyNumber($this->getOfficierPrice($item_id, 'darkmatter_week'));
        $item_to_parse['img_big'] = $this->getOfficierImage($item_id, 'img_big');
        $item_to_parse['img_small'] = $this->getOfficierImage($item_id, 'img_small');
        $item_to_parse['link_month'] = 'game.php?page=premium&offi=' . $item_id . '&time=month';
        $item_to_parse['link_week'] = 'game.php?page=premium&offi=' . $item_id . '&time=week';

        return $item_to_parse;
    }

    private function setOfficierStatusWithFormat(int $item_id): string
    {
        if ($this->officerService->isOfficerActive((int) $this->user[$this->objects->getObjects($item_id)], time())) {
            return $this->formatService->customColor(
                (string) $this->officerService->getDaysLeft(
                    (int) $this->user[$this->objects->getObjects($item_id)],
                    time()
                ),
                'lime'
            );
        }

        return $this->formatService->colorRed(__('game/officier.of_inactive'));
    }

    private function isOfficierAccesible(int $officier, string $time): bool
    {
        return ($this->objects->getPrice($officier, $time) <= $this->user['premium_dark_matter']);
    }

    private function getOfficierPrice(int $officier, string $time): int
    {
        return (int) floor($this->objects->getPrice($officier, $time));
    }

    private function getOfficierImage(int $officier, string $type): string
    {
        return $this->objects->getPrice($officier, $type);
    }
}
