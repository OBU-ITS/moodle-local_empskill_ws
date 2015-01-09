<?php

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
 * Employability Skill web service - service functions
 * @package   local_empskill_ws
 * @author    Peter Welham
 * @copyright 2015, Oxford Brookes University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Define the web service functions to install.
$functions = array(
        'local_empskill_ws_get_tag_id' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_tag_id',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns the id of the official tag with the given name (tag_id). The tag_rawname is passed in as a parameter.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
         'local_empskill_ws_get_tag_name' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_tag_name',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns a tag with the given id (tag_name, tag_description). The tag_id is passed in as a parameter.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_faculties' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_faculties',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns an array of faculties (faculty_name, faculty_id) that this user can view courses in.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_faculty_stats' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_faculty_stats',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'For a given faculty and skill category, returns an array of months (month_name, month_posts, month_associations).  The required faculty_id and category_id are passed in as parameters.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_course_stats' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_course_stats',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'For a given faculty and skill category, returns an array of current courses sorted by name (course_name, course_id, course_bloggers, course_associations).  The required faculty_id and category_id are passed in as parameters.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_skill_stats' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_skill_stats',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'For a given faculty, skill category and course, returns an array of skill categories sorted by name (skill_name, skill_id, skill_bloggers, skill_associations).  The required faculty_id, category_id and course_id are passed in as parameters.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_skill_categories' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_skill_categories',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'For a given class, returns an array of site-wide skill categories sorted by name (category_name, class_id, category_id).  The required class_name is passed in as parameter.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
         'local_empskill_ws_get_skills' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_skills',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns an array of skills for a given category sorted by name (skill_name skill_entries, skill_id,). The class_id and category_id are passed in as parameters.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_entries' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_entries',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns an array of entries (newest first) from the users\'s blog for the given category or skill (entry_title, entry_date, entry_id). The class_id, category_id and skill_id are passed in as parameters.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_entry' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_entry',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns user\'s blog entry with the given id (entry_title, entry_date, entry_course_id, entry_course_number, entry_course_name, entry_body, entry_link, entry_private flag). The entry_id is passed in as parameter.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_get_current_courses' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'get_current_courses',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Returns an array of courses (course_id, course_number, course_name) running in current semester that user is enrolled on. Run dates are removed from course_name.',
                'type'        => 'read',
				'capabilities'=> 'moodle/blog:create'
        ),
        'local_empskill_ws_save_entry' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'save_entry',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Adds or amends an entry in users\'s blog. The entry_id (if any), entry_title, entry_course_id, entry_body, entry_link, entry_private flag, entry_tag_1 and entry_tag_2 are passed in as parameters.  The entry_id is returned.',
                'type'        => 'write',
				'capabilities'=> 'moodle/blog:create'
        ),
		'local_empskill_ws_delete_entry' => array(
                'classname'   => 'local_empskill_ws_external',
                'methodname'  => 'delete_entry',
                'classpath'   => 'local/empskill_ws/externallib.php',
                'description' => 'Deletes entry from users\'s blog. The entry_id is passed in as a parameter.',
                'type'        => 'write',
				'capabilities'=> 'moodle/blog:create'
		)
);

// Define the services to install as pre-build services.
$services = array(
        'Employability Skill web service' => array(
			'shortname' => 'empskill_ws',
            'functions' => array(
				'local_empskill_ws_get_tag_id',
				'local_empskill_ws_get_tag_name',
				'local_empskill_ws_get_faculties',
				'local_empskill_ws_get_faculty_stats',
				'local_empskill_ws_get_course_stats',
				'local_empskill_ws_get_skill_stats',
				'local_empskill_ws_get_skill_categories',
				'local_empskill_ws_get_skills',
				'local_empskill_ws_get_entries',
				'local_empskill_ws_get_entry',
				'local_empskill_ws_get_current_courses',
				'local_empskill_ws_save_entry',
				'local_empskill_ws_delete_entry'
			),
            'restrictedusers' => 0,
            'enabled' => 1
        )
);
