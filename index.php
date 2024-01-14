<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk Course Reset
 *
 * @package    tool_bulkreset
 * @copyright  2020 Ponlawat Weerapanpisit, Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


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
    get_string('action'),
];
$table->data = [];
$now = time();
foreach ($schedules as $schedule) {
    if ($schedule->status == TOOL_BULKRESET_STATUS_SCHEDULED && $now > $schedule->starttime) {
        $schedule->status = TOOL_BULKRESET_STATUS_TOBEEXECUTED;
    }

    $actions = '';
    if ($schedule->status == TOOL_BULKRESET_STATUS_SUCCESS ||
        $schedule->status == TOOL_BULKRESET_STATUS_WARNING ||
        $schedule->status == TOOL_BULKRESET_STATUS_FAILED) {
        $actions .= ' ' . html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/schedulestatus.php",
        ['id' => $schedule->id]), get_string('view'));
    }
    if ($schedule->status != TOOL_BULKRESET_STATUS_EXECUTING) {
        $actions .= ' ' . html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/scheduledelete.php",
        ['id' => $schedule->id]), get_string('delete'), ['class' => 'text-danger']);
    }

    $table->data[] = [
        userdate($schedule->starttime),
        html_writer::span(tool_bulkreset_getstatustext($schedule->status), tool_bulkreset_getstatusclass($schedule->status)),
        $actions,
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
