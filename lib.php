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

defined('MOODLE_INTERNAL') || die;

const TOOL_BULKRESET_STATUS_SCHEDULED = 0;
const TOOL_BULKRESET_STATUS_TOBEEXECUTED = 1;
const TOOL_BULKRESET_STATUS_EXECUTING = 2;
const TOOL_BULKRESET_STATUS_SUCCESS = 3;
const TOOL_BULKRESET_STATUS_WARNING = 4;
const TOOL_BULKRESET_STATUS_FAILED = 5;

const TOOL_BULKRESET_SORT_SORTORDER = 1;
const TOOL_BULKRESET_SORT_NAME = 2;
const TOOL_BULKRESET_SORT_NAMEMULTILANG = 3;

function tool_bulkreset_renderselectallbuttons($show = true) {
    $selectall = html_writer::link('javascript:void(0);',
        get_string('selectall', 'tool_bulkreset'), ['class' => 'tool-bulkreset-selectall']);
    $deselectall = html_writer::link('javascript:void(0);',
        get_string('deselectall', 'tool_bulkreset'), ['class' => 'tool-bulkreset-deselectall']);
    return html_writer::div(
        $selectall . ' | ' . $deselectall
    , '', ['style' => 'display:' . ($show ? 'block' : 'none') . ';']);
}

function tool_bulkreset_renderselectallallbuttons() {
    $selectallall = html_writer::link('javascript:void(0);',
       get_string('selectallall', 'tool_bulkreset'),
       ['id' => 'tool-bulkreset-selectallall', 'class' => 'btn btn-primary']);
    $deselectallall = html_writer::link('javascript:void(0);',
       get_string('deselectallall', 'tool_bulkreset'),
       ['id' => 'tool-bulkreset-deselectallall', 'class' => 'btn btn-default']);
    return html_writer::tag('p',
        $selectallall . ' ' . $deselectallall
    );
}

function tool_bulkreset_getstatustext($status) {
    switch ($status) {
        case TOOL_BULKRESET_STATUS_SCHEDULED: return get_string('status_scheduled', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_TOBEEXECUTED: return get_string('status_tobeexecuted', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_EXECUTING: return get_string('status_executing', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_SUCCESS: return get_string('status_success', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_WARNING: return get_string('status_warning', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_FAILED: return get_string('status_failed', 'tool_bulkreset');
    }
    return get_string('status_unknown', 'tool_bulkreset');
}

function tool_bulkreset_getstatusclass($status) {
    switch ($status) {
        case TOOL_BULKRESET_STATUS_EXECUTING: return 'text-primary';
        case TOOL_BULKRESET_STATUS_SUCCESS: return 'text-success';
        case TOOL_BULKRESET_STATUS_WARNING: return 'text-warning';
        case TOOL_BULKRESET_STATUS_FAILED: return 'text-danger';
    }
    return '';
}

function tool_bulkreset_exceptiontoassoc($exorerr) {
    return [
        'message' => $exorerr->getMessage(),
        'code' => $exorerr->getCode(),
        'file' => $exorerr->getFile(),
        'line' => $exorerr->getLine(),
        'trace' => $exorerr->getTraceAsString(),
    ];
}

function tool_bulkreset_geterrortable($item) {
    $table = new html_table();
    $table->data = [
        [html_writer::span('message', '',  ['style' => 'font-weight: bold;']), $item->message],
        [html_writer::span('code', '', ['style' => 'font-weight: bold;']), $item->code],
        [html_writer::span('file', '', ['style' => 'font-weight: bold;']), $item->file],
        [html_writer::span('line', '', ['style' => 'font-weight: bold;']), $item->line],
        [html_writer::span('trace', '', ['style' => 'font-weight: bold;']), nl2br(htmlspecialchars($item->trace))],
    ];
    return $table;
}

function tool_bulkreset_executeschedule($schedule) {
    global $DB;
    $data = json_decode($schedule->data);
    $schedule->status = TOOL_BULKRESET_STATUS_EXECUTING;
    $DB->update_record('tool_bulkreset_schedules', $schedule);
    if (!is_array($data->courses)) {
        $data->courses = explode(',', $data->courses);
    }

    $results = [];

    $successall = true;
    foreach ($data->courses as $courseid) {
        $coursesuccess = false;
        try {
            $course = get_course($courseid);
            $data = clone($data);
            $data->id = $course->id;
            $data->courseid = $course->id;
            $data->reset_start_date_old = $course->startdate;
            $data->reset_end_date_old = $course->enddate;

            $result = reset_course_userdata($data);
            $coursesuccess = true;
            foreach ($result as $item) {
                $success = $item['error'] === false;
                $successall = $successall && $success;
            }
        } catch (Exception $ex) {
            mtrace('Exception thrown!');
            $successall = false;
            $result = tool_bulkreset_exceptiontoassoc($ex);
        } catch (Error $err) {
            mtrace('Error!');
            $successall = false;
            $result = tool_bulkreset_exceptiontoassoc($err);
        }

        $results[] = [
            'courseid' => $course->id,
            'success' => $coursesuccess,
            'result' => $result,
        ];
    }

    if ($successall) {
        $schedule->status = TOOL_BULKRESET_STATUS_SUCCESS;
    } else {
        $schedule->status = TOOL_BULKRESET_STATUS_WARNING;
    }

    $schedule->result = json_encode($results);

    $DB->update_record('tool_bulkreset_schedules', $schedule);

    return true;
}

function tool_bulkreset_getcategoriesbyid($categories) {
    $results = [];
    foreach ($categories as $category) {
        $results[$category->id] = $category;
    }
    return $results;
}

function tool_bulkreset_getcategorypathnames($category, $categories) {
    $paths = explode('/', $category->path);
    $pathnames = [];
    foreach ($paths as $path) {
        if (!$path || !$categories[$path]) {
            continue;
        }
        $pathnames[] = $categories[$path]->name;
    }
    return $pathnames;
}

function tool_bulkreset_getcategorytrees($categories) {
    $trees = [];
    foreach ($categories as $category) {
        $paths = explode('/', $category->path);
        if (!$paths[0]) {
            $paths = array_slice($paths, 1);
        }
        if (!isset($trees[$paths[0]])) {
            $trees[$paths[0]] = [$paths[0]];
        }
        $pointer = &$trees[$paths[0]];
        for ($i = 1; $i < count($paths); $i++) {
            $path = $paths[$i];
            if (!isset($pointer[$path])) {
                $pointer[$path] = [$path];
            }
            $pointer = &$pointer[$path];
        }
    }
    return $trees;
}

function tool_bulkreset_filtermultilang($text) {
    global $toolbulkresetfiltermultilang;
    $context = context_system::instance();
    if (!isset($toolbulkresetfiltermultilang)) {
        $filters = filter_get_active_in_context($context);
        foreach ($filters as $filtername => $localconfig) {
            if ($filtername == 'multilang') {
                require_once(__DIR__ . '/../../../filter/multilang/filter.php');
                $toolbulkresetfiltermultilang = new filter_multilang($context, $localconfig);
                break;
            }
        }
    }

    if (!isset($toolbulkresetfiltermultilang)) {
        throw new moodle_exception('Filter is not active');
    }

    return $toolbulkresetfiltermultilang->filter($text);
}

function tool_bulkreset_comparecategory($a, $b, $sortby) {
    if ($a->depth == $b->depth) {
        if ($sortby == TOOL_BULKRESET_SORT_NAMEMULTILANG) {
            if (!filter_is_enabled('multilang')) {
                return strcmp($a->name, $b->name);
            }
            return strcmp(
                tool_bulkreset_filtermultilang($a->name),
                tool_bulkreset_filtermultilang($b->name)
            );
        }
        switch ($sortby) {
            case TOOL_BULKRESET_SORT_SORTORDER:
                return $a->sortorder - $b->sortorder;
            case TOOL_BULKRESET_SORT_NAME:
                return strcmp($a->name, $b->name);
        }
        return $a->sortorder - $b->sortorder;
    }
    return $a->depth - $b->depth;
}

function tool_bulkreset_flattencategorytrees(&$results, $trees, $categories) {
    foreach ($trees as $child) {
        if (is_array($child)) {
            tool_bulkreset_flattencategorytrees($results, $child, $categories);
        } else {
            $results[] = $categories[$child];
        }
    }
}

function tool_bulkreset_getcategories($sortby = TOOL_BULKRESET_SORT_SORTORDER) {
    $categories = core_course_category::get_all();
    usort($categories, function($a, $b) use ($sortby) {
        return tool_bulkreset_comparecategory($a, $b, $sortby);
    });
    $categoriesbyid = tool_bulkreset_getcategoriesbyid($categories);
    $trees = tool_bulkreset_getcategorytrees($categories);
    $results = [];
    tool_bulkreset_flattencategorytrees($results, $trees, $categoriesbyid);
    return $results;
}

function tool_bulkreset_resetsettingsenabled() {
    global $toolbulkresetresetsettingsenabled;

    if (!isset($toolbulkresetresetsettingsenabled)) {
        $toolbulkresetresetsettingsenabled = false;
        $tools = core_plugin_manager::instance()->get_plugins_of_type('tool');
        foreach ($tools as $tool) {
            if ($tool->name == 'resetsettings') {
                $toolbulkresetresetsettingsenabled = true;
                break;
            }
        }
    }

    return $toolbulkresetresetsettingsenabled;
}

function tool_bulkreset_getsettings() {
    global $DB;
    $settings = [
        'blank' => get_string('settings_blank', 'tool_bulkreset'),
        'default' => get_string('settings_default', 'tool_bulkreset'),
    ];
    $templates = $DB->get_records_sql('SELECT id, name FROM {tool_resetsettings_settings} ORDER BY name ASC');
    foreach ($templates as $template) {
        $settings[$template->id] = $template->name;
    }
    return $settings;
}
