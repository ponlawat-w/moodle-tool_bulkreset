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
