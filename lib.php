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

function local_forumreset_extend_settings_navigation($settingsnav, $context)
{
    global $CFG, $PAGE, $DB;
    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }
    //Check is Forums available
    if ($DB->count_records('forum', ['course' => $PAGE->course->id]) == 0) {
        return;
    }

    // Only let users with the appropriate capability see this settings item.
    if (!has_capability('local/forumreset:resetforum', context_course::instance($PAGE->course->id))) {
        return;
    }
    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $name = 'Forum Reset';
        $url = new moodle_url('/local/forumreset/index.php', array('id' => $PAGE->course->id));
        $node = navigation_node::create(
            $name,
            $url,
            navigation_node::NODETYPE_LEAF,
            'forumreset',
            'forumreset',
            new pix_icon('t/left', 'Forumreset')
        );
        if ($PAGE->url->compare($url, URL_MATCH_BASE)) {
            $node->make_active();
        }
        $settingnode->add_node($node);
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
function local_forumreset_get_forums($courseid)
{
    global $DB;
    $records = $DB->get_records('forum', ['course' => $courseid]);
    $forums = array();
    foreach ($records as $r) {
        $forums[] = $r;
    }
    return $forums;
}




/**
 * Returns ALL activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @param int    $config     The block instance configuration
 * @param string $forceorder An override for the course order setting
 * @return array Activities with completion settings in the course
 */
function local_forumreset_get_activities($courseid, $config = null, $forceorder = null)
{
    $modinfo = get_fast_modinfo($courseid, -1);
    $sections = $modinfo->get_sections();
    $activities = array();
    foreach ($modinfo->instances as $module => $instances) {
        // List only forums!
        if ($module != "forum") {
            continue;
        }
        $modulename = get_string('pluginname', $module);
        foreach ($instances as $index => $cm) {
            if (
                $cm->completion != COMPLETION_TRACKING_NONE && ($config == null || (!isset($config->activitiesincluded) || ($config->activitiesincluded != 'selectedactivities' ||
                    !empty($config->selectactivities) &&
                    in_array($module . '-' . $cm->instance, $config->selectactivities))))
            ) {
                $activities[] = array(
                    'type'       => $module,
                    'modulename' => $modulename,
                    'id'         => $cm->id,
                    'instance'   => $cm->instance,
                    'name'       => format_string($cm->name),
                    'expected'   => $cm->completionexpected,
                    'section'    => $cm->sectionnum,
                    'position'   => array_search($cm->id, $sections[$cm->sectionnum]),
                    'url'        => method_exists($cm->url, 'out') ? $cm->url->out() : '',
                    'context'    => $cm->context,
                    'icon'       => $cm->get_icon_url(),
                    'available'  => $cm->available,
                    'visible'  => $cm->visible,
                );
            }
        }
    }

    // // Sort by first value in each element, which is time due.
    // if ($forceorder == 'orderbycourse' || ($config && $config->orderby == 'orderbycourse')) {
    //     usort($activities, 'block_completion_progress_compare_events');
    // } else {
    //     usort($activities, 'block_completion_progress_compare_times');
    // }

    return $activities;
}

/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function list_all_forums($courseID)
{
    global $DB;
    //Standard values without submitting the form
    $forums = local_forumreset_get_activities($courseID);
    $table = new html_table();
    $table->head = array('Forum', 'Typ', 'Reset Forum');
    // echo $OUTPUT->heading('Kursinformationen: '.get_course($courseID)->fullname  ,2);

    foreach ($forums as $entry) {
        // // List only visible forums!
        if ($entry['visible'] == "0") {
            continue;
        }
        $forumdetails = $DB->get_record('forum', ['id' => $entry['instance'], 'course' => $courseID]);
        $url = new moodle_url('/local/forumreset/reset_forum.php', array(
            'courseid' => $courseID,
            'forumid' => $forumdetails->id
        ));
        if ($DB->record_exists('forum_discussions', ['forum' => $forumdetails->id])) {
            $link = html_writer::link($url, 'reset');
        } else {
            $link = 'Diese Forum enthält keine Fragen!';
        }
        $table->data[] = array($forumdetails->name, $forumdetails->type, $link);
    }
    return $table;
}


/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function list_all_discussions($forumID, $courseid)
{
    global $DB;
    //Standard values without submitting the form
    $discussions = $DB->get_records('forum_discussions', ['forum' => $forumID]);
    // throw new dml_exception(var_dump($discussions));
    $table = new html_table();
    $table->head = array('Titel', 'Author', 'posts', 'show posts');
    // echo $OUTPUT->heading('Kursinformationen: '.get_course($courseID)->fullname  ,2);
    foreach ($discussions as $entry) {

        // $user=$DB->get_records('user',['id'=>$entry->userid]);
        $url = new moodle_url('/local/forumreset/reset_discussion.php', array(
            'courseid' => $courseid,
            'forumid' => $forumID,
            'discussionid' => $entry->id
        ));

        $link = html_writer::link($url, 'posts');

        $user = $DB->get_record('user', ['id' => $entry->userid]);
        $countposts = $DB->count_records('forum_posts', ['discussion' => $entry->id]);
        $table->data[] = array($entry->name, $user->lastname . ', ' . $user->firstname, $countposts - 1, $link);
    }
    return $table;
}

/**
 * Returns the activities with completion set in current course
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function list_all_posts($forumID, $courseid, $discussionid)
{
    global $DB;
    //Standard values without submitting the form
    $posts = $DB->get_records('forum_posts', ['discussion' => $discussionid]);
    // throw new dml_exception(var_dump($discussions));
    $table = new html_table();
    $table->head = array('Thema', 'Post', 'User');
    // echo $OUTPUT->heading('Kursinformationen: '.get_course($courseID)->fullname  ,2);

    foreach ($posts as $entry) {
        $user = $DB->get_record('user', ['id' => $entry->userid]);

        $table->data[] = array(clean_param($entry->subject, PARAM_TEXT), clean_param($entry->message, PARAM_TEXT), $user->lastname . ', ' . $user->firstname);
    }


    return $table;
}

/**
 * Resets all discussions on the current forum
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function reset_all_discussions($forumID, $courseID, $data, $userid)
{
    global $DB;
    // Only let users with the appropriate be able to reset forums.
    $course = $DB->get_record('course', ['id' => $courseID]);
    $coursecontext = context_course::instance($course->id);
    if (!has_capability('local/forumreset:resetforum', $coursecontext)) {
        $url_back = new moodle_url(
            '/course/view.php',
            array('id' => $courseID)
        );
        redirect($url_back, 'sie haben nicht die passenden Berechtigungen!', null, \core\output\notification::NOTIFY_ERROR);
    }
    $discussions = $DB->get_records('forum_discussions', ['forum' => $forumID, 'course' => $courseID]);

    foreach ($discussions as $entry) {
        $posts = $DB->get_records('forum_posts', ['discussion' => $entry->id]);
        if (in_array($entry->id, $data->selectdiscussions)) {
            //Keep first level posts of this discussion
            foreach ($posts as $postentry) {
                if ($entry->firstpost != $postentry->id) {
                    if ($postentry->parent == $entry->firstpost && $entry->userid != $postentry->userid) {
                        $DB->delete_records('forum_posts', ['id' => $postentry->id]);
                    }
                    if ($postentry->parent != $entry->firstpost) {
                        $DB->delete_records('forum_posts', ['id' => $postentry->id]);
                    }
                }
            }
        } else {
            //Delete first level posts, too.
            foreach ($posts as $postentry) {
                if ($entry->firstpost != $postentry->id) {
                    $DB->delete_records('forum_posts', ['id' => $postentry->id]);
                }
            }
        }
    }

    return;
}

/**
 * Resets all discussions on the current forum
 *
 * @param int    $courseid   ID of the course
 * @return array table of activities
 */
function delete_all_content($forumID, $courseID, $data)
{
    global $DB;

    $discussions = $DB->get_records('forum_discussions', ['forum' => $forumID, 'course' => $courseID]);

    foreach ($discussions as $entry) {
        // Delete all posts of this discussion first
        $posts = $DB->get_records('forum_posts', ['discussion' => $entry->id]);
        foreach ($posts as $postentry) {
            $DB->delete_records('forum_posts', ['id' => $postentry->id]);
        }
        // Afterwards delete also this discussion
        $DB->delete_records('forum_discussions', ['id' => $entry->id, 'forum' => $forumID, 'course' => $courseID]);
    }
    return;
}
