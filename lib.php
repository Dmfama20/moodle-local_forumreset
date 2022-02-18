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
 * Library of functions for local_forumreset.
 *
 * @package     local_forumreset
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function local_forumreset_extend_settings_navigation($settingsnav, $context) {
    global $CFG, $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('moodle/backup:backupcourse', context_course::instance($PAGE->course->id))) {
        return;
    }

    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $strfoo = 'Reset Forum';
        $url = new moodle_url('/local/forumreset/index.php', array('id' => $PAGE->course->id));
        $foonode = navigation_node::create(
            $strfoo,
            $url,
            navigation_node::NODETYPE_LEAF,
            'forumreset',
            'forumreset',
            new pix_icon('i/scheduled', 'Forumreset')
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $foonode->make_active();
        }
        $settingnode->add_node($foonode);
    }
}



/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @param int    $config     The block instance configuration
 * @param string $forceorder An override for the course order setting
 * @return array Activities with completion settings in the course
 */
function local_forumreset_get_forums($courseid) {
    global $DB;
    $records=$DB->get_records('forum',['course'=>$courseid]);
    $forums=array();
    foreach($records as $r)   {
        $forums[]=$r;   
    }
    return $forums;
}




/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function list_all_forums($courseID) {
    global $DB;
   //Standard values without submitting the form

   $forums = local_forumreset_get_forums($courseID);
   
   $table = new html_table();
   $table->head = array( 'Forum' , 'Typ', 'Reset Forum');
   // echo $OUTPUT->heading('Kursinformationen: '.get_course($courseID)->fullname  ,2);

  foreach($forums as $entry)  {

    $url = new moodle_url('/local/forumreset/reset_forum.php', array(
        'courseid'=> $courseID,
        'forumid' =>$entry->id
    ));
        $link = html_writer::link($url, 'reset');

       $table->data[] = array($entry->name,$entry->type, $link);
  }

  return $table;
}


/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function list_all_discussions($forumID, $courseid) {
    global $DB;
   //Standard values without submitting the form

   $discussions = $DB->get_records('forum_discussions',['forum'=>$forumID]);

    // throw new dml_exception(var_dump($discussions));

   
   $table = new html_table();
   $table->head = array( 'Titel' , 'Author','posts','show posts');
   // echo $OUTPUT->heading('Kursinformationen: '.get_course($courseID)->fullname  ,2);

  foreach($discussions as $entry)  {

    // $user=$DB->get_records('user',['id'=>$entry->userid]);
    $url = new moodle_url('/local/forumreset/reset_discussion.php', array(
        'courseid'=> $courseid,
        'forumid' =>$forumID,
        'discussionid'=>$entry->id
    ));

    $link = html_writer::link($url, 'posts');

    $user=$DB->get_record('user',['id'=>$entry->userid]);
    $countposts=$DB->count_records('forum_posts',['discussion'=>$entry->id]);

       $table->data[] = array($entry->name,$user->lastname.', '.$user->firstname,$countposts -1,$link);
  }


  return $table;
}

/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function list_all_posts($forumID, $courseid,$discussionid) {
    global $DB;
   //Standard values without submitting the form

   $posts = $DB->get_records('forum_posts',['discussion'=>$discussionid]);

    // throw new dml_exception(var_dump($discussions));

   
   $table = new html_table();
   $table->head = array( 'Thema' , 'Post','User');
   // echo $OUTPUT->heading('Kursinformationen: '.get_course($courseID)->fullname  ,2);

  foreach($posts as $entry)  {
      $user=$DB->get_record('user',['id'=>$entry->userid]);

       $table->data[] = array(clean_param($entry->subject,PARAM_TEXT),clean_param($entry->message,PARAM_TEXT),$user->lastname.', '.$user->firstname);
  }


  return $table;
}

/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function reset_all_discussions($forumID,$courseID,$data) {
    global $DB;
   $discussions=$DB->get_records('forum_discussions',['forum'=>$forumID,'course'=>$courseID]);
   
   foreach($discussions as $entry)  {
       $posts=$DB->get_records('forum_posts',['discussion'=>$entry->id]);
       if(in_array($entry->id, $data->selectdiscussions) )  {
            //Keep first level posts of this discussion
            foreach($posts as $postentry)    {
                if($entry->firstpost!=$postentry->id )    {
                    if($postentry->parent==$entry->firstpost && $entry->userid!=$postentry->userid)   {
                        $DB->delete_records('forum_posts',['id'=>$postentry->id]);
                    }
                    if($postentry->parent!=$entry->firstpost)   {
                        $DB->delete_records('forum_posts',['id'=>$postentry->id]);
                    }
                    
                    
                }
            }

       }
       else{
           //Delete first level posts, too.
        foreach($posts as $postentry)    {
            if($entry->firstpost!=$postentry->id)    {
                $DB->delete_records('forum_posts',['id'=>$postentry->id]);
            }
        }
       }
      

   }

  return ;
}



