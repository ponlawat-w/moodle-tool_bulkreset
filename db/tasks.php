<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'tool_bulkreset\task\schedule_task',
        'blocking' => 0,
        'minute' => '*',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ]
];
