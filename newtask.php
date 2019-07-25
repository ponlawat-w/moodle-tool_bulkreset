<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/classes/courses_form.php');

admin_externalpage_setup('bulkreset');

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url("/{$CFG->admin}/tool/bulkreset/formscript.js"));

$coursesform = new tool_bulkreset_courses_form("{$CFG->wwwroot}/{$CFG->admin}/tool/bulkreset/resetsettings.php");

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('bulkreset', 'tool_bulkreset'));

$coursesform->display();

echo $OUTPUT->footer();
