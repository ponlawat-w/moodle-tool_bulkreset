<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

admin_externalpage_setup('bulkreset');

echo $OUTPUT->header();

$schedules = $DB->get_records('tool_bulkreset_schedules', [], 'starttime ASC');

$table = new html_table();
$table->head = [
    get_string('starttime', 'tool_bulkreset'),
    get_string('status'),
    get_string('action')
];
$table->data = [];
$now = time();
foreach ($schedules as $schedule) {
    if ($schedule->status == TOOL_BULKRESET_STATUS_SCHEDULED && $now > $schedule->starttime) {
        $schedule->status = TOOL_BULKRESET_STATUS_TOBEEXECUTED;
    }

    $actions = '';
    if ($schedule->status == TOOL_BULKRESET_STATUS_SUCCESS || $schedule->status == TOOL_BULKRESET_STATUS_WARNING || $schedule->status == TOOL_BULKRESET_STATUS_FAILED) {
        $actions .= ' ' . html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/schedulestatus.php", ['id' => $schedule->id]), get_string('view'));
    }
    if ($schedule->status != TOOL_BULKRESET_STATUS_EXECUTING) {
        $actions .= ' ' . html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/scheduledelete.php", ['id' => $schedule->id]), get_string('delete'), ['class' => 'text-danger']);
    }

    $table->data[] = [
        userdate($schedule->starttime),
        html_writer::span(tool_bulkreset_getstatustext($schedule->status), tool_bulkreset_getstatusclass($schedule->status)),
        $actions
    ];
}

if (optional_param('scheduled', 0, PARAM_INT)) {
    echo html_writer::div(get_string('scheduleadded', 'tool_bulkreset'), 'alert alert-success');
}
if (optional_param('deleted', 0, PARAM_INT)) {
    echo html_writer::div(get_string('scheduledeleted', 'tool_bulkreset'), 'alert alert-success');
}

if (count($schedules)) {
    echo html_writer::table($table);
} else {
    echo html_writer::div(get_string('noschedule', 'tool_bulkreset'), 'alert alert-info');
}

echo html_writer::start_div('', ['style' => 'text-align: center;']);
echo html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/newtask.php"),
    get_string('newtask', 'tool_bulkreset'),
    ['class' => 'btn btn-primary']);
echo ' ';
echo html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php", ['t' => time()]),
    get_string('refresh'),
    ['class' => 'btn btn-default']);
echo html_writer::end_div();

echo $OUTPUT->footer();
