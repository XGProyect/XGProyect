<?php

namespace Xgp\App\Http\Controllers\Adm;

use App\Services\AdministrationService;
use App\Services\SettingsService;
use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Enumerators\AllianceRanksEnumerator as AllianceRanks;
use Xgp\App\Core\Enumerators\SwitchIntEnumerator as SwitchInt;
use Xgp\App\Core\Options;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Alliance\Ranks;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Alliances;

class AlliancesController extends BaseController
{
    private $edit;
    private $id;
    private $alliance_query;
    private ?Ranks $ranks = null;
    private Alliances $alliancesModel;
    private AdministrationService $administrationService;

    public function __construct()
    {
        $this->administrationService = new AdministrationService(
            new SettingsService()
        );
    }

    public function __invoke(): void
    {
        $this->administrationService->checkSession();
        $this->administrationService->authorization(__CLASS__);

        $this->alliancesModel = new Alliances();

        $alliance = isset($_GET['alliance']) ? trim($_GET['alliance']) : null;
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;
        $this->edit = isset($_GET['edit']) ? trim($_GET['edit']) : null;

        if ($alliance != '') {
            if (!$this->checkAlliance($alliance)) {
                session()->flash('danger', __('admin/alliances.al_nothing_found'));
                $alliance = '';
            } else {
                $this->alliance_query = $this->alliancesModel->getAllAllianceDataById($this->id);
                $this->ranks = new Ranks($this->alliance_query['alliance_ranks']);

                if ($_POST) {
                    // save the data
                    $this->saveData($type);
                }
            }
        }

        $parse['al_sub_title'] = '';
        $parse['type'] = ($type != '') ? $type : 'info';
        $parse['alliance'] = ($alliance != '') ? $alliance : '';
        $parse['status'] = ($alliance != '') ? '' : ' disabled';
        $parse['status_box'] = ($alliance != '') ? '' : ' disabled';
        $parse['tag'] = ($alliance != '') ? 'a' : 'button';
        $parse['content'] = ($alliance != '' && $type != '') ? $this->getData($type) : '';

        Template::legacyView(
            'admin.alliances',
            $parse
        );
    }

    private function getData(string $type): string
    {
        switch ($type) {
            case 'info':
            case '':
            default:
                return $this->getDataInfo();
                break;
            case 'ranks':
                return $this->getDataRanks();
                break;
            case 'members':
                return $this->getDataMembers();
                break;
        }
    }

    private function saveData(string $type = ''): void
    {
        switch ($type) {
            case 'info':
            case '':
            default:
                // save the data
                if (isset($_POST['send_data']) && $_POST['send_data']) {
                    $this->saveInfo();
                }
                break;
            case 'ranks':
                $this->saveRanks();
                break;
            case 'members':
                $this->saveMembers();
                break;
        }
    }

    //#####################################
    //
    // getData methods
    //
    //#####################################

    private function getDataInfo(): string
    {
        $parse = (array) $this->alliance_query;
        $parse['al_alliance_information'] = str_replace('%s', $this->alliance_query['alliance_name'], __('admin/alliances.al_alliance_information'));
        $parse['alliance_register_time'] = ($this->alliance_query['alliance_register_time'] == 0) ? '-' : date(Options::getInstance()->get('date_format_extended'), $this->alliance_query['alliance_register_time']);
        $parse['alliance_owner_picker'] = $this->buildUsersCombo($this->alliance_query['alliance_owner']);
        $parse['sel1'] = $this->alliance_query['alliance_request_notallow'] == 1 ? 'selected' : '';
        $parse['sel0'] = $this->alliance_query['alliance_request_notallow'] == 0 ? 'selected' : '';

        return Template::render('admin.alliances_information', $parse);
    }

    private function getDataRanks(): string
    {
        $parse['al_alliance_ranks'] = str_replace('%s', $this->alliance_query['alliance_name'], __('admin/alliances.al_alliance_ranks'));
        $parse['image_path'] = DEFAULT_SKINPATH;
        $alliance_ranks = $this->ranks->getAllRanksAsArray();
        $i = 0;
        $rank_row = '';

        // build the UI
        $rank_data = [];

        if (is_array($alliance_ranks)) {
            foreach ($alliance_ranks as $details) {
                $rank_data['name'] = $details['rank'];
                $rank_data['delete'] = (($details['rights'][AllianceRanks::DELETE] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['kick'] = (($details['rights'][AllianceRanks::KICK] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['bewerbungen'] = (($details['rights'][AllianceRanks::APPLICATIONS] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['memberlist'] = (($details['rights'][AllianceRanks::VIEW_MEMBER_LIST] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['bewerbungenbearbeiten'] = (($details['rights'][AllianceRanks::APPLICATION_MANAGEMENT] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['administrieren'] = (($details['rights'][AllianceRanks::ADMINISTRATION] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['onlinestatus'] = (($details['rights'][AllianceRanks::ONLINE_STATUS] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['mails'] = (($details['rights'][AllianceRanks::SEND_CIRCULAR] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['rechtehand'] = (($details['rights'][AllianceRanks::RIGHT_HAND] == SwitchInt::on) ? ' checked="checked"' : '');
                $rank_data['i'] = $i++;

                $rank_row .= Template::render('admin.alliances_ranks_row', $rank_data);
            }
        }

        $parse['ranks_table'] = empty($rank_row) ? __('admin/alliances.al_no_ranks') : $rank_row;

        return Template::render('admin.alliances_ranks', $parse);
    }

    private function getDataMembers(): string
    {
        $parse['al_alliance_members'] = str_replace(
            '%s',
            $this->alliance_query['alliance_name'],
            __('admin/alliances.al_alliance_members')
        );
        $all_members = $this->alliancesModel->getAllianceMembers($this->id);

        $members = '';

        if (!empty($all_members)) {
            foreach ($all_members as $member) {
                $member['alliance_request'] = ($member['ally_request']) ? __('admin/alliances.al_request_yes') : __('admin/alliances.al_request_no');
                $member['ally_request_text'] = ($member['ally_request_text']) ? $member['ally_request_text'] : '-';
                $member['alliance_register_time'] = date(Options::getInstance()->get('date_format_extended'), $member['ally_register_time']);

                if (isset($member['ally_rank_id'])) {
                    $member['ally_rank'] = $this->ranks->getRankById($member['ally_rank_id'])['rank'];
                } else {
                    $member['ally_rank'] = __('admin/alliances.al_rank_not_defined');
                }

                $members .= Template::render('admin.alliances_members_row', $member);
            }
        }

        $parse['members_table'] = empty($members) ? '<tr><td colspan="6" class="align_center text-error">' . __('admin/alliances.al_no_ranks') . '</td></tr>' : $members;

        return Template::render('admin.alliances_members', $parse);
    }

    //#####################################
    //
    // save / update methods
    //
    //#####################################

    private function saveInfo(): void
    {
        $alliance_name = isset($_POST['alliance_name']) ? $_POST['alliance_name'] : '';
        $alliance_name_orig = isset($_POST['alliance_name_orig']) ? $_POST['alliance_name_orig'] : '';
        $alliance_tag = isset($_POST['alliance_tag']) ? $_POST['alliance_tag'] : '';
        $alliance_tag_orig = isset($_POST['alliance_tag_orig']) ? $_POST['alliance_tag_orig'] : '';
        $alliance_owner = isset($_POST['alliance_owner']) ? $_POST['alliance_owner'] : '';
        $alliance_owner_orig = isset($_POST['alliance_owner_orig']) ? $_POST['alliance_owner_orig'] : '';
        $alliance_web = isset($_POST['alliance_web']) ? $_POST['alliance_web'] : '';
        $alliance_image = isset($_POST['alliance_image']) ? $_POST['alliance_image'] : '';
        $alliance_description = isset($_POST['alliance_description']) ? $_POST['alliance_description'] : '';
        $alliance_text = isset($_POST['alliance_text']) ? $_POST['alliance_text'] : '';
        $alliance_request = isset($_POST['alliance_request']) ? $_POST['alliance_request'] : '';
        $alliance_request_notallow = isset($_POST['alliance_request_notallow']) ? $_POST['alliance_request_notallow'] : '';

        $alliance_owner = (int) $alliance_owner;
        $alliance_request_notallow = (int) $alliance_request_notallow;
        $errors = '';

        if ($alliance_name != $alliance_name_orig) {
            if ($alliance_name == '' or !$this->alliancesModel->checkAllianceName($alliance_name)) {
                $errors .= __('admin/alliances.al_error_alliance_name') . '<br>';
            }
        }

        if ($alliance_tag != $alliance_tag_orig) {
            if ($alliance_tag == '' or !$this->alliancesModel->checkAllianceTag($alliance_tag)) {
                $errors .= __('admin/alliances.al_error_alliance_tag') . '<br>';
            }
        }

        if ($alliance_owner != $alliance_owner_orig) {
            if ($alliance_owner <= 0 or $this->alliancesModel->checkAllianceFounder($alliance_owner)) {
                $errors .= __('admin/alliances.al_error_founder') . '<br>';
            }
        }

        if ($errors != '') {
            session()->flash('warning', $errors);
        } else {
            $this->alliancesModel->updateAllianceData([
                'alliance_name' => $alliance_name,
                'alliance_tag' => $alliance_tag,
                'alliance_owner' => $alliance_owner,
                'alliance_web' => $alliance_web,
                'alliance_image' => $alliance_image,
                'alliance_description' => $alliance_description,
                'alliance_text' => $alliance_text,
                'alliance_request' => $alliance_request,
                'alliance_request_notallow' => $alliance_request_notallow,
                'alliance_id' => $this->id,
            ]);

            session()->flash('success', __('admin/alliances.al_all_ok_message'));
        }
    }

    private function saveRanks(): void
    {
        if (isset($_POST['create_rank'])) {
            if (!empty($_POST['rank_name'])) {
                $this->ranks->addNew(
                    $_POST['rank_name']
                );

                $this->alliancesModel->updateAllianceRanks(
                    $this->id,
                    $this->ranks->getAllRanksAsJsonString()
                );

                session()->flash('success', __('admin/alliances.al_rank_added'));
            } else {
                session()->flash('warning', __('admin/alliances.al_required_name'));
            }
        }

        // edit rights for each rank
        if (isset($_POST['save_ranks']) && !empty($_POST['id'])) {
            foreach ($_POST['id'] as $id) {
                $this->ranks->editRankById(
                    $id,
                    [
                        AllianceRanks::DELETE => isset($_POST['u' . $id . 'r1']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::KICK => isset($_POST['u' . $id . 'r2']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::APPLICATIONS => isset($_POST['u' . $id . 'r3']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::VIEW_MEMBER_LIST => isset($_POST['u' . $id . 'r4']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::APPLICATION_MANAGEMENT => isset($_POST['u' . $id . 'r5']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::ADMINISTRATION => isset($_POST['u' . $id . 'r6']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::ONLINE_STATUS => isset($_POST['u' . $id . 'r7']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::SEND_CIRCULAR => isset($_POST['u' . $id . 'r8']) ? SwitchInt::on : SwitchInt::off,
                        AllianceRanks::RIGHT_HAND => isset($_POST['u' . $id . 'r9']) ? SwitchInt::on : SwitchInt::off,
                    ]
                );
            }

            $this->alliancesModel->updateAllianceRanks(
                $this->id,
                $this->ranks->getAllRanksAsJsonString()
            );

            session()->flash('success', __('admin/alliances.al_rank_saved'));
        }

        // delete a rank
        if (isset($_POST['delete_message'])) {
            foreach ($_POST['delete_message'] as $rankId) {
                $this->ranks->deleteRankById($rankId);
            }

            $this->alliancesModel->updateAllianceRanks(
                $this->id,
                $this->ranks->getAllRanksAsJsonString()
            );

            session()->flash('success', __('admin/alliances.al_rank_removed'));
        }

        Functions::redirect('admin.php?' . $_SERVER['QUERY_STRING']);
    }

    private function saveMembers(): void
    {
        if (isset($_POST['delete_members'])) {
            $ids_string = '';

            if (isset($_POST['delete_message'])) {
                foreach ($_POST['delete_message'] as $userId => $delete_status) {
                    if ($delete_status == 'on' && $userId > 0 && is_numeric($userId)) {
                        $ids_string .= $userId . ',';
                    }
                }

                $amount = $this->alliancesModel->countAllianceMembers($this->id);

                if ($amount['Amount'] > 1) {
                    $this->alliancesModel->removeAllianceMembers($ids_string);

                    session()->flash('success', __('admin/alliances.us_all_ok_message'));
                } else {
                    session()->flash('warning', __('admin/alliances.al_cant_delete_last_one'));
                }
            }
        }
    }

    //#####################################
    //
    // build combo methods
    //
    //#####################################

    private function buildUsersCombo($userId): string
    {
        $combo_rows = '';
        $users = $this->alliancesModel->getAllUsers();

        foreach ($users as $users_row) {
            $combo_rows .= '<option value="' . $users_row['id'] . '" ' . ($users_row['id'] == $userId ? ' selected' : '') . '>' . $users_row['name'] . '</option>';
        }

        return $combo_rows;
    }

    //#####################################
    //
    // other required methods
    //
    //#####################################

    private function checkAlliance(string $alliance): bool
    {
        if ($alliance_query = $this->alliancesModel->checkAllianceByNameOrTag($alliance)) {
            $this->id = $alliance_query['alliance_id'];

            return true;
        }

        return false;
    }
}
