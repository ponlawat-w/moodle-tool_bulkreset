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
 * JavaScript for course selection page
 *
 * @module      too/bulkreset
 * @copyright   2025 Ponlawat Weerapanpisit <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

/**
 * Binds click event to select all / deselect all buttons.
 */
export const init = () => {
    const $selectallcheckboxes = $('.tool-bulkreset-selectall');
    const $deselectallcheckboxes = $('.tool-bulkreset-deselectall');
    const $selectallall = $('#tool-bulkreset-selectallall');
    const $deselectallall = $('#tool-bulkreset-deselectallall');
    const $sortselect = $('#tool-bulk_reset-sort_select');

    const setall = (event, value) => {
        $(event.target).parent().parent().find('input[type="checkbox"]').prop('checked', value);
    };

    const clickall = array => {
        $.each(array, (index, element) => {
            element.click();
        });
    };

    $selectallcheckboxes.on('click', e => {
        setall(e, true);
    });

    $deselectallcheckboxes.on('click', e => {
        setall(e, false);
    });

    $selectallall.on('click', () => {
        clickall($selectallcheckboxes);
    });

    $deselectallall.on('click', () => {
        clickall($deselectallcheckboxes);
    });

    const locations = location.href.split('?');
    let params = {};
    if (locations[1]) {
        params = locations[1].replace(',', '&').split('&').reduce((obj, curr) => {
            const paramsegments = curr.split('=');
            if (paramsegments.length < 2) {
                return obj;
            }
            obj[paramsegments[0]] = decodeURIComponent(paramsegments[1]);
            return obj;
        }, {});
        if (params.sort) {
            $sortselect.val(params.sort);
        }
    }

    $sortselect.on('change', () => {
        params.sort = $sortselect.val();
        window.location = location.href.split('?')[0] + `?${Object.keys(params).map(k => `${k}=${encodeURIComponent(params[k])}`)}`;
    });
};
