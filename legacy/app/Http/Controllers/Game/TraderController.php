<?php

namespace Xgp\App\Http\Controllers\Game;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\FormatLib as Format;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Game\ResourceMarket;
use Xgp\App\Libraries\Users;
use Xgp\App\Models\Game\Trader;

class TraderController extends BaseController
{
    public const MODULE_ID = 5;
    public const RESOURCES = ['metal', 'crystal', 'deuterium'];
    public const PERCENTAGES = [10, 50, 100];

    private ?ResourceMarket $trader;
    private string $error = '';
    private Trader $traderModel;

    public function __construct()
    {
        parent::__construct();

        Users::checkSession();

        $this->traderModel = new Trader();

        // init a new trader object
        $this->setUpTrader();
    }

    public function __invoke(): void
    {
        // Check module access
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        // time to do something
        $this->runAction();

        $this->buildPage();
    }

    /**
     * Creates a new trader object that will handle all the trader
     * creation methods and actions
     *
     * @return void
     */
    private function setUpTrader(): void
    {
        $this->trader = new ResourceMarket(
            $this->user,
            $this->planet
        );
    }

    /**
     * Run an action
     *
     * @return void
     */
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

    /**
     * Refill resources
     *
     * @param string $resource
     * @param integer $percentage
     * @return void
     */
    private function refillResource(string $resource, int $percentage): void
    {
        if ($this->trader->{'is' . $resource . 'StorageFillable'}($percentage)) {
            if ($this->trader->isRefillPayable($resource, $percentage)) {
                $this->traderModel->refillStorage(
                    $this->trader->{'getPriceToFill' . $percentage . 'Percent'}($resource),
                    $resource,
                    $this->trader->getProjectedResouces($resource, $percentage),
                    $this->user['user_id'],
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

    private function buildPage(): void
    {
        Template::getInstance()->view(
            'game/trader_overview_view',
            array_merge(
                $this->setMessageDisplay(),
                $this->getPage()
            )
        );
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
                'game/trader_resources_view',
                [
                    'list_of_resources' => $this->buildResourcesSection(),
                ]
            ),
        ];
    }

    /**
     * Build resources section
     *
     * @return array
     */
    private function buildResourcesSection(): array
    {
        $list_of_resources = [];

        foreach (self::RESOURCES as $resource) {
            $list_of_resources[] = [
                'dpath' => DPATH,
                'resource' => $resource,
                'resource_name' => __('game/global' . $resource),
                'current_resource' => Format::shortlyNumber($this->planet['planet_' . $resource]),
                'max_resource' => Format::shortlyNumber($this->planet['planet_' . $resource . '_max']),
                'refill_options' => $this->setRefillOptions($resource),
            ];
        }

        return $list_of_resources;
    }

    /**
     * Set the different refill options
     *
     * @param string $resource
     * @return array
     */
    private function setRefillOptions(string $resource): array
    {
        $refillOptions = [];

        foreach (self::PERCENTAGES as $percentage) {
            $dm_price = $this->trader->{'getPriceToFill' . $percentage . 'Percent'}($resource);

            if (
                !$this->trader->{'is' . ucfirst($resource) . 'StorageFillable'}($percentage)
                || $dm_price == 0
            ) {
                $price = Format::colorRed('-');
                $button = '';
            } else {
                $price = Format::customColor(
                    Format::prettyNumber($dm_price),
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
