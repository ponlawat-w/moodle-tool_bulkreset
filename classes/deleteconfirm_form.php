<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once(__DIR__ . '/../lib.php');

class deleteconfirm_form extends moodleform {
    public $scheduleid;

    public function __construct() {
        $this->scheduleid = required_param('id', PARAM_INT);
        parent::__construct();
    }

    public function definition() {
        $mform = $this->_form;
        $mform->addElement('html', get_string('deleteconfirm', 'tool_bulkreset'));
        $mform->addElement('hidden', 'id', $this->scheduleid);
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons(true, get_string('yes'));
    }
}
