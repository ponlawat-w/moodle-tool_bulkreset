<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk Course Reset
 *
 * @package    tool_bulkreset
 * @copyright  2020 Ponlawat Weerapanpisit, Adam Jenkins <adam@wisecat.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


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
