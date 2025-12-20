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
 * Couses form
 *
 * @package     tool_bulkreset
 * @copyright   2020 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/../lib.php');

/**
 * Courses form
 */
class tool_bulkreset_courses_form extends moodleform {
    /** @var int $sort */
    public $sort = TOOL_BULKRESET_SORT_SORTORDER;

    /**
     * Constructor
     *
     * @param string|null $actionurl
     * @param int $sort
     */
    public function __construct($actionurl = null, $sort = TOOL_BULKRESET_SORT_SORTORDER) {
        $this->sort = $sort;
        parent::__construct($actionurl);
    }

    /**
     * Form definition
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('html', \core\output\html_writer::tag('p', get_string('selectcourses', 'tool_bulkreset')));

        $mform->addElement('html', tool_bulkreset_renderselectallallbuttons());

        $categories = tool_bulkreset_getcategories($this->sort);
        foreach ($categories as $category) {
            if (!$category->coursecount) {
                continue;
            }

            $headername = "coursecategory_{$category->id}";
            $mform->addElement('header', $headername, $category->get_nested_name(false));
            $mform->setExpanded($headername, true);

            $courses = get_courses($category->id);
            $mform->addElement('html', tool_bulkreset_renderselectallbuttons(count($courses) > 1));

            foreach ($courses as $course) {
                $mform->addElement('advcheckbox', "courses[{$course->id}]", $course->fullname, '', ['courses' => 1]);
                $mform->setDefault("courses[{$course->id}]", 1);
            }
        }

        $mform->addElement('html', \core\output\html_writer::start_tag('hr'));

        $mform->addElement('header', 'schedulingheader', get_string('scheduling', 'tool_bulkreset'));
        $mform->setExpanded('schedulingheader', true);

        $mform->addElement('date_time_selector', 'schedule', get_string('schedule', 'tool_bulkreset'));

        if (tool_bulkreset_resetsettingsenabled()) {
            $mform->addElement('header', 'settingstemplateheader', get_string('settingstemplateheader', 'tool_bulkreset'));

            $mform->setExpanded('settingstemplateheader', true);

            $settings = tool_bulkreset_getsettings();
            $mform->addElement('select', 'settingstemplate', get_string('settingstemplate', 'tool_bulkreset'), $settings);
            $mform->setType('settingstemplate', PARAM_TEXT);
            $mform->setDefault('settingstemplate', 'blank');

            $mform->addElement(
                'static',
                'gotoresetsettings',
                '',
                \core\output\html_writer::link(
                    new \core\url('/admin/tool/resetsettings'),
                    get_string('gotoresetsettings', 'tool_bulkreset')
                )
            );
        }

        $this->add_action_buttons(true, get_string('continue'));
    }

    /**
     * Get data to forward
     *
     * @return \stdClass
     */
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
            'schedule' => $schedule,
            'settingstemplate' => $data->settingstemplate,
        ];
    }
}
