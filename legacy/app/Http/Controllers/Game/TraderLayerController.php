<?php

declare(strict_types=1);

namespace Xgp\App\Http\Controllers\Game;

use App\Services\FormatService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Functions;
use Xgp\App\Libraries\Users;

class TraderLayerController extends BaseController
{
    public const MODULE_ID = 5;

    private array $planet = [];

    public function __construct(private FormatService $formatService)
    {
    }

    public function __invoke(): void
    {
        Functions::moduleMessage(Functions::isModuleAccesible(self::MODULE_ID));

        $this->planet = Users::getInstance()->getPlanetData();

        Template::render(
            'game/trader_layer_view',
            $this->getMode()
        );
    }

    /*

    if ($this->current_user['premium_dark_matter'] < $this->tr_dark_matter) {

    Functions::message(
    str_replace(
    '%s', $this->tr_dark_matter, __['tr_darkmatter_needed']
    ), '', '', true
    );

    die();
    }

    if (isset($_POST['ress']) && $_POST['ress'] != '') {

    switch ($_POST['ress']) {

    case 'metal':
    if ($_POST['cristal'] < 0 or $_POST['deut'] < 0) {
    Functions::message(__['tr_only_positive_numbers'], "game.php?page=traderOverview", 1);
    } else {
    $necessaire = (($_POST['cristal'] * 2) + ($_POST['deut'] * 4));
    $amout = array(
    'metal' => 0,
    'crystal' => $_POST['cristal'],
    'deuterium' => $_POST['deut'],
    );

    $storage = $this->checkStorage($amout);

    if (is_string($storage)) {

    die(Functions::message($storage, 'game.php?page=traderOverview', '2'));
    }

    if ($this->current_planet['planet_metal'] > $necessaire) {

    $this->db->query(
    "UPDATE " . PLANETS . " SET
    `planet_metal` = `planet_metal` - " . round($necessaire) . ",
    `planet_crystal` = `planet_crystal` + " . round($_POST['cristal']) . ",
    `planet_deuterium` = `planet_deuterium` + " . round($_POST['deut']) . "
    WHERE `planet_id` = '" . $this->current_planet['planet_id'] . "';"
    );

    $this->current_planet['planet_metal'] -= $necessaire;
    $this->current_planet['planet_crystal'] += isset($_POST['cristal']) ? $_POST['cristal'] : 0;
    $this->current_planet['planet_deuterium'] += isset($_POST['deut']) ? $_POST['deut'] : 0;

    $this->discountDarkMatter(); // REDUCE DARKMATTER
    } else {

    Functions::message(__['tr_not_enought_metal'], "game.php?page=traderOverview", 1);
    }
    }
    break;

    case 'cristal':
    if ($_POST['metal'] < 0 or $_POST['deut'] < 0) {

    Functions::message(__['tr_only_positive_numbers'], "game.php?page=traderOverview", 1);
    } else {

    $necessaire = ((abs($_POST['metal']) * 0.5) + (abs($_POST['deut']) * 2));
    $amout = array(
    'metal' => $_POST['metal'],
    'crystal' => 0,
    'deuterium' => $_POST['deut'],
    );

    $storage = $this->checkStorage($amout);

    if (is_string($storage)) {

    die(Functions::message($storage, 'game.php?page=traderOverview', '2'));
    }

    if ($this->current_planet['planet_crystal'] > $necessaire) {

    $this->db->query(
    "UPDATE " . PLANETS . " SET
    `planet_metal` = `planet_metal` + " . round($_POST['metal']) . ",
    `planet_crystal` = `planet_crystal` - " . round($necessaire) . ",
    `planet_deuterium` = `planet_deuterium` + " . round($_POST['deut']) . "
    WHERE `planet_id` = '" . $this->current_planet['planet_id'] . "';"
    );

    $this->current_planet['planet_metal'] += isset($_POST['metal']) ? $_POST['metal'] : 0;
    $this->current_planet['planet_crystal'] -= $necessaire;
    $this->current_planet['planet_deuterium'] += isset($_POST['deut']) ? $_POST['deut'] : 0;

    $this->discountDarkMatter(); // REDUCE DARKMATTER
    } else {

    Functions::message(__['tr_not_enought_crystal'], "game.php?page=traderOverview", 1);
    }
    }
    break;

    case 'deuterium':
    if ($_POST['cristal'] < 0 or $_POST['metal'] < 0) {

    Functions::message(__['tr_only_positive_numbers'], "game.php?page=traderOverview", 1);
    } else {

    $necessaire = ((abs($_POST['metal']) * 0.25) + (abs($_POST['cristal']) * 0.5));
    $amout = array(
    'metal' => $_POST['metal'],
    'crystal' => $_POST['cristal'],
    'deuterium' => 0,
    );

    $storage = $this->checkStorage($amout);

    if (is_string($storage)) {

    die(Functions::message($storage, 'game.php?page=traderOverview', '2'));
    }

    if ($this->current_planet['planet_deuterium'] > $necessaire) {

    $this->db->query(
    "UPDATE " . PLANETS . " SET
    `planet_metal` = `planet_metal` + " . round($_POST['metal']) . ",
    `planet_crystal` = `planet_crystal` + " . round($_POST['cristal']) . ",
    `planet_deuterium` = `planet_deuterium` - " . round($necessaire) . "
    WHERE `planet_id` = '" . $this->current_planet['planet_id'] . "';"
    );

    $this->current_planet['planet_metal'] += isset($_POST['metal']) ? $_POST['metal'] : 0;
    $this->current_planet['planet_crystal'] += isset($_POST['cristal']) ? $_POST['cristal'] : 0;
    $this->current_planet['planet_deuterium'] -= $necessaire;

    $this->discountDarkMatter(); // REDUCE DARKMATTER
    } else {

    Functions::message(__['tr_not_enought_deuterium'], "game.php?page=traderOverview", 1);
    }
    }
    break;
    }

    Functions::message(__['tr_exchange_done'], "game.php?page=traderOverview", 1);
    } else {

    $template = 'trader/trader_main';

    if (isset($_POST['action'])) {

    $parse['mod_ma_res'] = '1';

    switch ((isset($_POST['choix']) ? $_POST['choix'] : null)) {

    case 'metal':
    $template = 'trader/trader_metal';

    $parse['mod_ma_res_a'] = '2';
    $parse['mod_ma_res_b'] = '4';

    break;

    case 'cristal':
    $template = 'trader/trader_cristal';

    $parse['mod_ma_res_a'] = '0.5';
    $parse['mod_ma_res_b'] = '2';

    break;

    case 'deut':
    $template = 'trader/trader_deuterium';

    $parse['mod_ma_res_a'] = '0.25';
    $parse['mod_ma_res_b'] = '0.5';

    break;
    }
    }
    }

    $this->page->display(Template::render($template, $parse));*/
    //}

    /**
     * Get the kind of trader that we are requesting
     *
     * @return array
     */
    private function getMode(): array
    {
        $mode = filter_input(INPUT_GET, 'mode', FILTER_UNSAFE_RAW);
        $template = '';

        if (in_array($mode, ['traderResources', 'traderAuctioneer', 'traderScrap', 'traderImportExport'])) {
            $view_to_get = strtolower(strtr($mode, ['trader' => '']));
            $template = Template::render(
                'game/trader_' . $view_to_get . '_view',
                [
                    'list_of_resources' => $this->{'build' . ucfirst($view_to_get) . 'Section'}(),
                ]
            );
        }

        return [
            'currentMode' => $template,
        ];
    }

    /**
     * Build resources section
     */
    private function buildResourcesSection(): array
    {
        $list_of_resources = [];

        foreach (['metal' => 4500, 'crystal' => 9000, 'deuterium' => 13500] as $resource => $price) {
            $list_of_resources[] = [
                'resource' => $resource,
                'resource_name' => __('game/global' . $resource),
                'current_resource' => $this->formatService->shortlyNumber((float) $this->planet['planet_' . $resource]),
                'max_resource' => $this->formatService->shortlyNumber((float) $this->planet['planet_' . $resource . '_max']),
                'dark_matter_price_10' => $this->formatService->prettyNumber($price),
                'dark_matter_price_50' => $this->formatService->prettyNumber($price * 5),
                'dark_matter_price_100' => $this->formatService->prettyNumber($price * 10),
            ];
        }

        return $list_of_resources;
    }
}
