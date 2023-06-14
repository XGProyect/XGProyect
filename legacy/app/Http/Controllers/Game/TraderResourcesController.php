<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\ResourceMarket;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Trader;

class TraderResourcesController extends BaseController
{
    public const MODULE_ID = 5;
    public const RESOURCES = ['metal', 'crystal', 'deuterium'];
    public const PERCENTAGES = [10, 50, 100];

    private array $user = [];
    private array $planet = [];
    private ?ResourceMarket $trader;
    private string $error = '';
    private Trader $traderModel;

    public function __invoke(): void
    {
        Users::checkSession();

        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->user = Users::getInstance()->getUserData();
        $this->planet = Users::getInstance()->getPlanetData();
        $this->traderModel = new Trader();
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
                $this->refillResource(...explode('-', key($refill)));
            }
        }
    }

    private function refillResource(string $resource, int $percentage): void
    {
        if ($this->trader->{'is' . $resource . 'StorageFillable'}($percentage)) {
            if ($this->trader->isRefillPayable($resource, $percentage)) {
                $this->traderModel->refillStorage(
                    $this->trader->{'getPriceToFill' . $percentage . 'Percent'}($resource),
                    $resource,
                    $this->trader->getProjectedResouces($resource, $percentage),
                    $this->user['id'],
                    $this->planet['planet_id']
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
            'currentMode' => Template::getInstance()->render(
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
                    'currentResource' => Format::shortlyNumber($this->planet['planet_' . $resource]),
                    'maxResource' => Format::shortlyNumber($this->planet['planet_' . $resource . '_max']),
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
                !$this->trader->{'is' . ucfirst($resource) . 'StorageFillable'}($percentage)
                || $dmPrice == 0
            ) {
                $price = Format::colorRed('-');
                $button = '';
            } else {
                $price = Format::customColor(
                    Format::prettyNumber($dmPrice),
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
