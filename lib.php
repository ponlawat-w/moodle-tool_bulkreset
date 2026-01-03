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
 * Plugin library.
 *
 * @package     tool_bulkreset
 * @copyright   2020 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/** @var int TOOL_BULKRESET_STATUS_SCHEDULED schedule status scheduled */
const TOOL_BULKRESET_STATUS_SCHEDULED = 0;
/** @var int TOOL_BULKRESET_STATUS_TOBEEXECUTED schedule status tobeexecuted */
const TOOL_BULKRESET_STATUS_TOBEEXECUTED = 1;
/** @var int TOOL_BULKRESET_STATUS_EXECUTING schedule status executing */
const TOOL_BULKRESET_STATUS_EXECUTING = 2;
/** @var int TOOL_BULKRESET_STATUS_SUCCESS schedule status success */
const TOOL_BULKRESET_STATUS_SUCCESS = 3;
/** @var int TOOL_BULKRESET_STATUS_WARNING schedule status warning */
const TOOL_BULKRESET_STATUS_WARNING = 4;
/** @var int TOOL_BULKRESET_STATUS_FAILED schedule status failed */
const TOOL_BULKRESET_STATUS_FAILED = 5;

/** @var int TOOL_BULKRESET_SORT_SORTORDER sort order by sortorder */
const TOOL_BULKRESET_SORT_SORTORDER = 1;
/** @var int TOOL_BULKRESET_SORT_NAME sort order by name */
const TOOL_BULKRESET_SORT_NAME = 2;
/** @var int TOOL_BULKRESET_SORT_NAMEMULTILANG sort order by namemultilang */
const TOOL_BULKRESET_SORT_NAMEMULTILANG = 3;

/**
 * Render select all buttons.
 *
 * @param bool $show
 * @return string
 */
function tool_bulkreset_renderselectallbuttons($show = true) {
    $selectall = \core\output\html_writer::link(
        'javascript:void(0);',
        get_string('selectall', 'tool_bulkreset'),
        ['class' => 'tool-bulkreset-selectall']
    );
    $deselectall = \core\output\html_writer::link(
        'javascript:void(0);',
        get_string('deselectall', 'tool_bulkreset'),
        ['class' => 'tool-bulkreset-deselectall']
    );
    return \core\output\html_writer::div(
        $selectall . ' | ' . $deselectall,
        '',
        ['style' => 'display: ' . ($show ? 'block' : 'none') . ';']
    );
}

/**
 * Render select all / deselect all buttons for selecting list of courses to be reset.
 *
 * @return string
 */
function tool_bulkreset_renderselectallallbuttons() {
    $selectallall = \core\output\html_writer::link(
        'javascript:void(0);',
        get_string('selectallall', 'tool_bulkreset'),
        ['id' => 'tool-bulkreset-selectallall', 'class' => 'btn btn-primary']
    );
    $deselectallall = \core\output\html_writer::link(
        'javascript:void(0);',
        get_string('deselectallall', 'tool_bulkreset'),
        ['id' => 'tool-bulkreset-deselectallall', 'class' => 'btn btn-default']
    );
    return \core\output\html_writer::tag(
        'p',
        $selectallall . ' ' . $deselectallall
    );
}

/**
 * Get status text.
 *
 * @param int $status
 * @return string
 */
function tool_bulkreset_getstatustext($status) {
    switch ($status) {
        case TOOL_BULKRESET_STATUS_SCHEDULED:
            return get_string('status_scheduled', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_TOBEEXECUTED:
            return get_string('status_tobeexecuted', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_EXECUTING:
            return get_string('status_executing', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_SUCCESS:
            return get_string('status_success', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_WARNING:
            return get_string('status_warning', 'tool_bulkreset');
        case TOOL_BULKRESET_STATUS_FAILED:
            return get_string('status_failed', 'tool_bulkreset');
    }
    return get_string('status_unknown', 'tool_bulkreset');
}

/**
 * Get status CSS class.
 *
 * @param string $status
 * @return string
 */
function tool_bulkreset_getstatusclass($status) {
    switch ($status) {
        case TOOL_BULKRESET_STATUS_EXECUTING:
            return 'text-primary';
        case TOOL_BULKRESET_STATUS_SUCCESS:
            return 'text-success';
        case TOOL_BULKRESET_STATUS_WARNING:
            return 'text-warning';
        case TOOL_BULKRESET_STATUS_FAILED:
            return 'text-danger';
    }
    return '';
}

/**
 * Convert exception to assoc array for logging.
 *
 * @param \Exception|\Error $exorerr
 * @return array
 */
function tool_bulkreset_exceptiontoassoc($exorerr) {
    return [
        'message' => $exorerr->getMessage(),
        'code' => $exorerr->getCode(),
        'file' => $exorerr->getFile(),
        'line' => $exorerr->getLine(),
        'trace' => $exorerr->getTraceAsString(),
    ];
}

/**
 * Get error table.
 *
 * @param \stdClass $item
 * @return \core_table\output\html_table
 */
function tool_bulkreset_geterrortable($item) {
    $table = new \core_table\output\html_table();
    $table->data = [
        [\core\output\html_writer::span('message', '', ['style' => 'font-weight: bold;']), $item->message],
        [\core\output\html_writer::span('code', '', ['style' => 'font-weight: bold;']), $item->code],
        [\core\output\html_writer::span('file', '', ['style' => 'font-weight: bold;']), $item->file],
        [\core\output\html_writer::span('line', '', ['style' => 'font-weight: bold;']), $item->line],
        [\core\output\html_writer::span('trace', '', ['style' => 'font-weight: bold;']), nl2br(htmlspecialchars($item->trace))],
    ];
    return $table;
}

/**
 * Execute schedule
 *
 * @param \stdClass $schedule
 * @return void
 */
function tool_bulkreset_executeschedule($schedule) {
    global $DB;
    /** @var \moodle_database $DB */
    $DB;

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

/**
 * Get an array of course categories with array key being category ID and the value being the category object.
 *
 * @param \core_course_category[] $categories
 * @return \core_course_category[]
 */
function tool_bulkreset_getcategoriesbyid($categories) {
    $results = [];
    foreach ($categories as $category) {
        $results[$category->id] = $category;
    }
    return $results;
}

/**
 * Get category path names
 *
 * @param \stdClass $category
 * @param \stdClass[] $categories
 * @return string[]
 */
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

/**
 * Get category trees.
 *
 * @param array $categories
 * @return array
 */
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

/**
 * Filter multilang
 *
 * @param string $text
 * @return string
 */
function tool_bulkreset_filtermultilang($text) {
    global $toolbulkresetfiltermultilang;
    $context = context_system::instance();
    if (!isset($toolbulkresetfiltermultilang)) {
        $filters = filter_get_active_in_context($context);
        foreach ($filters as $filtername => $localconfig) {
            if ($filtername == 'multilang') {
                require_once(__DIR__ . '/../../../filter/multilang/filter.php');
                $toolbulkresetfiltermultilang = new \filter_multilang\text_filter($context, $localconfig);
                break;
            }
        }
    }

    if (!isset($toolbulkresetfiltermultilang)) {
        throw new \core\exception\moodle_exception('Filter is not active');
    }

    return $toolbulkresetfiltermultilang->filter($text);
}

/**
 * Compare sort order between two course categories.
 *
 * @param \core_course_category $a
 * @param \core_course_category $b
 * @param int $sortby
 * @return int
 */
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

/**
 * Flatten category trees.
 *
 * @param \core_course_category[] $results
 * @param array $trees
 * @param array $categories
 * @return void
 */
function tool_bulkreset_flattencategorytrees(&$results, $trees, $categories) {
    foreach ($trees as $child) {
        if (is_array($child)) {
            tool_bulkreset_flattencategorytrees($results, $child, $categories);
        } else {
            $results[] = $categories[$child];
        }
    }
}

/**
 * Get all course categories and sub-categories in a flattended array.
 *
 * @param int $sortby
 * @return \core_course_category[]
 */
function tool_bulkreset_getcategories($sortby = TOOL_BULKRESET_SORT_SORTORDER) {
    $categories = core_course_category::get_all();
    usort(
        $categories,
        function ($a, $b) use ($sortby) {
            return tool_bulkreset_comparecategory($a, $b, $sortby);
        }
    );
    $categoriesbyid = tool_bulkreset_getcategoriesbyid($categories);
    $trees = tool_bulkreset_getcategorytrees($categories);
    $results = [];
    tool_bulkreset_flattencategorytrees($results, $trees, $categoriesbyid);
    return $results;
}

/**
 * Test if reset settings templates plugin is installed and enabled.
 *
 * @return bool
 */
function tool_bulkreset_resetsettingsenabled(): bool {
    $resetsettingsplugin = core_plugin_manager::instance()->get_plugin_info('tool_resetsettings');
    if (!$resetsettingsplugin) {
        return false;
    }
    return $resetsettingsplugin->is_enabled();
}

/**
 * Get settings templates list.
 * This function does not check if tool_resetsettings is installed.
 *
 * @return array
 */
function tool_bulkreset_getsettings() {
    global $DB;
    /** @var \moodle_database $DB */
    $DB;
    $settings = [
        'blank' => get_string('settings_blank', 'tool_bulkreset'),
        'default' => get_string('settings_default', 'tool_bulkreset'),
    ];
    $templates = $DB->get_records('tool_resetsettings_settings', [], 'name ASC', 'id, name');
    foreach ($templates as $template) {
        $settings[$template->id] = $template->name;
    }
    return $settings;
}
