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
 * Library of functions for local_differentiator.
 *
 * @package     local_dexpmod
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class resetforum_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        global $PAGE;

       
        $mform = $this->_form; // Don't forget the underscore! 

       
        // $mform->addElement('text', 'addtime', 'Tage drauf rechnen', $attributes);
        $mform->setType('addtime', PARAM_INT);
        foreach ($PAGE->url->params() as $name => $value) {
            $mform->addElement('hidden', $name, $value);
            $mform->setType($name, PARAM_RAW);
        }
  

        $this->add_action_buttons($cancel = true, $submitlabel='Reset all discussions!');
    
    }
    // //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }
}

class resetdiscussion_form extends moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;
        global $PAGE;

       
        $mform = $this->_form; // Don't forget the underscore! 

       
        // $mform->addElement('text', 'addtime', 'Tage drauf rechnen', $attributes);
        $mform->setType('addtime', PARAM_INT);
        foreach ($PAGE->url->params() as $name => $value) {
            $mform->addElement('hidden', $name, $value);
            $mform->setType($name, PARAM_RAW);
        }
  

        $this->add_action_buttons($cancel = true, $submitlabel='Reset discussion!');
    
    }
    // //Custom validation should be added here
    // function validation($data, $files) {
    //     return array();
    // }
}


