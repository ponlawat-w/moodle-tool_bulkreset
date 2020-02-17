<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/classes/courses_form.php');
require_once(__DIR__ . '/classes/resetsettings_form.php');

admin_externalpage_setup('bulkreset');
$coursesform = new tool_bulkreset_courses_form();
$resetsettingsform = new tool_bulkreset_resetsettings_form();

if ($coursesform->is_cancelled() || $resetsettingsform->is_cancelled()) {
    redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"));
    exit;
}
if (!$coursesform->is_submitted() && !$resetsettingsform->is_submitted()) {
    redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"));
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
        redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php", ['scheduled' => 1]));
    }
} else if (is_numeric($forwarddata->settingstemplate)
    && $forwarddata->settingstemplate
    && tool_bulkreset_resetsettingsenabled()) {
    $setting = $DB->get_record('tool_resetsettings_settings', ['id' => $forwarddata->settingstemplate]);
    $resetsettingsform->set_data(json_decode($setting->data));
} else if ($forwarddata->settingstemplate == 'default') {
    $resetsettingsform->load_defaults();
}

echo $OUTPUT->header();
$resetsettingsform->display();
echo $OUTPUT->footer();
