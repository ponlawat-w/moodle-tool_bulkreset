<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Reset settings page.
 *
 * @package     tool_bulkreset
 * @copyright   2020 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/classes/courses_form.php');
require_once(__DIR__ . '/classes/resetsettings_form.php');

admin_externalpage_setup('bulkreset');
$coursesform = new tool_bulkreset_courses_form();
$resetsettingsform = new tool_bulkreset_resetsettings_form();

if ($coursesform->is_cancelled() || $resetsettingsform->is_cancelled()) {
    redirect(new \core\url('/' . $CFG->admin . '/tool/bulkreset/index.php'));
    exit;
}
if (!$coursesform->is_submitted() && !$resetsettingsform->is_submitted()) {
    redirect(new \core\url('/' . $CFG->admin . '/tool/bulkreset/index.php'));
    exit;
}

$forwarddata = $coursesform->is_submitted() ? $coursesform->getforwarddata() : $resetsettingsform->getforwarddata();
$resetsettingsform = new tool_bulkreset_resetsettings_form($forwarddata);

if ($data = $resetsettingsform->get_data()) {
    if (isset($data->selectdefault)) {
        $_POST = [];
        $resetsettingsform = new tool_bulkreset_resetsettings_form($forwarddata);
        $resetsettingsform->load_defaults();
    } else if (isset($data->deselectall)) {
        $_POST = [];
        $resetsettingsform = new tool_bulkreset_resetsettings_form($forwarddata);
    } else {
        $schedule = new stdClass();
        $schedule->starttime = $data->schedule;
        $schedule->status = TOOL_BULKRESET_STATUS_SCHEDULED;
        unset($data->settingstemplate);
        unset($data->submitbutton);
        $schedule->data = json_encode($data);
        $DB->insert_record('tool_bulkreset_schedules', $schedule);
        redirect(new \core\url("/{$CFG->admin}/tool/bulkreset/index.php", ['scheduled' => 1]));
    }
} else if (
    is_numeric($forwarddata->settingstemplate)
    && $forwarddata->settingstemplate
    && tool_bulkreset_resetsettingsenabled()
) {
    $setting = $DB->get_record('tool_resetsettings_settings', ['id' => $forwarddata->settingstemplate]);
    $resetsettingsform->set_data(json_decode($setting->data));
} else if ($forwarddata->settingstemplate == 'default') {
    $resetsettingsform->load_defaults();
}

echo $OUTPUT->header();
$resetsettingsform->display();
echo $OUTPUT->footer();
