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
    TOOL_BULKRESET_SORT_NAME => 'name',
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
