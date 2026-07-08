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
 * Form for editing a custom SQL report.
 *
 * @package report_customsqlroles
 * @copyright 2009 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');
require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Form for editing a custom SQL report.
 *
 * @copyright © 2009 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_customsqlroles_edit_form extends moodleform {
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $categoryoptions = report_customsqlroles_category_options();
        $mform->addElement('select', 'categoryid', get_string('category', 'report_customsqlroles'),
                $categoryoptions);
        $catdefault = isset($categoryoptions[1]) ? 1 : key($categoryoptions);
        $mform->setDefault('categoryid', $catdefault);

        $mform->addElement('text', 'displayname', get_string('displayname', 'report_customsqlroles'));
        $mform->addRule('displayname', get_string('displaynamerequired', 'report_customsqlroles'),
                        'required', null, 'client');
        $mform->setType('displayname', PARAM_TEXT);

        $mform->addElement('editor', 'description',
                get_string('description', 'report_customsqlroles'));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('textarea', 'querysql', get_string('querysql', 'report_customsqlroles'),
                ['rows' => '25', 'cols' => '80']);
        $mform->addRule('querysql', get_string('querysqlrequried', 'report_customsqlroles'),
                'required', null, 'client');
        $mform->setType('querysql', PARAM_RAW);

        $mform->addElement('submit', 'verify', get_string('verifyqueryandupdate', 'report_customsqlroles'));
        $mform->registerNoSubmitButton('verify');

        $hasparameters = 0;
        if (count($this->_customdata)) {
            $mform->addElement('static', 'params', '', get_string('queryparams', 'report_customsqlroles'));
            foreach ($this->_customdata as $queryparam => $formparam) {
                $type = report_customsqlroles_get_element_type($queryparam);
                $mform->addElement($type, $formparam, $queryparam);
                if ($type == 'text') {
                    $mform->setType($formparam, PARAM_RAW);
                }
                $hasparameters++;
            }
            $mform->addElement('static', 'spacer', '', '');
        }

        $mform->addElement('static', 'note', get_string('note', 'report_customsqlroles'),
                           get_string('querynote', 'report_customsqlroles', $CFG->wwwroot));

        $capabilityoptions = report_customsqlroles_capability_options();
        $mform->addElement('select', 'capability', get_string('whocanaccess', 'report_customsqlroles'),
                           $capabilityoptions);
        end($capabilityoptions);
        $mform->setDefault('capability', key($capabilityoptions));

        $mform->addElement('autocomplete', 'allowedroleids', get_string('allowedroles', 'report_customsqlroles'),
                report_customsqlroles_role_options(), array('multiple' => true));
        $mform->addHelpButton('allowedroleids', 'allowedroles', 'report_customsqlroles');

        $mform->addElement('text', 'querylimit', get_string('querylimit', 'report_customsqlroles'));
        $mform->setType('querylimit', PARAM_INT);
        $mform->setDefault('querylimit', get_config('report_customsqlroles', 'querylimitdefault'));
        $mform->addRule('querylimit', get_string('requireint', 'report_customsqlroles'),
                        'numeric', null, 'client');

        $runat = array();
        if ($hasparameters) {
            $runat[] = $mform->createElement('select', 'runable', null,  report_customsqlroles_runable_options('manual'));
        } else {
            $runat[] = $mform->createElement('select', 'runable', null,  report_customsqlroles_runable_options());
        }
        $runat[] = $mform->createElement('select', 'at', null, report_customsqlroles_daily_at_options());
        $mform->addGroup($runat, 'runablegroup', get_string('runable', 'report_customsqlroles'),
                get_string('at', 'report_customsqlroles'), false);

        $mform->addElement('checkbox', 'singlerow', get_string('typeofresult', 'report_customsqlroles'),
                           get_string('onerow', 'report_customsqlroles'));

        $mform->addElement('text', 'customdir', get_string('customdir', 'report_customsqlroles'), 'size = 70');
        $mform->setType('customdir', PARAM_PATH);
        $mform->disabledIf('customdir', 'runable', 'eq', 'manual');
        $mform->addHelpButton('customdir', 'customdir', 'report_customsqlroles');

        $options = [
            'ajax' => 'report_customsqlroles/userselector', // Bit of a hack, but the service seems to do what we want.
            'multiple' => true,
            'valuehtmlcallback' => function($userid) {
                global $DB, $OUTPUT;

                $user = $DB->get_record('user', ['id' => (int) $userid], '*', IGNORE_MISSING);
                if (!$user) {
                    return false;
                }

                if (class_exists('\core_user\fields')) {
                    $extrafields = \core_user\fields::for_identity(\context_system::instance(),
                            false)->get_required_fields();
                } else {
                    $extrafields = get_extra_user_fields(context_system::instance());
                }

                return $OUTPUT->render_from_template(
                        'report_customsqlroles/form-user-selector-suggestion',
                        \report_customsqlroles\external\get_users::prepare_result_object(
                                $user, $extrafields)
                        );
            }
        ];
        $mform->addElement('autocomplete', 'emailto', get_string('emailto', 'report_customsqlroles'), [], $options);
        $mform->setType('emailto', PARAM_RAW);

        $mform->addElement('select', 'emailwhat', get_string('emailwhat', 'report_customsqlroles'),
                report_customsqlroles_email_options());

        $mform->disabledIf('singlerow', 'runable', 'eq', 'manual');
        $mform->disabledIf('at', 'runable', 'ne', 'daily');
        $mform->disabledIf('emailto', 'runable', 'eq', 'manual');
        $mform->disabledIf('emailwhat', 'runable', 'eq', 'manual');

        $this->add_action_buttons();
    }

    public function set_data($currentvalues) {
        global $DB, $OUTPUT;

        $currentvalues->emailto = explode(',', $currentvalues->emailto);
        $currentvalues->allowedroleids = report_customsqlroles_parse_allowed_role_ids(
                empty($currentvalues->allowedroleids) ? '' : $currentvalues->allowedroleids);
        parent::set_data($currentvalues);

        // Add report information.
        $mform = $this->_form;
        $reportinfocontext = new stdClass();
        $reportinfocontext->timecreated = $currentvalues->timecreated > 0 ? userdate($currentvalues->timecreated) : '';
        $reportinfocontext->timemodified = $currentvalues->timemodified > 0 ? userdate($currentvalues->timemodified) : '';
        $reportinfocontext->usermodified = '';
        if ($currentvalues->usermodified > 0) {
            $usermodified = $DB->get_record('user', ['id' => $currentvalues->usermodified]);
            $reportinfocontext->usermodified = html_writer::link(
                $url = new moodle_url('/user/profile.php', ['id' => $usermodified->id]),
                fullname($usermodified)
            );
        }
        $reportinfo = $OUTPUT->render_from_template(
            'report_customsqlroles/form_report_information',
            $reportinfocontext
        );
        $mform->addElement('html', $reportinfo);
    }

    public function validation($data, $files) {
        global $CFG, $DB, $USER;

        $errors = parent::validation($data, $files);

        $sql = $data['querysql'];
        if (report_customsqlroles_contains_bad_word($sql)) {
            // Obviously evil stuff in the SQL.
            $errors['querysql'] = get_string('notallowedwords', 'report_customsqlroles',
                    implode(', ', report_customsqlroles_bad_words_list()));

        } else if (strpos($sql, ';') !== false) {
            // Do not allow any semicolons.
            $errors['querysql'] = get_string('nosemicolon', 'report_customsqlroles');

        } else if ($CFG->prefix != '' && preg_match('/\b' . $CFG->prefix . '\w+/i', $sql)) {
            // Make sure prefix is prefix_, not explicit.
            $errors['querysql'] = get_string('noexplicitprefix', 'report_customsqlroles', $CFG->prefix);

        } else if (!array_key_exists('runable', $data)) {
            // This happens when the user enters a query including placehoders, and
            // selectes Run: Scheduled, and then tries to save the form.
            $errors['runablegroup'] = get_string('noscheduleifplaceholders', 'report_customsqlroles');

        } else {
            // Now try running the SQL, and ensure it runs without errors.
            $report = new stdClass;
            $report->querysql = $sql;
            $report->runable = $data['runable'];
            if ($report->runable === 'daily') {
                $report->at = $data['at'];
            }
            $sql = report_customsqlroles_prepare_sql($report, time());

            // Check for required query parameters if there are any.
            $paramvalues = [];
            foreach (report_customsqlroles_get_query_placeholders_and_field_names($sql) as $queryparam => $formparam) {
                if (!isset($data[$formparam])) {
                    $errors['params'] = get_string('queryparamschanged', 'report_customsqlroles');
                    break;
                }
                $paramvalues[$queryparam] = $data[$formparam];
            }

            if (!isset($errors['params'])) {
                try {
                    $rs = report_customsqlroles_execute_query($sql, $paramvalues, 2);

                    if (!empty($data['singlerow'])) {
                        // Count rows for Moodle 2 as all Moodle 1.9 useful and more performant
                        // recordset methods removed.
                        $rows = 0;
                        foreach ($rs as $value) {
                            $rows++;
                        }
                        if (!$rows) {
                            $errors['querysql'] = get_string('norowsreturned', 'report_customsqlroles');
                        } else if ($rows >= 2) {
                            $errors['querysql'] = get_string('morethanonerowreturned',
                                                             'report_customsqlroles');
                        }
                    }
                    // Check the list of users in emailto field.
                    if ($data['runable'] !== 'manual') {
                        if ($invaliduser = report_customsqlroles_validate_users($data['emailto'], $data['capability'])) {
                            $errors['emailto'] = $invaliduser;
                        }
                    }
                    $rs->close();
                } catch (dml_exception $e) {
                    $errors['querysql'] = get_string('queryfailed', 'report_customsqlroles',
                    $e->getMessage() . ' ' . $e->debuginfo);
                } catch (Exception $e) {
                    $errors['querysql'] = get_string('queryfailed', 'report_customsqlroles',
                                                     $e->getMessage());
                }
            }
        }

        // Check querylimit is in range.
        $maxlimit = get_config('report_customsqlroles', 'querylimitmaximum');
        if (empty($data['querylimit']) || $data['querylimit'] > $maxlimit) {
            $errors['querylimit'] = get_string('querylimitrange', 'report_customsqlroles', $maxlimit);
        }

        if (!empty($data['customdir'])) {
            $path = $data['customdir'];

            // The path either needs to be a writable directory ...
            if (is_dir($path) ) {
                if (!is_writable($path)) {
                    $errors['customdir'] = get_string('customdirnotwritable', 'report_customsqlroles', $path);
                }

            } else if (substr($path, -1) == DIRECTORY_SEPARATOR) {
                // ... and it must exist...
                $errors['customdir'] = get_string('customdirmustexist', 'report_customsqlroles', $path);

            } else {

                // ... or be a path to a writable file, or a new file in a writable directory.
                $dir = dirname($path);

                if (!is_dir($dir)) {
                    $errors['customdir'] = get_string('customdirnotadirectory', 'report_customsqlroles', $dir);
                } else {

                    if (file_exists($path)) {
                        if (!is_writable($path)) {
                            $errors['customdir'] = get_string('filenotwritable', 'report_customsqlroles', $path);
                        }
                    } else {
                        if (!is_writable($dir)) {
                            $errors['customdir'] = get_string('customdirmustexist', 'report_customsqlroles', $dir);
                        }
                    }
                }
            }
        }

        return $errors;
    }
}
