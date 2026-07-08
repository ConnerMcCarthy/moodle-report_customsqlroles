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
 * Script for editing a custom SQL report.
 *
 * @package report_customsqlroles
 * @copyright 2009 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/edit_form.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', 0, PARAM_INT);
$urlparams = [];
if ($id) {
    $urlparams['id'] = $id;
}

admin_externalpage_setup('report_customsqlroles', '', $urlparams, '/report/customsqlroles/edit.php');
$context = context_system::instance();
require_capability('report/customsqlroles:definequeries', $context);

$relativeurl = 'edit.php';
$report = null;
$reportquerysql = '';

// Are we editing an existing report, or creating a new one.
if ($id) {
    $report = $DB->get_record('report_customsqlroles_queries', array('id' => $id));
    if (!$report) {
        print_error('invalidreportid', 'report_customsqlroles', report_customsqlroles_url('index.php'), $id);
    }
    $reportquerysql = $report->querysql;
    $report->allowedroleids = report_customsqlroles_parse_allowed_role_ids(
            empty($report->allowedroleids) ? '' : $report->allowedroleids);
    $queryparams = !empty($report->queryparams) ? unserialize($report->queryparams) : array();
    foreach ($queryparams as $param => $value) {
        $report->{'queryparam'.$param} = $value;
    }
    $relativeurl .= '?id=' . $id;
}

$querysql = optional_param('querysql', $reportquerysql, PARAM_RAW);
$queryparams = report_customsqlroles_get_query_placeholders_and_field_names($querysql);

$mform = new report_customsqlroles_edit_form(report_customsqlroles_url($relativeurl), $queryparams);

if ($mform->is_cancelled()) {
    redirect(report_customsqlroles_url('index.php'));
}

if ($newreport = $mform->get_data()) {
    $newreport->descriptionformat = $newreport->description['format'];
    $newreport->description = $newreport->description['text'];

    // Currently, autocomplete can return an empty value in the array. If we get one, strip it out.
    $newreport->emailto = trim(implode(',', $newreport->emailto), ',');
    $allowedroleids = empty($newreport->allowedroleids) ? array() : $newreport->allowedroleids;
    if (!is_array($allowedroleids)) {
        $allowedroleids = array($allowedroleids);
    }
    $allowedroleids = array_map('intval', $allowedroleids);
    $allowedroleids = array_values(array_filter(array_unique($allowedroleids)));
    $newreport->allowedroleids = implode(',', $allowedroleids);

    // Set the following fields to empty strings if the report is running manually.
    if ($newreport->runable === 'manual') {
        $newreport->at = '';
        $newreport->emailto = '';
        $newreport->emailwhat = '';
        $newreport->customdir = '';
    }
    if ($newreport->runable == 'manual' || empty($newreport->singlerow)) {
        $newreport->singlerow = 0;
    }

    // Pick up named parameters into serialised array.
    if ($queryparams) {
        foreach ($queryparams as $queryparam => $formparam) {
            $queryparams[$queryparam] = $newreport->{$formparam};
            unset($newreport->{$formparam});
        }
        $newreport->queryparams = serialize($queryparams);
    } else {
        $newreport->queryparams = '';
    }

    $newreport->usermodified = $USER->id;
    $newreport->timemodified = \report_customsqlroles\utils::time();
    if ($id) {
        $newreport->id = $id;
        if (empty($report->timemodified)) {
            $newreport->timecreated = $newreport->timemodified;
        }
        $ok = $DB->update_record('report_customsqlroles_queries', $newreport);
        if (!$ok) {
            print_error('errorupdatingreport', 'report_customsqlroles',
                        report_customsqlroles_url('edit.php?id=' . $id));
        }

    } else {
        $newreport->timecreated = $newreport->timemodified;
        $id = $DB->insert_record('report_customsqlroles_queries', $newreport);
        if (!$id) {
            print_error('errorinsertingreport', 'report_customsqlroles',
                        report_customsqlroles_url('edit.php'));
        }
    }

    report_customsqlroles_log_edit($id);
    if ($newreport->runable == 'manual') {
        redirect(report_customsqlroles_url('view.php?id=' . $id));
    } else {
        redirect(report_customsqlroles_url('index.php'));
    }
}

admin_externalpage_setup('report_customsqlroles');
echo $OUTPUT->header().
     $OUTPUT->heading(get_string('editingareport', 'report_customsqlroles'));

if ($report) {
    $report->description = array('text' => $report->description, 'format' => $report->descriptionformat);
    $mform->set_data($report);
}

$mform->display();

echo $OUTPUT->footer();
