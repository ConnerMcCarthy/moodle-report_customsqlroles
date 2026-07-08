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
 * Admin settings tree setup for the Custom SQL admin report.
 *
 * @package report_customsqlroles
 * @copyright 2011 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Start of week, used for the day to run weekly reports.
    $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $days = array_map(function($day) {
        return get_string($day, 'calendar');
    }, $days);

    $default = \core_calendar\type_factory::get_calendar_instance()->get_starting_weekday();

    // Setting this option to -1 will use the value from the site calendar.
    $options = [-1 => get_string('startofweek_default', 'report_customsqlroles', $days[$default])] + $days;
    $settings->add(new admin_setting_configselect('report_customsqlroles/startwday',
            get_string('startofweek', 'report_customsqlroles'),
            get_string('startofweek_desc', 'report_customsqlroles'), -1, $options));

    $settings->add(new admin_setting_configtext_with_maxlength('report_customsqlroles/querylimitdefault',
            get_string('querylimitdefault', 'report_customsqlroles'),
            get_string('querylimitdefault_desc', 'report_customsqlroles'), 5000, PARAM_INT, null, 10));

    $settings->add(new admin_setting_configtext_with_maxlength('report_customsqlroles/querylimitmaximum',
            get_string('querylimitmaximum', 'report_customsqlroles'),
            get_string('querylimitmaximum_desc', 'report_customsqlroles'), 5000, PARAM_INT, null, 10));
}

$ADMIN->add('reports', new admin_externalpage('report_customsqlroles',
        get_string('pluginname', 'report_customsqlroles'),
        new moodle_url('/report/customsqlroles/index.php'),
        'report/customsqlroles:view'));
