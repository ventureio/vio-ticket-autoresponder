<?php

use Illuminate\Database\Capsule\Manager as DB;

if (!class_exists('base_addon')) {
    require_once __DIR__ . '/base_addon.php';
}

class ticket_autoresponder_addon extends base_addon {

    public function __construct($vars = null) {
        $this->getModuleConfig();
        $this->modulePath = dirname(__DIR__);
        if (!empty($vars['_lang'])) {
            $this->setLanguage($vars['_lang']);
        }
    }

    protected function unserializeConfig() {
        if (!is_array($this->moduleSettings['weekend'])) {
            $this->moduleSettings['weekend'] = unserialize(html_entity_decode($this->moduleSettings['weekend']));
        }
    }

    protected function getModuleConfig() {
        if (!empty($this->moduleSettings)) {
            return $this->moduleSettings;
        }
        $result = array();
        $rows = DB::table('tbladdonmodules')
                ->where('module', 'vio_ticket_autoresponder')
                ->get();
        foreach ($rows as $row) {

            $result[$row->setting] = $row->value;
        }
        $this->moduleSettings = $result;
        $this->unserializeConfig();
        return $this->moduleSettings;
    }

    protected function getEmailTemplates() {
        $result = array();
        $rows = DB::table('tblemailtemplates')
                ->where('type', 'general')
                ->where('custom', 1)
                ->get();
        foreach ($rows as $row) {
            $result[$row->id] = $row->name;
        }
        return $result;
    }

    protected function updateConfig($data) {
        DB::table('tbladdonmodules')
                ->where('module', 'vio_ticket_autoresponder')
                ->whereNotIn('setting', array('version', 'access'))
                ->delete();
        $data['weekend'] = serialize($data['weekend']);
        foreach ($data as $k => $v) {
            DB::table('tbladdonmodules')->insert(array(
                'module' => 'vio_ticket_autoresponder',
                'setting' => $k,
                'value' => $v,
            ));
        }
    }

    public function indexAction() {
        if (!empty($_POST)) {
            $data = $_POST;
            unset($data['token']);
            $this->updateConfig($data);
            $this->redirect('addonmodules.php?module=vio_ticket_autoresponder&success=1');
        }
        echo $this->getSmartyTemplate('taConfig', array(
            'emailTemplates' => $this->getEmailTemplates(),
            'config' => $this->moduleSettings,
            'show_message' => !empty($_REQUEST['success'])
        ));
    }

    public function autoreplyCheck() {
        if (!empty($this->moduleSettings['holidays_email']) && $this->isHoliday()) {
            return DB::table('tblemailtemplates')->find($this->moduleSettings['holidays_email']);
        } elseif (!empty($this->moduleSettings['closed_email']) && $this->isOutOfOfficeTime()) {
            return DB::table('tblemailtemplates')->find($this->moduleSettings['closed_email']);
        }
    }

    protected function isHoliday() {
        if (!empty($this->moduleSettings['holidays'])) {
            $currentDate = date('m/d/Y');
            return !!strpos($this->moduleSettings['holidays'], $currentDate);
        }
        return false;
    }

    protected function isOutOfOfficeTime() {
        if (!empty($this->moduleSettings['weekend'])) {
            $currentDay = date('D');
            if (in_array($currentDay, $this->moduleSettings['weekend'])) {
                return true;
            } else {
                $startHours = intval($this->moduleSettings['from_hours']);
                if($startHours == 12) {
                    $startHours = 0;
                }
                if (!empty($this->moduleSettings['from_ampm']) && $this->moduleSettings['from_ampm'] == 'pm') {
                    $startHours += 12;
                }
                if ($startHours < 0 || $startHours > 23) {
                    $startHours = 0;
                }
                $startMins = intval($this->moduleSettings['from_mins']);
                if ($startMins < 0 || $startMins > 59) {
                    $startMins = 0;
                }

                $endHours = intval($this->moduleSettings['to_hours']);
                if($endHours == 12) {
                    $endHours = 0;
                }
                if (!empty($this->moduleSettings['to_ampm']) && $this->moduleSettings['to_ampm'] == 'pm') {
                    $endHours += 12;
                }
                if ($endHours < 0 || $endHours > 23) {
                    $endHours = 23;
                }
                $endMins = intval($this->moduleSettings['to_mins']);
                if ($endMins < 0 || $endMins > 59) {
                    $endMins = 59;
                }

                $startWorkTime = strtotime($startHours . ':' . $startMins . ':00');
                $endWorkTime = strtotime($endHours . ':' . $endMins . ':00');
                if(time() < $startWorkTime || time() > $endWorkTime) {
                    return true;
                }
            }
        }
        return false;
    }

}
