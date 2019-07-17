<?php

defined('MOODLE_INTERNAL') || die;

function tool_bulkreset_renderselectallbuttons() {
    $selectall = html_writer::link('javascript:void(0);', get_string('selectall', 'tool_bulkreset'), ['class' => 'tool-bulkreset-selectall']);
    $deselectall = html_writer::link('javascript:void(0);', get_string('deselectall', 'tool_bulkreset'), ['class' => 'tool-bulkreset-deselectall']);
    return html_writer::div(
        $selectall . ' / ' . $deselectall
    );
}

function tool_bulkreset_renderselectallallbuttons() {
    $selectallall = html_writer::link('javascript:void(0);', get_string('selectallall', 'tool_bulkreset'), ['id' => 'tool-bulkreset-selectallall', 'class' => 'btn btn-primary']);
    $deselectallall = html_writer::link('javascript:void(0);', get_string('deselectallall', 'tool_bulkreset'), ['id' => 'tool-bulkreset-deselectallall', 'class' => 'btn btn-default']);
    return html_writer::tag('p',
        $selectallall . ' ' . $deselectallall
    );
}
