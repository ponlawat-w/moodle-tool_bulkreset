<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/deleteconfirm_form.php');

admin_externalpage_setup('bulkreset');

$scheduleid = required_param('id', PARAM_INT);
$schedule = $DB->get_record('tool_bulkreset_schedules', ['id' => $scheduleid]);
if (!$schedule) {
    throw new moodle_exception('Schedule not found');
}
if ($schedule->status == TOOL_BULKRESET_STATUS_EXECUTING) {
    throw new moodle_exception('Cannot delete executing task');
}

$confirmform = new deleteconfirm_form();

if ($confirmform->is_cancelled()) {
    redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"));
} else if ($confirmform->is_submitted()) {
    $DB->delete_records('tool_bulkreset_schedules', ['id' => $schedule->id]);
    redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php", ['deleted' => 1]));
}

echo $OUTPUT->header();

$confirmform->display();

echo $OUTPUT->footer();
