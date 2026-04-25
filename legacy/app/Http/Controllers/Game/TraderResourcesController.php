<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\ResourceMarket;
use App\Enums\Module;
use Illuminate\Support\Facades\DB;
use Xgp\App\Core\Concerns\PreparesLegacySql;
use Xgp\App\Libraries\Users;

/**
 * @SuppressWarnings("PHPMD.StaticAccess")
 */
class TraderResourcesController extends BaseController
{
    use PreparesLegacySql;

    public const RESOURCES = ['metal', 'crystal', 'deuterium'];
    public const PERCENTAGES = [10, 50, 100];

    private array $user = [];
    private array $planet = [];
    private ?ResourceMarket $trader;
    private string $error = '';

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(Module::Trader));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->trader = new ResourceMarket(
            $this->user,
            $this->planet
        );

        $this->runAction();

        Template::legacyView(
            'trader.overview',
            array_merge(
                $this->setMessageDisplay(),
                $this->getPage()
            )
        );
    }

    private function runAction(): void
    {
        $refill = filter_input_array(INPUT_POST);

        if ($refill) {
            if (
                preg_match_all(
                    '/(' . join('|', self::RESOURCES) . ')-(' . join('|', self::PERCENTAGES) . ')/',
                    key($refill)
                )
            ) {
                $parts = explode('-', key($refill));
                $this->refillResource($parts[0], (int) $parts[1]);
            }
        }
    }

    private function refillResource(string $resource, int $percentage): void
    {
        if ($this->trader->{'is' . $resource . 'StorageFillable'}($percentage)) {
            if ($this->trader->isRefillPayable($resource, $percentage)) {
                $dark_matter = (int) $this->trader->{'getPriceToFill' . $percentage . 'Percent'}($resource);
                $amount = $this->trader->getProjectedResouces($resource, $percentage);
                DB::statement(
                    $this->prepareSql(
                        'UPDATE `' . PREMIUM . '` pr, `' . PLANETS . "` p SET
                        pr.`premium_dark_matter` = pr.`premium_dark_matter` - '" . $dark_matter . "',
                        p.`planet_" . $resource . "` = '" . $amount . "'
                        WHERE pr.`premium_user_id` = '" . $this->user['id'] . "'
                            AND p.`planet_id` = '" . $this->planet['planet_id'] . "';"
                    )
                );

                Functions::redirect('game.php?page=traderResources');
            } else {
                $this->error = __('game/trader.tr_no_enough_dark_matter');
            }
        } else {
            $this->error = __('game/trader.tr_no_enough_storage');
        }
    }

    private function setMessageDisplay(): array
    {
        $message = [
            'color' => '',
            'message' => '',
        ];

        if ($this->error != '') {
            $message = [
                'color' => '#ff0000',
                'message' => $this->error,
            ];
        }

        return $message;
    }

    private function getPage(): array
    {
        return [
            'currentMode' => Template::render(
                'trader.resources',
                array_merge(
                    [
                        'resourcesList' => $this->buildResourcesSection(),
                    ]
                )
            ),
        ];
    }

    private function buildResourcesSection(): array
    {
        $resourcesList = [];

        foreach (self::RESOURCES as $resource) {
            $resourcesList[] = array_merge(
                [
                    'resource' => $resource,
                    'resourceName' => __('game/global.' . $resource),
                    'currentResource' => $this->formatService->shortlyNumber($this->planet['planet_' . $resource]),
                    'maxResource' => $this->formatService->shortlyNumber($this->planet['planet_' . $resource . '_max']),
                    'refillOptions' => $this->setRefillOptions($resource),
                ]
            );
        }

        return $resourcesList;
    }

    private function setRefillOptions(string $resource): array
    {
        $refillOptions = [];

        foreach (self::PERCENTAGES as $percentage) {
            $dmPrice = $this->trader->{'getPriceToFill' . $percentage . 'Percent'}($resource);

            if (
                !$this->trader->{'is' . ucfirst($resource) . 'StorageFillable'}($percentage) ||
                $dmPrice == 0
            ) {
                $price = $this->formatService->colorRed('-');
                $button = '';
            } else {
                $price = $this->formatService->customColor(
                    $this->formatService->prettyNumber((int) $dmPrice),
                    '#2cbef2'
                ) . ' ' . __('game/global.dark_matter_short');
                $button = '<input type="submit" name="' . $resource . '-' . $percentage . '" value="' . __('game/trader.tr_refill_button') . '">';
            }

            $refillOptions[] = [
                'label' => (self::PERCENTAGES == 100) ? __('game/trader.tr_refill_to') : __('game/trader.tr_refill_by'),
                'percentage' => $percentage,
                'tr_requires' => __('game/trader.tr_requires'),
                'price' => $price,
                'button' => $button,
            ];
        }

        return $refillOptions;
    }
}
