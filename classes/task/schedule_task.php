<?php

namespace tool_bulkreset\task;

class schedule_task extends \core\task\scheduled_task {

    public function get_name() {
        get_string('schedule_task', 'tool_bulkreset');
    }

    public function execute() {
        global $DB;
        require_once(__DIR__ . '/../../lib.php');
        $schedules = $DB->get_records_sql('SELECT * FROM {tool_bulkreset_schedules} WHERE status = ? AND starttime < ?', [TOOL_BULKRESET_STATUS_SCHEDULED, time()]);
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
