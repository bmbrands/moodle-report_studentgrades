<?php
// This file is part of the quizcijfers grade report
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
 * Definition of the grade_report_quizcijfers class
 *
 * @package    report_quizcijfers 
 * @copyright  2017 Sonsbeekmedia, bas@sonsbeekmedia.nl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Bas Brands
 */

require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');

/**
 * Class providing an API for the user report building and displaying.
 * @uses grade_report
 * @package report_user
 */

class grade_report_quizcijfers {

    public $canviewhidden;
    private $user;
    private $users;
    private $coursegrades;
    private $courses;

    /**
     * Constructor. Sets local copies of user preferences and initialises grade_tree.
     * @param int $courseid
     * @param object $gpr grade plugin return tracking object
     * @param string $context
     * @param int $userid The id of the user
     */
    public function __construct($courseid, $context) {
        global $DB, $CFG, $USER;
        $this->courseid = $courseid;
        $this->course = $DB->get_record('course', array('id' => $courseid));

        $this->canviewhidden = has_capability('moodle/grade:viewhidden', $context);
        $this->sort = optional_param('dir', 'ASC', PARAM_ALPHA);
        $this->user = $USER;
        $this->users = array();
        $this->mycourses();
        $this->coursegrades = array();
        $this->fill_table();
        usort($this->users, array($this, "cmp"));
        $this->baseurl = new moodle_url('/report/quizcijfers/index.php');
    }

    function mycourses() {
        global $DB;
        $this->courses = array();
        $categories = $DB->get_records('course_categories', array('visible' => 1));
        $enrolledcourses = enrol_get_all_users_courses($this->user->id);
        if (is_siteadmin()) {
            $this->courses = $DB->get_records('course', array('category' => $this->course->category, 'visible' => 1));
        } else {
            foreach ($enrolledcourses as $course) {
                if (!$course->visible) {
                    continue;
                }
                if ($course->category != $this->course->category) {
                    continue;
                }
                $coursecontext = context_course::instance($course->id);
                if (!has_capability('report/quizcijfers:viewall', $coursecontext)) {
                    continue;
                }
                $this->courses[] = $course;
            }
        }
    }


    function fill_table() {
        global $DB, $PAGE, $CFG;

        foreach ($this->courses as $course) {
            $grades = $this->get_course_grades($course);
        }

    }


    /**
     * Prints or returns the HTML from the flexitable.
     * @param bool $return Whether or not to return the data instead of printing it directly.
     * @return string
     */
    public function print_table() {
        global $OUTPUT, $CFG;
        $content = '';
        $data = new stdClass();

        // User Column
        $th = new stdClass();
        $th->name = get_string('users');
        $th->key = '';
        $th->nosort = true;

        $params= array('dir' => 'ASC');
        if ($this->sort == 'ASC') {
            $params['dir'] = 'DESC';
            $th->sortdesc = new moodle_url($this->baseurl, $params);
            $th->iconsort = $OUTPUT->pix_icon('t/sort_asc', 'Sort ASC');
        } else {
            $th->sortdesc = new moodle_url($this->baseurl, $params);
            $th->iconsort = $OUTPUT->pix_icon('t/sort_desc', 'Sort DESC');
        }

        $data->heading[] = $th;

        if ($CFG->enablebadges) {
            $th = new stdClass();
            $th->name = get_string('badges');
            $th->key = '';
            $th->nosort = true;
            $data->heading[] = $th;
        }
        
        foreach ($this->courses as $course) {
            $th = new stdClass();
            $th->name = $course->fullname;
            $th->key = '';
            $th->nosort = true;
            $data->heading[] = $th;
        }


        foreach ($this->users as $user) {
            $myuser = new stdClass();
            $value = new stdClass();
            $value->value = fullname($user);
            $myuser->values[] = $value;

            if ($CFG->enablebadges) {
                $value = new stdClass();
                $value->value = $this->userbadges($user);
                $myuser->values[] = $value;
            }
            foreach ($this->courses as $course) {
                $value = new stdClass();
                $params = array('id' => $user->id, 'course' => $course->id, 'mode' => 'complete');
                $value->link = new moodle_url('/report/outline/user.php', $params);
                if (isset($this->coursegrades[$course->id][$user->id])) {
                    $value->value = round($this->coursegrades[$course->id][$user->id]->grade);
                } else {
                    $value->link = false;
                    $value->value = '-';
                }
                $myuser->values[] = $value;
            }
            $data->myusers[] = $myuser;
        }
        return $OUTPUT->render_from_template('report_quizcijfers/table', $data);
    }

    /**
     * Processes the data sent by the form (grades and feedbacks).
     * @var array $data
     * @return bool Success or Failure (array of errors).
     */
    function process_data($data) {
    }

    function process_action($target, $action) {
    }

    public function get_course_grades($course) {
        global $DB;
        $enrolledusers = get_enrolled_users(context_course::instance($course->id));
        
        $grades = grade_get_course_grades($course->id, array_keys($enrolledusers));
        foreach ($enrolledusers as $euser) {
            if (!array_key_exists($euser->id, $this->users)) {
                $euser->fullname = fullname($euser);
                $this->users[$euser->id] = $euser;
            }
        }
        $this->coursegrades[$course->id] = $grades->grades;
    }

    function cmp($a, $b) {
        if ($this->sort == 'DESC') {
            return strcmp($b->fullname, $a->fullname);
        } else {
            return strcmp($a->fullname, $b->fullname);
        }
    }

    private function userbadges($user) {
        global $CFG, $OUTPUT;
        require_once($CFG->libdir . "/badgeslib.php");
        $badges = badges_get_user_badges($user->id);
        $template = new stdClass();
        $template->badges = array();
        foreach ($badges as $badge) {
             $context = ($badge->type == BADGE_TYPE_SITE) ? context_system::instance() : context_course::instance($badge->courseid);
            $badge->imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', 'f1', false);
            $badge->image = $badge->imageurl->out();
            $badge->url = new moodle_url('/badges/badge.php', array('hash' => $badge->uniquehash));
            $template->badges[] = $badge;
        }
        return $OUTPUT->render_from_template('report_quizcijfers/badges', $template);

    }
}
