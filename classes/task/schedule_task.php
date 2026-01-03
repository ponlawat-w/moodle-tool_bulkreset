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
 * Schedule task
 *
 * @package     tool_bulkreset
 * @copyright   2020 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_bulkreset\task;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../../lib.php');
require_once(__DIR__ . '/../../../../../course/lib.php');
require_once(__DIR__ . '/../../../../../course/format/lib.php');

/**
 * Schedule task for cronjob
 */
class schedule_task extends \core\task\scheduled_task {
    /**
     * Get task name
     *
     * @return string
     */
    public function get_name() {
        return get_string('scheduletask', 'tool_bulkreset');
    }

    /**
     * Execute bulk reset
     *
     * @return bool
     */
    public function execute() {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        $schedules = $DB->get_records_sql(
            'SELECT * FROM {tool_bulkreset_schedules} WHERE status = ? AND starttime < ?',
            [TOOL_BULKRESET_STATUS_SCHEDULED, time()]
        );
        foreach ($schedules as $schedule) {
            mtrace('Executing schedule #' . $schedule->id);
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
