<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('courses', new admin_externalpage('bulkreset', get_string('bulkreset', 'tool_bulkreset'),
        "{$CFG->wwwroot}/{$CFG->admin}/tool/bulkreset/index.php"));
}
