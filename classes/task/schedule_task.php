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


namespace tool_bulkreset\task;

class schedule_task extends \core\task\scheduled_task {

    public function get_name() {
        get_string('schedule_task', 'tool_bulkreset');
    }

    public function execute() {
        global $DB;
        require_once(__DIR__ . '/../../lib.php');
        $schedules = $DB->get_records_sql('SELECT * FROM {tool_bulkreset_schedules} WHERE status = ? AND starttime < ?',
        [TOOL_BULKRESET_STATUS_SCHEDULED, time()]);
        foreach ($schedules as $schedule) {
            mtrace("Executing schedule #{$schedule->id}");
            try {
                tool_bulkreset_executeschedule($schedule);
            } catch (\Exception $ex) {
                $schedule->status = TOOL_BULKRESET_STATUS_FAILED;
                $schedule->result = tool_bulkreset_exceptiontoassoc($ex);
                $DB->update_record('tool_bulkreset_schedules', $schedule);
            } catch (\Error $err) {
                $schedule->status = TOOL_BULKRESET_STATUS_FAILED;
                $schedule->result = tool_bulkreset_exceptiontoassoc($err);
                $DB->update_record('tool_bulkreset_schedules', $schedule);
            }
        }
        return true;
    }
}
