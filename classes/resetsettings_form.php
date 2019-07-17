<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/course/lib.php');

class tool_bulkreset_resetsettings_form extends moodleform {
    public $courseids;
    public $coursenames;

    public function __construct($courseids = []) {
        $this->courseids = $courseids;
        $this->coursenames = [];
        foreach ($this->courseids as $courseid) {
            $course = get_course($courseid);
            $this->coursenames[$courseid] = $course ? $course->fullname : 'N/A';
        }
        parent::__construct();
    }

    private function getroles() {
        $roles = [];
        foreach ($this->courseids as $courseid) {
            $courseroles = get_assignable_roles(context_course::instance($courseid));
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

    private function getcoursesinmod($modname) {
        if (!$this->courseids || !count($this->courseids)) {
            return [];
        }

        global $DB;
        $params = [];
        for ($i = 0; $i < count($this->courseids); $i++) {
            $params[] = '?';
        }
        $paramsql = implode(',', $params);
        return $DB->get_records_sql("SELECT DISTINCT course FROM {{$modname}} WHERE course IN ({$paramsql})", $this->courseids);
    }

    private function getcoursesinmodhtml($coursesinmodrecord) {
        $list = [];
        foreach ($coursesinmodrecord as $courseinmod) {
            $list[] = html_writer::tag('li', $this->coursenames[$courseinmod->course]);
        }
        return html_writer::tag('ul', implode('', $list), ['class' => 'small text-small']);
    }

    public function getcourseids() {
        $data = $this->get_data();
        if (!$data || !$data->courses) {
            return [];
        }
        return explode(',', $data->courses);
    }

    public function getresetdata() {
        $courses = [];

        foreach ($this->courseids as $courseid) {
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

    function definition() {
        global $DB, $CFG;

        if (!count($this->courseids)) {
            $this->courseids = $this->getcourseids();
        }

        $mform =& $this->_form;

        $mform->addElement('header', 'generalheader', get_string('general'));

        $mform->addElement('date_time_selector', 'reset_start_date', get_string('startdate'), array('optional' => true));
        $mform->addHelpButton('reset_start_date', 'startdate');
        $mform->addElement('date_time_selector', 'reset_end_date', get_string('enddate'), array('optional' => true));
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

        $mform->addElement('select', 'unenrol_users', get_string('unenrolroleusers', 'enrol'), $roles, array('multiple' => 'multiple'));
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

        $unsupported_mods = array();
        if ($allmods = $DB->get_records('modules') ) {
            foreach ($allmods as $mod) {
                $modname = $mod->name;
                $modfile = $CFG->dirroot."/mod/$modname/lib.php";
                $mod_reset_course_form_definition = $modname.'_reset_course_form_definition';
                $mod_reset__userdata = $modname.'_reset_userdata';
                if (file_exists($modfile)) {
                    $coursesinmod = $this->getcoursesinmod($modname);
                    if (!$coursesinmod || !count($coursesinmod)) {
                        continue;
                    }
                    include_once($modfile);
                    if (function_exists($mod_reset_course_form_definition)) {
                        $mod_reset_course_form_definition($mform);
                        $mform->addElement(
                            'static',
                            "coursesinmod_{$modname}",
                            get_string('coursesinmod', 'tool_bulkreset', get_string('modulenameplural', $modname)),
                            $this->getcoursesinmodhtml($coursesinmod));
                    } else if (!function_exists($mod_reset__userdata)) {
                        $unsupported_mods[] = $mod;
                    }
                } else {
                    debugging('Missing lib.php in '.$modname.' module');
                }
            }
        }
        // mention unsupported mods
        if (!empty($unsupported_mods)) {
            $mform->addElement('header', 'unsupportedheader', get_string('resetnotimplemented'));
            foreach($unsupported_mods as $mod) {
                $mform->addElement('static', 'unsup'.$mod->name, get_string('modulenameplural', $mod->name));
                $mform->setAdvanced('unsup'.$mod->name);
            }
        }

        $mform->addElement('hidden', 'courses', implode(',', $this->courseids));
        $mform->setType('courses', PARAM_TEXT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('resetcourses', 'tool_bulkreset'));
        $buttonarray[] = &$mform->createElement('submit', 'selectdefault', get_string('selectdefault'));
        $buttonarray[] = &$mform->createElement('submit', 'deselectall', get_string('deselectall'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    function load_defaults() {
        global $CFG, $COURSE, $DB;

        $mform =& $this->_form;

        $defaults = array ('reset_events'=>1, 'reset_roles_local'=>1, 'reset_gradebook_grades'=>1, 'reset_notes'=>1);

        // Set student as default in unenrol user list, if role with student archetype exist.
        if ($studentrole = get_archetype_roles('student')) {
            $defaults['unenrol_users'] = array_keys($studentrole);
        }

        if ($allmods = $DB->get_records('modules') ) {
            foreach ($allmods as $mod) {
                $modname = $mod->name;
                $modfile = $CFG->dirroot."/mod/$modname/lib.php";
                $mod_reset_course_form_defaults = $modname.'_reset_course_form_defaults';
                if (file_exists($modfile)) {
                    @include_once($modfile);
                    if (function_exists($mod_reset_course_form_defaults)) {
                        if ($moddefs = $mod_reset_course_form_defaults($COURSE)) {
                            $defaults = $defaults + $moddefs;
                        }
                    }
                }
            }
        }

        foreach ($defaults as $element=>$default) {
            $mform->setDefault($element, $default);
        }
    }
}
