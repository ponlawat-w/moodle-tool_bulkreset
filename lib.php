<?php

defined('MOODLE_INTERNAL') || die;

const TOOL_BULKRESET_STATUS_SCHEDULED = 0;
const TOOL_BULKRESET_STATUS_TOBEEXECUTED = 1;
const TOOL_BULKRESET_STATUS_EXECUTING = 2;
const TOOL_BULKRESET_STATUS_SUCCESS = 3;
const TOOL_BULKRESET_STATUS_WARNING = 4;
const TOOL_BULKRESET_STATUS_FAILED = 5;

function tool_bulkreset_renderselectallbuttons() {
    $selectall = html_writer::link('javascript:void(0);', get_string('selectall', 'tool_bulkreset'), ['class' => 'tool-bulkreset-selectall']);
    $deselectall = html_writer::link('javascript:void(0);', get_string('deselectall', 'tool_bulkreset'), ['class' => 'tool-bulkreset-deselectall']);
    return html_writer::div(
        $selectall . ' / ' . $deselectall
    );
}

function tool_bulkreset_renderselectallallbuttons() {
    $selectallall = html_writer::link('javascript:void(0);', get_string('selectallall', 'tool_bulkreset'), ['id' => 'tool-bulkreset-selectallall', 'class' => 'btn btn-primary']);
    $deselectallall = html_writer::link('javascript:void(0);', get_string('deselectallall', 'tool_bulkreset'), ['id' => 'tool-bulkreset-deselectallall', 'class' => 'btn btn-default']);
    return html_writer::tag('p',
        $selectallall . ' ' . $deselectallall
    );
}

function tool_bulkreset_getstatustext($status) {
    switch ($status) {
        case TOOL_BULKRESET_STATUS_SCHEDULED: return get_string('status_scheduled', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_TOBEEXECUTED: return get_string('status_tobeexecuted', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_EXECUTING: return get_string('status_executing', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_SUCCESS: return get_string('status_success', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_WARNING: return get_string('status_warning', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_FAILED: return get_string('status_failed', 'tool_bulkreset');
    }
    return get_string('status_unknown', 'tool_bulkreset');
}

function tool_bulkreset_getstatusclass($status) {
    switch ($status) {
        case TOOL_BULKRESET_STATUS_EXECUTING: return 'text-primary';
        case TOOL_BULKRESET_STATUS_SUCCESS: return 'text-success';
        case TOOL_BULKRESET_STATUS_WARNING: return 'text-warning';
        case TOOL_BULKRESET_STATUS_FAILED: return 'text-danger';
    }
    return '';
}

function tool_bulkreset_exceptiontoassoc($exorerr) {
    return [
        'message' => $exorerr->getMessage(),
        'code' => $exorerr->getCode(),
        'file' => $exorerr->getFile(),
        'line' => $exorerr->getLine(),
        'trace' => $exorerr->getTraceAsString()
    ];
}

function tool_bulkreset_geterrortable($item) {
    $table = new html_table();
    $table->data = [
        [html_writer::span('message','',  ['style' => 'font-weight: bold;']), $item->message],
        [html_writer::span('code', '', ['style' => 'font-weight: bold;']), $item->code],
        [html_writer::span('file', '', ['style' => 'font-weight: bold;']), $item->file],
        [html_writer::span('line', '', ['style' => 'font-weight: bold;']), $item->line],
        [html_writer::span('trace', '', ['style' => 'font-weight: bold;']), nl2br(htmlspecialchars($item->trace))]
    ];
    return $table;
}

function tool_bulkreset_executeschedule($schedule) {
    global $DB;
    $data = json_decode($schedule->data);
    $schedule->status = TOOL_BULKRESET_STATUS_EXECUTING;
    $DB->update_record('tool_bulkreset_schedules', $schedule);
    if (!is_array($data->courses)) {
        $data->courses = explode(',', $data->courses);
    }

    $results = [];

    $successall = true;
    foreach ($data->courses as $courseid) {
        $coursesuccess = false;
        try {
            $course = get_course($courseid);
            $data = clone($data);
            $data->id = $course->id;
            $data->courseid = $course->id;
            $data->reset_start_date_old = $course->startdate;
            $data->reset_end_date_old = $course->enddate;

            $result = reset_course_userdata($data);
            $coursesuccess = true;
            foreach ($result as $item) {
                $success = $item['error'] === false;
                $successall = $successall && $success;
            }
        } catch (Exception $ex) {
            mtrace('Exception thrown!');
            $successall = false;
            $result = tool_bulkreset_exceptiontoassoc($ex);
        } catch (Error $err) {
            mtrace('Error!');
            $successall = false;
            $result = tool_bulkreset_exceptiontoassoc($err);
        }

        $results[] = [
            'courseid' => $course->id,
            'success' => $coursesuccess,
            'result' => $result
        ];
    }

    if ($successall) {
        $schedule->status = TOOL_BULKRESET_STATUS_SUCCESS;
    } else {
        $schedule->status = TOOL_BULKRESET_STATUS_WARNING;
    }

    $schedule->result = json_encode($results);

    $DB->update_record('tool_bulkreset_schedules', $schedule);

    return true;
}
