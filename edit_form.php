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
 * Local plugin "ForumReset" - edit_form.php
 * *
 * @package     local_forumreset
 * @copyright   2022 Alexander Dominicus, Bochum University of Applied Science <alexander.dominicus@hs-bochum.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class resetforum_form extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $PAGE, $DB;


        $mform = $this->_form; // Don't forget the underscore! 


        // $mform->addElement('text', 'addtime', 'Tage drauf rechnen', $attributes);
        $mform->setType('addtime', PARAM_INT);
        foreach ($PAGE->url->params() as $name => $value) {
            $mform->addElement('hidden', $name, $value);
            $mform->setType($name, PARAM_RAW);
        }
        $mform->addElement('advcheckbox', 'keepfirstlevelpost', 'Keep first level posts');
        $infotext = 'In der Tabelle unten finden Sie alle Diskussionen des Forums. Wählen Sie <i>keep first level posts</i> 
        (=Antworten vom Author auf seinen Eröffnungsbeitrag) um die Diskussionen auszuwählen bei denen diese erhalten bleiben sollen. Wichtig: 
        Die entsprechenden Themen müssen in der Auswahliste (s.u.) markiert werden!';
        $mform->addElement('static', 'infotext', '', $infotext);
        $mform->hideif('infotext', 'keepfirstlevelpost', 'eq', '0');
        $mform->addElement('advcheckbox', 'deleteallcontent', 'Delete all content');

        $discussions = $DB->get_records('forum_discussions', ['forum' => $this->_customdata['forumid']]);
        $discussionsarray = array();
        foreach ($discussions as $entry) {
            $discussionsarray[$entry->id] = $entry->name;
        }
        $mform->addElement('select', 'selectdiscussions', 'first level post erhalten', $discussionsarray);
        $mform->getElement('selectdiscussions')->setMultiple(true);
        $mform->getElement('selectdiscussions')->setSize(count($discussionsarray));
        $mform->setAdvanced('selectdiscussions', true);
        $mform->hideif('selectdiscussions', 'keepfirstlevelpost', 'eq', '0');


        $this->add_action_buttons($cancel = false, $submitlabel = 'Reset Forum!');
    }
    // //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }
}

class resetdiscussion_form extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;
        global $PAGE;


        $mform = $this->_form; // Don't forget the underscore! 


        // $mform->addElement('text', 'addtime', 'Tage drauf rechnen', $attributes);
        $mform->setType('addtime', PARAM_INT);
        foreach ($PAGE->url->params() as $name => $value) {
            $mform->addElement('hidden', $name, $value);
            $mform->setType($name, PARAM_RAW);
        }


        $this->add_action_buttons($cancel = false, $submitlabel = 'Zurück!');
    }
    // //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }
}
