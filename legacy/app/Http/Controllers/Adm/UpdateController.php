<?php

namespace Xgp\App\Http\Controllers\Adm;

use Illuminate\Routing\Controller as BaseController;
use Xgp\App\Core\Template;
use Xgp\App\Libraries\Adm\AdministrationLib as Administration;
use Xgp\App\Libraries\Functions;
use Xgp\App\Models\Adm\Update;

class UpdateController extends BaseController
{
    private $system_version;
    private $db_version;
    private $demo;
    private $output = [];
    private Update $updateModel;

    public function __invoke(): void
    {
        Administration::checkSession();

        if (!Administration::authorization(__CLASS__)) {
            Administration::noAccessMessage(__('admin/global.no_permissions'));
            exit;
        }

        $this->updateModel = new Update();

        $this->buildPage();
    }

    private function buildPage(): void
    {
        $continue = true;
        $alerts = '';

        $this->system_version = config('version.files');
        $this->db_version = Functions::readConfig('version');

        if ($this->system_version == $this->db_version) {
            session()->flash('danger', __('admin/update.up_no_update_required'));
            $continue = false;
        }

        $parse['up_sub_title'] = sprintf(__('admin/update.up_sub_title'), $this->db_version, $this->system_version);

        if ($_POST && isset($_POST['send'])) {
            $this->demo = (isset($_POST['demo_mode']) && $_POST['demo_mode'] == 'on') ? true : false;

            if (!$this->checkVersion()) {
                $alerts = __('admin/update.up_no_version_file');
                $continue = false;
            }

            if ($continue) {
                $this->startUpdate();

                session()->flash('success', __('admin/update.up_success'));

                if ($this->demo) {
                    $parse['result'] = print_r($this->output, true);

                    Template::legacyView(
                        'admin.update_result',
                        $parse
                    );
                } else {
                    session()->flash('danger', __('admin/update.up_success'));
                    $continue = false;
                }
            } else {
                session()->flash('warning', $alerts);
            }
        }

        $parse['continue'] = $continue;

        Template::legacyView(
            'admin.update',
            $parse
        );
    }

    private function checkVersion(): bool
    {
        return file_exists(
            UPDATE_PATH . 'update_common.php'
        );
    }

    private function startUpdate(): void
    {
        $updates_dir = opendir(UPDATE_PATH);
        $exceptions = ['.', '..', '.htaccess', 'index.html', '.DS_Store', 'update_common.php'];
        $files_to_read = [];
        $db_version = strtr($this->db_version, ['v' => '', '.' => '']);

        while (($update_dir = readdir($updates_dir)) !== false) {
            if (!in_array($update_dir, $exceptions)) {
                $file_version = strtr(
                    $update_dir,
                    ['update_' => '', '.php' => '']
                );

                // ignore previous versions, we only want the newer ones
                if ($db_version >= $file_version) {
                    continue;
                }

                array_push($files_to_read, $file_version);
            }
        }

        // sort very important to keep versions order
        asort($files_to_read);

        // add common
        array_push($files_to_read, 'common');

        // Do we have something? Go...
        if (count($files_to_read) > 0) {
            foreach ($files_to_read as $version) {
                $this->executeFile($version);
            }
        }
    }

    private function executeFile(string $version): void
    {
        // Define some stuff
        $update_path = UPDATE_PATH . 'update_' . $version . '.php';
        $queries = [];

        require_once $update_path;

        // Check if there was something
        if (count($queries) > 0) {
            foreach ($queries as $query) {
                if (!$this->demo) {
                    $this->output[] = $this->updateModel->runQuery($query);
                } else {
                    $this->output[] = $query;
                }
            }
        }
    }
}
