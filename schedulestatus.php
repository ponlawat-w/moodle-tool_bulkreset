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
 * Schedule status page.
 *
 * @package     tool_bulkreset
 * @copyright   2020 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

admin_externalpage_setup('bulkreset');

$scheduleid = required_param('id', PARAM_INT);

/** @var \moodle_page $PAGE */
$PAGE;
$PAGE->set_url(new \core\url('/admin/tool/bulkreset/schedulestatus.php'), ['id' => $scheduleid]);
$PAGE->set_title(get_string('bulkresettaskstatus', 'tool_bulkreset'));
$PAGE->set_heading(get_string('bulkresettaskstatus', 'tool_bulkreset'));

$schedule = $DB->get_record('tool_bulkreset_schedules', ['id' => $scheduleid]);
if (!$schedule) {
    throw new \core\exception\moodle_exception('Schedule not found');
}
if (
    $schedule->status == TOOL_BULKRESET_STATUS_SCHEDULED
    || $schedule->status == TOOL_BULKRESET_STATUS_TOBEEXECUTED
    || $schedule->status == TOOL_BULKRESET_STATUS_EXECUTING
) {
    throw new \core\exception\moodle_exception('Schedule is not finished yet');
}

$result = json_decode($schedule->result);

echo $OUTPUT->header();
echo \core\output\html_writer::start_tag('p');
echo \core\output\html_writer::end_tag('p');

$statustable = new \core_table\output\html_table();
$statustable->data = [
    [get_string('starttime', 'tool_bulkreset'), userdate($schedule->starttime)],
    [
        get_string('status'),
        \core\output\html_writer::span(
            tool_bulkreset_getstatustext($schedule->status),
            tool_bulkreset_getstatusclass($schedule->status)
        ),
    ],
];

if ($schedule->status == TOOL_BULKRESET_STATUS_FAILED) {
    $statustable->data[] = [
        '',
        \core\output\html_writer::table(tool_bulkreset_geterrortable($result)),
    ];
} else {
    foreach ($result as $courseresult) {
        $course = get_course($courseresult->courseid);
        if ($courseresult->success) {
            $coursetable = new \core_table\output\html_table();
            $coursetable->head = [get_string('resetcomponent'), get_string('resettask'), get_string('resetstatus')];
            $coursetable->data = [];
            foreach ($courseresult->result as $item) {
                $coursetable->data[] = [
                    $item->component,
                    $item->item,
                    ($item->error === false) ?
                        get_string('ok')
                        : '<div class="notifyproblem">' . $item->error . '</div>',
                ];
            }
            $coursestatustext = \core\output\html_writer::span(
                tool_bulkreset_getstatustext(TOOL_BULKRESET_STATUS_SUCCESS),
                tool_bulkreset_getstatusclass(TOOL_BULKRESET_STATUS_SUCCESS)
            );
        } else {
            $coursetable = tool_bulkreset_geterrortable($courseresult->result);
            $coursestatustext = \core\output\html_writer::span(
                tool_bulkreset_getstatustext(TOOL_BULKRESET_STATUS_FAILED),
                tool_bulkreset_getstatusclass(TOOL_BULKRESET_STATUS_FAILED)
            );
        }

        $statustable->data[] = [
            \core\output\html_writer::link(
                new \core\url('/course/view.php', ['id' => $course->id]),
                $course->fullname,
                ['target' => '_blank']
            ),
            \core\output\html_writer::tag('p', $coursestatustext) . \core\output\html_writer::table($coursetable),
        ];
    }
}

echo \core\output\html_writer::table($statustable);

echo \core\output\html_writer::start_div('', ['style' => 'text-align: center;']);
echo \core\output\html_writer::link(
    new \core\url('/' . $CFG->admin . '/tool/bulkreset/schedules.php'),
    get_string('back'),
    ['class' => 'btn btn-secondary mt-4']
);
echo \core\output\html_writer::end_div();

echo $OUTPUT->footer();
