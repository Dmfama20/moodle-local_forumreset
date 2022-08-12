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
 * Local Differentiator main view.
 *
 * @package     local_dexmod
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('lib.php');
require_once('edit_form.php');

require_login();

global $CFG, $DB, $PAGE;
$courseID = required_param('courseid', PARAM_INT);
$forumID = required_param('forumid', PARAM_INT);
$discussionID = required_param('discussionid', PARAM_INT);


$currentparams = ['courseid' => $courseID, 'forumid' => $forumID, 'discussionid' => $discussionID];
$url = new moodle_url('/local/forumreset/reset_discussion.php', $currentparams);
$PAGE->set_url($url);





// Set page context.
$PAGE->set_context(context_system::instance());
// Set page layout.
$PAGE->set_pagelayout('standard');
// Set page layout.

$PAGE->set_title($SITE->fullname . ': ' . "Show Discussions");
$PAGE->set_heading($SITE->fullname);
// $PAGE->set_url(new moodle_url('/local/dexmod/index.php'));
$PAGE->navbar->ignore_active(true);
// $PAGE->navbar->add("Dexpmod", new moodle_url('/local/dexpmod/index.php'));
$PAGE->navbar->add("Show Discussions", new moodle_url($url));
$PAGE->set_pagelayout('admin');

$mform = new resetdiscussion_form(null, array('courseid' => $courseID, 'forumid' => $forumID, 'discussionid' => $discussionID));


if ($data = $mform->get_data()) {
    $url_back = new moodle_url(
        '/local/forumreset/reset_forum.php',
        array('courseid' => $courseID, 'forumid' => $forumID,)
    );
    redirect($url_back);
} else {
}

echo $OUTPUT->header();
$discussion = $DB->get_record('forum_discussions', ['id' => $discussionID]);
echo $OUTPUT->heading('All posts of ' . $discussion->name . ':', 5);
$table = list_all_posts($forumID, $courseID, $discussionID);
echo html_writer::table($table);
//display the form
$mform->display();




echo $OUTPUT->footer();
