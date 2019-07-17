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

        $this->add_action_buttons(false, get_string('continue'));
    }

    public function getselectedcourseids() {
        $data = $this->get_submitted_data();
        if (!$data || !$data->courses) {
            return [];
        }

        $ids = [];
        foreach ($data->courses as $courseid => $value) {
            if ($value) {
                $ids[] = $courseid;
            }
        }

        return $ids;
    }
}
