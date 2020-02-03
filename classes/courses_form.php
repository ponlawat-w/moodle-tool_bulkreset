<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/../lib.php');

class tool_bulkreset_courses_form extends moodleform {
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('html', html_writer::tag('p', get_string('selectcourses', 'tool_bulkreset')));

        $mform->addElement('html', tool_bulkreset_renderselectallallbuttons());

        $categories = core_course_category::get_all();
        foreach ($categories as $category) {
            if (!$category->coursecount) {
                continue;
            }

            $headername = "coursecategory_{$category->id}";
            $mform->addElement('header', $headername, $category->name);
            $mform->setExpanded($headername, true);

            $mform->addElement('html', tool_bulkreset_renderselectallbuttons());

            $courses = get_courses($category->id);
            foreach ($courses as $course) {
                $mform->addElement('advcheckbox', "courses[{$course->id}]", $course->fullname, '', ['courses' => 1]);
                $mform->setDefault("courses[{$course->id}]", 1);
            }
        }

        $mform->addElement('header', 'schedulingheader', get_string('scheduling', 'tool_bulkreset'));
        $mform->setExpanded('schedulingheader', true);

        $mform->addElement('date_time_selector', 'schedule', get_string('schedule', 'tool_bulkreset'));

        $this->add_action_buttons(true, get_string('continue'));
    }

    public function getforwarddata() {
        $data = $this->get_submitted_data();
        $courseids = [];
        foreach ($data->courses as $courseid => $value) {
            if ($value) {
                $courseids[] = $courseid;
            }
        }

        $schedule = isset($data->schedule) && $data->schedule ? $data->schedule : time();

        return (object)[
            'courses' => $courseids,
            'schedule' => $schedule
        ];
    }
}
