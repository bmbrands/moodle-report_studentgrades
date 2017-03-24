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
 * The report studentgrades user report default page
 *
 * @package    report_studentgrades 
 * @copyright  2017 Sonsbeekmedia, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Bas Brands
 */

require_once '../../config.php';
require_once $CFG->dirroot.'/report/studentgrades/lib.php';
require_once $CFG->dirroot.'/report/studentgrades/studentgrades_report_class.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/querylib.php';

$id = required_param('id',PARAM_INT);       // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($id);
require_login($course);

$PAGE->set_context($context);
$PAGE->set_url('/report/studentgrades/studentgrades_report.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout('report');
$PAGE->set_title(get_string('pluginname', 'report_studentgrades'));
$PAGE->navbar->add(get_string('pluginname', 'report_studentgrades'));

$report = new grade_report_studentgrades($id, $context); 

echo $OUTPUT->header();

echo $report->print_table();

echo $OUTPUT->footer();
