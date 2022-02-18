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
 * Local Forumreset main view.
 *
 * @package     local_forumreset
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('lib.php');
// require_once('edit_form.php');

 require_login();

global $CFG, $DB, $PAGE;
$courseID = required_param('id', PARAM_INT);


$currentparams = ['id' => $courseID];
$url = new moodle_url('/local/forumreset/index.php', $currentparams);
$PAGE->set_url($url);





// Set page context.
$PAGE->set_context(context_system::instance());
// Set page layout.
$PAGE->set_pagelayout('standard');
// Set page layout.

$PAGE->set_title($SITE->fullname . ': ' . "Forum Reset");
$PAGE->set_heading($SITE->fullname);
// $PAGE->set_url(new moodle_url('/local/dexmod/index.php'));
$PAGE->navbar->ignore_active(true);
// $PAGE->navbar->add("Dexpmod", new moodle_url('/local/dexpmod/index.php'));
$PAGE->navbar->add("Forum Reset", new moodle_url($url));
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();

$table=list_all_forums($courseID);

if(count($table->data)==0)    {    
   echo $OUTPUT->heading('Dieser Kurs enthält keine Foren!',2);
}
else{
    echo html_writer::table($table);
}

$backurl=new moodle_url('/course/view.php', array('id' => $courseID ));
 echo $OUTPUT->single_button($backurl, 'Zurück zum Kurs');





echo $OUTPUT->footer();
