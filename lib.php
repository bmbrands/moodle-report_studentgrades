<?php
// This file is part of the studentgrades grade report
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
 * report_studentgrades global functions
 *
 * @package    report_studentgrades 
 * @copyright  2017 Sonsbeekmedia, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Bas Brands
 */

require_once($CFG->dirroot . '/grade/report/lib.php');

defined('MOODLE_INTERNAL') || die();

function report_studentgrades_extend_navigation_course($navigation, $course, $context) {
    global $CFG, $OUTPUT;
    if (has_capability('report/studentgrades:view', $context)) {
        $url = new moodle_url('/report/studentgrades/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_studentgrades'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}