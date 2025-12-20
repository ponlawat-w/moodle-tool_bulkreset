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
 * Reset settings form
 *
 * @package     tool_bulkreset
 * @copyright   2020 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once(__DIR__ . '/../lib.php');

/**
 * Reset settings form
 */
class tool_bulkreset_resetsettings_form extends moodleform {
    /** @var stdClass $forwarddata */
    public $forwarddata = null;
    /** @var string[] $coursenames with array key being course ID. */
    public $coursenames;
    /** @var bool $inherited */
    public $inherited = false;

    /**
     * Constructor
     *
     * @param stdCalss|null $forwarddata
     * @param bool $inherited
     */
    public function __construct($forwarddata = null, $inherited = false) {
        $this->inherited = $inherited;
        if ($forwarddata) {
            $this->forwarddata = $forwarddata;
            $this->coursenames = [];
            $courseids = $this->getcourseids();
            foreach ($courseids as $courseid) {
                $course = get_course($courseid);
                $this->coursenames[$courseid] = $course ? $course->fullname : 'N/A';
            }
        }
        parent::__construct();
    }

    /**
     * Get course IDs
     *
     * @return int[]
     */
    private function getcourseids() {
        if ($this->inherited) {
            $courses = get_courses();
            $courseids = [];
            foreach ($courses as $course) {
                $courseids[] = $course->id;
            }
            return $courseids;
        } else if (!isset($this->forwarddata) || !isset($this->forwarddata->courses)) {
            return [];
        }
        return $this->forwarddata->courses;
    }

    /**
     * Get roles
     *
     * @return string[]
     */
    private function getroles() {
        $roles = [];
        $courseids = $this->getcourseids();
        foreach ($courseids as $courseid) {
            /** @var \context $coursecontext */
            $coursecontext = \core\context\course::instance($courseid);
            $courseroles = get_assignable_roles($coursecontext);
            foreach ($courseroles as $key => $value) {
                if (isset($roles[$key])) {
                    $roles[$key][] = $value;
                } else {
                    $roles[$key] = [$value];
                }
            }
        }
        $roles[0] = [get_string('noroles', 'role')];
        $roles = array_reverse($roles, true);
        $result = [];
        foreach ($roles as $key => $role) {
            $result[$key] = implode(' / ', array_unique($role));
        }
        return $result;
    }

    /**
     * Get courses in module name.
     *
     * @param string $modname
     * @return \stdClass[]
     */
    private function getcoursesinmod($modname) {
        if (!$this->forwarddata->courses || !count($this->forwarddata->courses)) {
            return [];
        }

        global $DB;
        $params = [];
        for ($i = 0; $i < count($this->forwarddata->courses); $i++) {
            $params[] = '?';
        }
        $paramsql = implode(',', $params);
        return $DB->get_records_sql(
            "SELECT DISTINCT course FROM {{$modname}} WHERE course IN ({$paramsql})",
            $this->forwarddata->courses
        );
    }

    /**
     * Get courses in module as HTML renderable.
     *
     * @param \stdClass[] $coursesinmodrecord
     * @return string
     */
    private function getcoursesinmodhtml($coursesinmodrecord) {
        $list = [];
        foreach ($coursesinmodrecord as $courseinmod) {
            $list[] = \core\output\html_writer::tag('li', $this->coursenames[$courseinmod->course]);
        }
        return \core\output\html_writer::tag('ul', implode('', $list), ['class' => 'small text-small']);
    }

    /**
     * Get forward data.
     *
     * @return \stdClass
     */
    public function getforwarddata() {
        if (!$this->is_submitted()) {
            return (object)[
                'courses' => [],
                'schedule' => 0,
                'settingstemplate' => null,
            ];
        }
        $data = $this->get_data();
        $courseids = (!$data || !$data->courses) ? $this->getcourseids() : explode(',', $data->courses);
        $schedule = isset($data->schedule) && $data->schedule ? $data->schedule : time();
        return (object)[
            'courses' => $courseids,
            'schedule' => $schedule,
            'settingstemplate' => $data->settingstemplate,
        ];
    }

    /**
     * Get reset data.
     *
     * @return \stdClass[]
     */
    public function getresetdata() {
        $courses = [];

        $courseids = $this->getcourseids();
        foreach ($courseids as $courseid) {
            $course = get_course($courseid);
            $data = $this->get_data();
            $data->id = $course->id;
            $data->courseid = $course->id;
            $data->reset_start_date_old = $course->startdate;
            $data->reset_end_date_old = $course->enddate;
            $courses[] = $data;
        }

        return $courses;
    }

    /**
     * Form definition
     *
     * @return void
     */
    protected function definition() {
        global $DB, $CFG;

        if (!$this->forwarddata) {
            $this->forwarddata = $this->getforwarddata();
        }

        $mform =& $this->_form;

        $mform->addElement('header', 'generalheader', get_string('general'));

        $mform->addElement('date_time_selector', 'reset_start_date', get_string('startdate'), ['optional' => true]);
        $mform->addHelpButton('reset_start_date', 'startdate');
        $mform->addElement('date_time_selector', 'reset_end_date', get_string('enddate'), ['optional' => true]);
        $mform->addHelpButton('reset_end_date', 'enddate');
        $mform->addElement('checkbox', 'reset_events', get_string('deleteevents', 'calendar'));
        $mform->addElement('checkbox', 'reset_notes', get_string('deletenotes', 'notes'));
        $mform->addElement('checkbox', 'reset_comments', get_string('deleteallcomments', 'moodle'));
        $mform->addElement('checkbox', 'reset_completion', get_string('deletecompletiondata', 'completion'));
        $mform->addElement('checkbox', 'delete_blog_associations', get_string('deleteblogassociations', 'blog'));
        $mform->addHelpButton('delete_blog_associations', 'deleteblogassociations', 'blog');
        $mform->addElement('checkbox', 'reset_competency_ratings', get_string('deletecompetencyratings', 'core_competency'));

        $mform->addElement('header', 'rolesheader', get_string('roles'));

        $roles = $this->getroles();

        $mform->addElement('select', 'unenrol_users', get_string('unenrolroleusers', 'enrol'), $roles, ['multiple' => 'multiple']);
        $mform->addElement('checkbox', 'reset_roles_overrides', get_string('deletecourseoverrides', 'role'));
        $mform->setAdvanced('reset_roles_overrides');
        $mform->addElement('checkbox', 'reset_roles_local', get_string('deletelocalroles', 'role'));

        $mform->addElement('header', 'gradebookheader', get_string('gradebook', 'grades'));

        $mform->addElement('checkbox', 'reset_gradebook_items', get_string('removeallcourseitems', 'grades'));
        $mform->addHelpButton('reset_gradebook_items', 'removeallcourseitems', 'grades');
        $mform->addElement('checkbox', 'reset_gradebook_grades', get_string('removeallcoursegrades', 'grades'));
        $mform->addHelpButton('reset_gradebook_grades', 'removeallcoursegrades', 'grades');
        $mform->disabledIf('reset_gradebook_grades', 'reset_gradebook_items', 'checked');

        $mform->addElement('header', 'groupheader', get_string('groups'));

        $mform->addElement('checkbox', 'reset_groups_remove', get_string('deleteallgroups', 'group'));
        $mform->addElement('checkbox', 'reset_groups_members', get_string('removegroupsmembers', 'group'));
        $mform->disabledIf('reset_groups_members', 'reset_groups_remove', 'checked');

        $mform->addElement('checkbox', 'reset_groupings_remove', get_string('deleteallgroupings', 'group'));
        $mform->addElement('checkbox', 'reset_groupings_members', get_string('removegroupingsmembers', 'group'));
        $mform->disabledIf('reset_groupings_members', 'reset_groupings_remove', 'checked');

        $unsupportedmods = [];
        if ($allmods = $DB->get_records('modules')) {
            foreach ($allmods as $mod) {
                $modname = $mod->name;
                $modfile = $CFG->dirroot . '/mod/$modname/lib.php';
                $modresetcourseformdefinition = $modname . '_reset_course_form_definition';
                $modresetuserdata = $modname . '_reset_userdata';
                if (file_exists($modfile)) {
                    if (!$this->inherited) {
                        $coursesinmod = $this->getcoursesinmod($modname);
                        if (!$coursesinmod || !count($coursesinmod)) {
                            continue;
                        }
                    }
                    include_once($modfile);
                    if (function_exists($modresetcourseformdefinition)) {
                        $modresetcourseformdefinition($mform);
                        if (!$this->inherited) {
                            $mform->addElement(
                                'static',
                                "coursesinmod_{$modname}",
                                get_string('coursesinmod', 'tool_bulkreset', get_string('modulenameplural', $modname)),
                                $this->getcoursesinmodhtml($coursesinmod)
                            );
                        }
                    } else if (!function_exists($modresetuserdata)) {
                        $unsupportedmods[] = $mod;
                    }
                } else {
                    debugging('Missing lib.php in ' . $modname . ' module');
                }
            }
        }
        // Mention unsupported mods.
        if (!empty($unsupportedmods)) {
            $mform->addElement('header', 'unsupportedheader', get_string('resetnotimplemented'));
            foreach ($unsupportedmods as $mod) {
                $mform->addElement('static', 'unsup' . $mod->name, get_string('modulenameplural', $mod->name));
                $mform->setAdvanced('unsup' . $mod->name);
            }
        }

        if (!$this->inherited) {
            $mform->addElement('hidden', 'courses', implode(',', $this->getcourseids()));
            $mform->setType('courses', PARAM_TEXT);
            $mform->addElement('hidden', 'schedule', $this->forwarddata->schedule);
            $mform->setType('schedule', PARAM_INT);
            $mform->addElement('hidden', 'settingstemplate', $this->forwarddata->settingstemplate);
            $mform->setType('settingstemplate', PARAM_TEXT);

            $buttonarray = [];
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('resetcourses', 'tool_bulkreset'));
            if (!tool_bulkreset_resetsettingsenabled()) {
                $buttonarray[] = &$mform->createElement('submit', 'selectdefault', get_string('selectdefault'));
                $buttonarray[] = &$mform->createElement('submit', 'deselectall', get_string('deselectall'));
            }
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
            $mform->closeHeaderBefore('buttonar');
        }
    }

    /**
     * Load default data.
     *
     * @return void
     */
    public function load_defaults() {
        global $CFG, $COURSE, $DB;

        $mform =& $this->_form;

        $defaults = [
            'reset_events' => 1,
            'reset_roles_local' => 1,
            'reset_gradebook_grades' => 1,
            'reset_notes' => 1,
        ];

        // Set student as default in unenrol user list, if role with student archetype exist.
        if ($studentrole = get_archetype_roles('student')) {
            $defaults['unenrol_users'] = array_keys($studentrole);
        }

        if ($allmods = $DB->get_records('modules')) {
            foreach ($allmods as $mod) {
                $modname = $mod->name;
                $modfile = $CFG->dirroot . '/mod/$modname/lib.php';
                $modresetcourseformdefaults = $modname . '_reset_course_form_defaults';
                if (file_exists($modfile)) {
                    @include_once($modfile);
                    if (function_exists($modresetcourseformdefaults)) {
                        if ($moddefs = $modresetcourseformdefaults($COURSE)) {
                            $defaults = $defaults + $moddefs;
                        }
                    }
                }
            }
        }

        foreach ($defaults as $element => $default) {
            $mform->setDefault($element, $default);
        }
    }
}
