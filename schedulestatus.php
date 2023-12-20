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

$scheduleid = required_param('id', PARAM_INT);
$schedule = $DB->get_record('tool_bulkreset_schedules', ['id' => $scheduleid]);
if (!$schedule) {
    throw new moodle_exception('Schedule not found');
}
if ($schedule->status == TOOL_BULKRESET_STATUS_SCHEDULED ||
    $schedule->status == TOOL_BULKRESET_STATUS_TOBEEXECUTED ||
    $schedule->status == TOOL_BULKRESET_STATUS_EXECUTING) {
    throw new moodle_exception('Schedule is not finished yet');
}

$result = json_decode($schedule->result);

echo $OUTPUT->header();
echo html_writer::start_tag('p');
echo html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"), get_string('back'));
echo html_writer::end_tag('p');

$statustable = new html_table();
$statustable->data = [
    [get_string('starttime', 'tool_bulkreset'), userdate($schedule->starttime)],
    [get_string('status'), html_writer::span(tool_bulkreset_getstatustext($schedule->status),
     tool_bulkreset_getstatusclass($schedule->status))],
];

if ($schedule->status == TOOL_BULKRESET_STATUS_FAILED) {
    $statustable->data[] = [
        '', html_writer::table(tool_bulkreset_geterrortable($result)),
    ];
} else {
    foreach ($result as $courseresult) {
        $course = get_course($courseresult->courseid);
        if ($courseresult->success) {
            $coursetable = new html_table();
            $coursetable->head = [get_string('resetcomponent'), get_string('resettask'), get_string('resetstatus')];
            $coursetable->data = [];
            foreach ($courseresult->result as $item) {
                $coursetable->data[] = [
                    $item->component,
                    $item->item,
                    ($item->error === false) ?
                        get_string('ok')
                        : '<div class="notifyproblem">'.$item->error.'</div>',
                ];
            }
            $coursestatustext = html_writer::span(tool_bulkreset_getstatustext(TOOL_BULKRESET_STATUS_SUCCESS),
            tool_bulkreset_getstatusclass(TOOL_BULKRESET_STATUS_SUCCESS));
        } else {
            $coursetable = tool_bulkreset_geterrortable($courseresult->result);
            $coursestatustext = html_writer::span(tool_bulkreset_getstatustext(TOOL_BULKRESET_STATUS_FAILED),
            tool_bulkreset_getstatusclass(TOOL_BULKRESET_STATUS_FAILED));
        }

        $statustable->data[] = [
            html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), $course->fullname, ['target' => '_blank']),
            html_writer::tag('p', $coursestatustext) . html_writer::table($coursetable),
        ];
    }
}

echo html_writer::table($statustable);

echo html_writer::start_div('', ['style' => 'text-align: center;']);
echo html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"),
    get_string('back'),
    ['class' => 'btn btn-default']);
echo html_writer::end_div();

echo $OUTPUT->footer();
