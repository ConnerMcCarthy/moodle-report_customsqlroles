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
 * Custom SQL report categories.
 *
 * Users with the report/customsqlroles:managequeries capability can create and edit
 * the custom categories.
 *
 * This page shows the list of categories, with edit icons, and an add new button
 * if you have the report/customsqlroles:managequeries capability.
 *
 * @package report_customsqlroles
 * @copyright 2013 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir . '/adminlib.php');

admin_externalpage_setup('report_customsqlroles', '', null, '/report/customsqlroles/manage.php');
$context = context_system::instance();
require_capability('report/customsqlroles:managecategories', $context);

echo $OUTPUT->header() . $OUTPUT->heading(get_string('managecategories', 'report_customsqlroles'));

$categories = $DB->get_records('report_customsqlroles_categories', null, 'name ASC');

echo html_writer::tag('p', get_string('addcategorydesc', 'report_customsqlroles'));

if (!empty($categories)) {
    foreach ($categories as $category) {
        echo html_writer::start_tag('div');

        echo ' ' . html_writer::tag('span', format_string($category->name) . ' ', array('class' => 'report_customsqlroles')) .
        html_writer::tag('a', $OUTPUT->pix_icon('t/edit', get_string('edit')),
                array('title' => get_string('editcategoryx', 'report_customsqlroles', format_string($category->name)),
                        'href' => report_customsqlroles_url('addcategory.php?id=' . $category->id)));

        if ($category->id != 1 && !$DB->record_exists('report_customsqlroles_queries', ['categoryid' => $category->id])) {
            echo ' ' .  html_writer::tag('a', $OUTPUT->pix_icon('t/delete', get_string('delete')),
                    array('title' => get_string('deletecategoryx', 'report_customsqlroles', format_string($category->name)),
                            'href' => report_customsqlroles_url('categorydelete.php?id=' . $category->id)));
        }

        echo html_writer::end_tag('div');
    }
}

echo $OUTPUT->single_button(report_customsqlroles_url('addcategory.php'),
        get_string('addcategory', 'report_customsqlroles'));

echo $OUTPUT->footer();
