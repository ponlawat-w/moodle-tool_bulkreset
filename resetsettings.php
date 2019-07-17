<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/classes/courses_form.php');
require_once(__DIR__ . '/classes/resetsettings_form.php');

admin_externalpage_setup('bulkreset');
$coursesform = new tool_bulkreset_courses_form();
$resetsettingsform = new tool_bulkreset_resetsettings_form();

if (!$coursesform->is_submitted() && !$resetsettingsform->is_submitted()) {
    redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"));
    exit;
}

$courseids = $coursesform->is_submitted() ? $coursesform->getselectedcourseids() : $resetsettingsform->getcourseids();
$resetsettingsform = new tool_bulkreset_resetsettings_form($courseids);

if ($resetsettingsform->is_cancelled()) {
    redirect(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"));
    exit;
} else if ($data = $resetsettingsform->get_data()) {
    if (isset($data->selectdefault)) {
        $_POST = [];
        $resetsettingsform = new tool_bulkreset_resetsettings_form($courseids);
        $resetsettingsform->load_defaults();
    } else if (isset($data->deselectall)) {
        $_POST = [];
        $resetsettingsform = new tool_bulkreset_resetsettings_form($courseids);
    } else {
        echo $OUTPUT->header();
        echo html_writer::div(get_string('resetfinish', 'tool_bulkreset'), 'alert alert-success');
        $courses_resetdata = $resetsettingsform->getresetdata();
        foreach ($courses_resetdata as $resetdata) {
            $course = get_course($resetdata->courseid);
            $status = reset_course_userdata($resetdata);

            $tablerows = [];
            foreach ($status as $item) {
                $tablerows[] = [
                    $item['component'],
                    $item['item'],
                    ($item['error'] === false) ?
                        get_string('ok')
                        : '<div class="notifyproblem">'.$item['error'].'</div>'
                ];
            }
            $table = new html_table();
            $table->head = [get_string('resetcomponent'), get_string('resettask'), get_string('resetstatus')];
            $table->size = ['20%', '40%', '40%'];
            $table->align = ['left', 'left', 'left'];
            $table->data = $tablerows;

            echo html_writer::tag('h2', $course->fullname);
            echo html_writer::table($table);
            echo html_writer::link(new moodle_url('/course/view.php', ['id' => $course->id]), get_string('view'), ['class' => 'btn btn-primary', 'target' => '_blank']);
            echo html_writer::start_tag('hr');
        }
        echo html_writer::link(new moodle_url("/{$CFG->admin}/tool/bulkreset/index.php"), get_string('finish', 'tool_bulkreset'), ['class' => 'btn btn-default']);
        echo $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->header();
$resetsettingsform->display();
echo $OUTPUT->footer();
