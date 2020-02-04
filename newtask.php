<?php

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/classes/courses_form.php');

admin_externalpage_setup('bulkreset');

$sorttype = optional_param('sort', TOOL_BULKRESET_SORT_SORTORDER, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/admin/tool/bulkreset/newtask.php', ['sort' => $sorttype]));
$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url("/{$CFG->admin}/tool/bulkreset/formscript.js"));

$coursesform = new tool_bulkreset_courses_form("{$CFG->wwwroot}/{$CFG->admin}/tool/bulkreset/resetsettings.php", $sorttype);

echo $OUTPUT->header();

echo html_writer::tag('h2', get_string('bulkreset', 'tool_bulkreset'));

$sortoptions = [
    TOOL_BULKRESET_SORT_SORTORDER => 'sortorder',
    TOOL_BULKRESET_SORT_NAME => 'name'
];
if (filter_is_enabled('multilang')) {
    $sortoptions[TOOL_BULKRESET_SORT_NAMEMULTILANG] = 'namemultilang';
}
$options = [];
foreach ($sortoptions as $value => $sortoption) {
    $options[] = html_writer::tag('option', get_string('sort_' . $sortoption, 'tool_bulkreset'),
        ['value' => $value]);
}

echo html_writer::tag('p',
    html_writer::tag('select',
        implode('', $options),
    ['class' => 'form-control', 'id' => 'tool-bulk_reset-sort_select']));

$coursesform->display();

echo $OUTPUT->footer();
