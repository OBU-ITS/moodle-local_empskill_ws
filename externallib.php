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

/*
 * Employability Skill web service - external library
 *
 * @package    local_empskill_ws
 * @author     Peter Welham
 * @copyright  2015, Oxford Brookes University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
 
require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/course/lib.php");

class local_empskill_ws_external extends external_api {

	public static function get_tag_id_parameters() {
		return new external_function_parameters(
			array(
				'tag_rawname' => new external_value(PARAM_TEXT, 'Tag name')
			)
		);
	}

	public static function get_tag_id_returns() {
		return new external_single_structure(
			array(
				'tag_id' => new external_value(PARAM_INT, 'Tag ID')
			)
		);
	}

	public static function get_tag_id($tag_rawname) {
		global $CFG, $DB;

		// Parameter validation
		$params = self::validate_parameters(
			self::get_tag_id_parameters(), array(
				'tag_rawname' => $tag_rawname
			)
		);

		if (strlen($params['tag_rawname']) < 1) {
			throw new invalid_parameter_exception('tag_rawname must be a non-empty string');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		// Get the tag ID of the given tag
		$criteria = "rawname = '" . $params['tag_rawname'] . "' AND tagtype = 'official'";
	    if (!($tag = $DB->get_record_select('tag', $criteria, null, 'id'))) {
			throw new invalid_parameter_exception('tag not found');
		}
		
		return array('tag_id' => $tag->id);
	}

	public static function get_tag_name_parameters() {
		return new external_function_parameters(
			array(
				'tag_id' => new external_value(PARAM_INT, 'ID of the tag required')
			)
		);
	}

	public static function get_tag_name_returns() {
		return new external_single_structure(
			array(
				'tag_name' => new external_value(PARAM_TEXT, 'Tag name'),
				'tag_description' => new external_value(PARAM_TEXT, 'Tag description')
			)
		);
	}

	public static function get_tag_name($tag_id) {
		global $CFG, $DB;

		$params = self::validate_parameters(
			self::get_tag_name_parameters(), array(
				'tag_id' => $tag_id
			)
		);
		
		if ($params['tag_id'] < 1) {
			throw new invalid_parameter_exception('tag_id must be a positive integer');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		// Get the given tag
		$criteria = "id = '" . $params['tag_id'] . "'";
	    if (!($tag = $DB->get_record_select('tag', $criteria, null, 'rawname, description'))) {
			throw new invalid_parameter_exception('tag not found');
		}
		
		return array(
			'tag_name' => $tag->rawname,
			'tag_description' => strip_tags($tag->description)
		);
	}
	
	public static function get_faculties_parameters() {
		return new external_function_parameters(array());
	}

	public static function get_faculties_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'faculty_name' => new external_value(PARAM_TEXT, 'Faculty name'),
					'faculty_id' => new external_value(PARAM_INT, 'Faculty ID')
				)
			)
		);
	}

	public static function get_faculties() {
		global $CFG, $DB, $USER;

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		// Get course_viewer role
		$course_viewer_role = $DB->get_record('role', array('shortname'=>'viewer'), 'id', MUST_EXIST);

		require_once($CFG->dirroot . '/course/externallib.php');

		$faculties = array();
		$db_ret = self::get_categories(); // Course categories
		foreach ($db_ret as $row) {
			if ($row->parent == '122') { // Top-level PIP-linked course categories
				if (strncmp($row->name, 'Faculty', 7) == 0) {
					// Check whether current user is a course viewer
					$context = context_coursecat::instance($row->id);
					$course_viewers = get_role_users($course_viewer_role->id, $context, false, 'u.id');
					foreach ($course_viewers as $course_viewer) {
						if ($course_viewer->id == $USER->id) {
							$faculties[] = array(
								'faculty_name' => $row->name,
								'faculty_id' => $row->id
							);
						}
					}
				}
			}
		}

		return $faculties;
	}

	public static function get_faculty_stats_parameters() {
		return new external_function_parameters(
			array(
				'faculty_id' => new external_value(PARAM_INT, 'ID of the faculty required'),
				'category_id' => new external_value(PARAM_INT, 'ID of the skill category required')
			)
		);
	}

	public static function get_faculty_stats_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'month_name' => new external_value(PARAM_TEXT, 'Name of month'),
					'month_posts' => new external_value(PARAM_INT, 'Average posts per current student (*10)'),
					'month_associations' => new external_value(PARAM_INT, 'Average course-associated posts per current student (*10)')
				)
			)
		);
	}

	public static function get_faculty_stats($faculty_id, $category_id) {
		global $CFG, $DB;

		$params = self::validate_parameters(
			self::get_faculty_stats_parameters(), array(
				'faculty_id' => $faculty_id,
				'category_id' => $category_id
			)
		);
		
		if ($params['faculty_id'] < 1) {
			throw new invalid_parameter_exception('faculty_id must be a positive integer');
		}

		if ($params['category_id'] < 1) {
			throw new invalid_parameter_exception('category_id must be a positive integer');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);
		
		require_once($CFG->dirroot . '/course/externallib.php');

		$month_names = array(
			'January', 'February', 'March', 'April', 'May', 'June',
			'July', 'August', 'September', 'October', 'November', 'December'
		);
		$months = array();
		$stats = self::get_stats('faculty', $params['faculty_id'], $params['category_id'], 0);
		foreach ($stats as $stat) {
			if ($stat['stat_name'] == 'stat_totals') {
				$students = $stat['stat_students'];
			} else {
				$associations = $stat['stat_associations'];
				$posts = $stat['stat_posts'];
					
				// Convert the numbers to averages
				if ($students > 0) {
					$posts = floor(($posts * 10) / $students + 0.5); // Average posts per current student (*10)
					$associations = floor(($associations * 10) / $students + 0.5); // Average course-associated posts per current student (*10)
				}
					
				$month_number = $stat['stat_name'];
				$month_number = substr($month_number, 4);
				$month_name = $month_names[$month_number - 1];
				$months[] = array(
					'month_name' => $month_name,
					'month_posts' => $posts,
					'month_associations' => $associations
				);
			}
		}
		
		return $months;
	}

	public static function get_course_stats_parameters() {
		return new external_function_parameters(
			array(
				'faculty_id' => new external_value(PARAM_INT, 'ID of the faculty required'),
				'category_id' => new external_value(PARAM_INT, 'ID of the skill category required')
			)
		);
	}

	public static function get_course_stats_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'course_id' => new external_value(PARAM_INT, 'Course ID'),
					'course_number' => new external_value(PARAM_TEXT, 'Number of course'),
					'course_name' => new external_value(PARAM_TEXT, 'Name of course'),
					'course_bloggers' => new external_value(PARAM_INT, 'Course bloggers (%)'),
					'course_associations' => new external_value(PARAM_INT, 'Course associations (%)')
				)
			)
		);
	}

	public static function get_course_stats($faculty_id, $category_id) {
		global $CFG, $DB;

		$params = self::validate_parameters(
			self::get_course_stats_parameters(), array(
				'faculty_id' => $faculty_id,
				'category_id' => $category_id
			)
		);
		
		if ($params['faculty_id'] < 1) {
			throw new invalid_parameter_exception('faculty_id must be a positive integer');
		}

		if ($params['category_id'] < 1) {
			throw new invalid_parameter_exception('category_id must be a positive integer');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);
		
		require_once($CFG->dirroot . '/course/externallib.php');

		$courses = array();
		$stats = array();
		$db_category_ret = self::get_categories(); // Course categories
		foreach ($db_category_ret as $category) {
			if ($category->parent == $params['faculty_id']) {
				$db_course_ret = self::get_current_courses($category->id);
				foreach ($db_course_ret as $course) {
					$stats = self::get_stats('course', $params['faculty_id'], $params['category_id'], $course['course_id']);
					$total = $stats[0];
					$bloggers = $total['stat_bloggers'];
					$students = $total['stat_students'];
					$associations = $total['stat_associations'];
					$posts = $total['stat_posts'];
					
					// Convert the numbers to percentages
					if ($students > 0) {
						$bloggers = floor(($bloggers * 100) / $students + 0.5); // Course students that have, at some time, posted a skill entry
					}
					if ($posts > 0) {
						$associations = floor(($associations * 100) / $posts + 0.5); // Skill entries posted by course students that they associated with the course
					}
					
					$courses[] = array(
						'course_id' => $course['course_id'],
						'course_number' => $course['course_number'],
						'course_name' => $course['course_name'],
						'course_bloggers' => $bloggers,
						'course_associations' => $associations
					);
				}
			}
		}
		
		// Sort the courses by number
		usort($courses, function($a, $b) {
			return (strcmp($a['course_number'], $b['course_number']));
		});
		
		return $courses;
	}

	public static function get_skill_stats_parameters() {
		return new external_function_parameters(
			array(
				'faculty_id' => new external_value(PARAM_INT, 'ID of the faculty required'),
				'category_id' => new external_value(PARAM_INT, 'ID of the skill category required'),
				'course_id' => new external_value(PARAM_INT, 'ID of the course required')
			)
		);
	}

	public static function get_skill_stats_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'skill_name' => new external_value(PARAM_TEXT, 'Skill name'),
					'skill_bloggers' => new external_value(PARAM_INT, 'Skill bloggers (%)'),
					'skill_associations' => new external_value(PARAM_INT, 'Skill associations (%)')
				)
			)
		);
	}

	public static function get_skill_stats($faculty_id, $category_id, $course_id) {
		global $CFG, $DB;

		$params = self::validate_parameters(
			self::get_skill_stats_parameters(), array(
				'faculty_id' => $faculty_id,
				'category_id' => $category_id,
				'course_id' => $course_id
			)
		);
		
		if ($params['faculty_id'] < 1) {
			throw new invalid_parameter_exception('faculty_id must be a positive integer');
		}

		if ($params['category_id'] < 1) {
			throw new invalid_parameter_exception('category_id must be a positive integer');
		}

		if ($params['course_id'] < 0) {
			throw new invalid_parameter_exception('course_id must not be negative');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);
		
		$skills = array();
		$stats = self::get_stats('skill', $params['faculty_id'], $params['category_id'], $params['course_id']);
		foreach ($stats as $stat) {
			if ($stat['stat_name'] == 'stat_totals') {
				$students = $stat['stat_students'];
			} else {
				$bloggers = $stat['stat_bloggers'];
				$associations = $stat['stat_associations'];
				$posts = $stat['stat_posts'];
					
				// Convert the numbers to percentages
				if ($students > 0) {
					$bloggers = floor(($bloggers * 100) / $students + 0.5); // Faculty/course students that have, at some time, posted an entry for this skill
				}
				if ($posts > 0) {
					$associations = floor(($associations * 100) / $posts + 0.5); // Entries for this skill posted by faculty/course students that they associated with a/the course
				}
					
				$skills[] = array(
					'skill_name' => $stat['stat_name'],
					'skill_bloggers' => $bloggers,
					'skill_associations' => $associations
				);
			}
		}
		
		return $skills;
	}

	public static function get_skill_categories_parameters() {
		return new external_function_parameters(
			array(
				'class_name' => new external_value(PARAM_TEXT, 'Class name')
			)
		);
	}

	public static function get_skill_categories_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'category_name' => new external_value(PARAM_TEXT, 'Category name'),
					'class_id' => new external_value(PARAM_INT, 'Class ID'),
					'category_id' => new external_value(PARAM_INT, 'Category ID')
				)
			)
		);
	}

	public static function get_skill_categories($class_name) {
		global $CFG, $DB;

		// Parameter validation
		$params = self::validate_parameters(
			self::get_skill_categories_parameters(), array(
				'class_name' => $class_name
			)
		);

		if (strlen($params['class_name']) < 1) {
			throw new invalid_parameter_exception('class_name must be a non-empty string');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		// Get the tag ID of the given category class name
		$criteria = "rawname = '" . $params['class_name'] . "'";
	    if (!($class = $DB->get_record_select('tag', $criteria, null, 'id'))) {
			throw new invalid_parameter_exception('category_type tag not found');
		}
		
		// Store the ID of each related tag (category) in an array
		$criteria = "tagid = '" . $class->id . "' AND itemtype = 'tag'";
		$db_ret = $DB->get_records_select('tag_instance', $criteria, null, 'itemid');
		$ids = array();
		foreach ($db_ret as $row) {
			$ids[] = $row->itemid;
		}
		
		// Get the sorted names of the related tags (categories) and store them in an array
		$db_ret = $DB->get_records_list('tag', 'id', $ids, 'name', 'rawname, id');
		$categories = array();
		foreach ($db_ret as $row) {
			$categories[] = array(
				'category_name' => $row->rawname,
				'class_id' => $class->id,
				'category_id' => $row->id
			);
		}

		return $categories;
	}

	public static function get_skills_parameters() {
		return new external_function_parameters(
			array(
				'class_id' => new external_value(PARAM_INT, 'ID of the skills category class'),
				'category_id' => new external_value(PARAM_INT, 'ID of the required skills category')
			)
		);
	}

	public static function get_skills_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'skill_name' => new external_value(PARAM_TEXT, 'Skill name'),
					'skill_id' => new external_value(PARAM_INT, 'Skill ID'),
					'skill_entries' => new external_value(PARAM_INT, 'Skill blog entry count')
				)
			)
		);
	}

	public static function get_skills($class_id, $category_id) {
		global $CFG, $DB;

		$params = self::validate_parameters(
			self::get_skills_parameters(), array(
				'class_id' => $class_id,
				'category_id' => $category_id
			)
		);
		
		if ($params['class_id'] < 0) {
			throw new invalid_parameter_exception('class_id must not be a negative integer');
		}

		if ($params['category_id'] < 1) {
			throw new invalid_parameter_exception('category_id must be a positive integer');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		// Store the ID of each related tag (skill) in an array
		$criteria = "tagid = '" . $params['category_id'] . "' AND itemtype = 'tag' AND itemid <> '" . $params['class_id'] . "'";
		$db_ret = $DB->get_records_select('tag_instance', $criteria, null, 'itemid');
		$ids = array();
		foreach ($db_ret as $row) {
			$ids[] = $row->itemid;
		}
		
		require_once($CFG->dirroot . '/blog/locallib.php');

		// Get the sorted names of the related tags (skills) and store them in an array
		$db_ret = $DB->get_records_list('tag', 'id', $ids, 'name', 'rawname, id');
		$skills = array();
		foreach ($db_ret as $row) {
			$blog = new blog_listing(
				array(
					'user' => $USER->id,
					'tag' => $row->id
				)
			);
			$skills[] = array(
				'skill_name' => $row->rawname,
				'skill_entries' => count($blog->get_entries()),
				'skill_id' => $row->id
			);
		}

		return $skills;
	}

	public static function get_entries_parameters() {
		return new external_function_parameters(
			array(
				'class_id' => new external_value(PARAM_INT, 'ID of the required category class'),
				'category_id' => new external_value(PARAM_INT, 'ID of the category for which to return entries'),
				'skill_id' => new external_value(PARAM_INT, 'ID of the skill (0 for all) for which to return entries')
			)
		);
	}

	public static function get_entries_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'entry_title' => new external_value(PARAM_TEXT, 'Entry title'),
					'entry_date' => new external_value(PARAM_TEXT, 'Entry creation date'),
					'entry_id' => new external_value(PARAM_INT, 'Entry ID')
				)
			)
		);
	}

	public static function get_entries($class_id, $category_id, $skill_id) {
		global $CFG, $DB, $USER;

		$params = self::validate_parameters(
			self::get_entries_parameters(), array(
				'class_id' => $class_id,
				'category_id' => $category_id,
				'skill_id' => $skill_id
			)
		);
		
		if ($params['class_id'] < 0) {
			throw new invalid_parameter_exception('class_id must not be negative');
		}

		if ($params['category_id'] < 1) {
			throw new invalid_parameter_exception('category_id must be a positive integer');
		}

		if ($params['skill_id'] < 0) {
			throw new invalid_parameter_exception('skill_id must not be negative');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);
		
		require_once($CFG->dirroot . '/blog/locallib.php');
		
		$list = array();
		if ($params['skill_id']) { // Just for one skill
			$blog = new blog_listing(
				array(
					'user' => $USER->id,
					'tag' => $params['skill_id']
				)
			);
			$list = $blog->get_entries();
		}
		else // For all skills in the category
		{
			// Get the ID of each related tag (skill) and store all of it's blog entries in an array
			$criteria = "tagid = '" . $params['category_id'] . "' AND itemtype = 'tag' AND itemid <> '" . $params['class_id'] . "'";
			$db_ret = $DB->get_records_select('tag_instance', $criteria, null, 'itemid');
			foreach ($db_ret as $row) {
				$blog = new blog_listing(
					array(
						'user' => $USER->id,
						'tag' => $row->itemid
					)
				);
				$list = array_merge($list, $blog->get_entries());
			}
			
			// Sort the array into descending ID order (newest entry first)
			usort($list, function($a, $b) {
				return ($b->id - $a->id);
			});
		}
		
		$entries = array();
		foreach ($list as $entry) {
			$entries[] = array(
				'entry_title' => $entry->subject,
				'entry_date' => date('l, j F Y, g:i A', $entry->created),
				'entry_id' => $entry->id
			);
		}

		return $entries;
	}

	public static function get_entry_parameters() {
		return new external_function_parameters(
			array(
				'entry_id' => new external_value(PARAM_INT, 'ID of the blog entry required')
			)
		);
	}

	public static function get_entry_returns() {
		return new external_single_structure(
			array(
				'entry_title' => new external_value(PARAM_TEXT, 'Entry title'),
				'entry_date' => new external_value(PARAM_TEXT, 'Entry creation date'),
				'entry_course_id' => new external_value(PARAM_INT, 'Entry course ID (if any)'),
				'entry_course_number' => new external_value(PARAM_TEXT, 'Entry course number'),
				'entry_course_name' => new external_value(PARAM_TEXT, 'Entry course name'),
				'entry_body' => new external_value(PARAM_TEXT, 'Entry body'),
				'entry_link' => new external_value(PARAM_TEXT, 'Entry link'),
				'entry_private' => new external_value(PARAM_BOOL, 'If true (non-zero), entry is not made public')
			)
		);
	}

	public static function get_entry($entry_id) {
		global $CFG, $DB, $USER;

		$params = self::validate_parameters(
			self::get_entry_parameters(), array(
				'entry_id' => $entry_id
			)
		);
		
		if ($params['entry_id'] < 1) {
			throw new invalid_parameter_exception('entry_id must be a positive integer');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		require_once($CFG->dirroot . '/blog/locallib.php');

		// Get the given entry and check that it belongs to the user
		$entry = new blog_entry($params['entry_id']);
		if ($entry->userid <> $USER->id) {
			throw new invalid_parameter_exception('entry_id not owned by this user');
		}
		
		if ($entry->courseassoc == 0) {
			$course_id = 0;
			$course_number = '';
			$course_name = '';
		} else if (!($context = $DB->get_record('context', array('id' => $entry->courseassoc)))) {
				throw new invalid_parameter_exception('invalid context_id');
		} else if (!($course = $DB->get_record('course', array('id' => $context->instanceid)))) {
				throw new invalid_parameter_exception('invalid course_id');
		} else {
			$course_id = $course->id;
			$split_pos = strpos($course->fullname, ': ');
			if ($split_pos !== false) {
				$course_number = substr($course->fullname, 0, $split_pos);
				$course_name = substr($course->fullname, ($split_pos + 2));
			} else {
				$course_number = '';
				$course_name = $course_fullname;
			}
			$split_pos = strpos($course_name, ' (');
			if ($split_pos !== false) {
				$course_name = substr($course_name, 0, $split_pos);
			}
		}
		
		$body = $entry->summary;
		$split_pos = strpos($body, '<p><a id="es-link" href="');
		if ($split_pos == false) {
			$link = '';
		} else {
			$link = substr($body, ($split_pos + 25));
			$body = substr($body, 0, $split_pos);
			$split_pos = strpos($link, '"');
			if ($split_pos !== false) {
				$link = substr($link, 0, $split_pos);
			}
		}
		$body = strip_tags($body);
		
		$entry = array(
			'entry_title' => $entry->subject,
			'entry_date' => date('l, j F Y, g:i A', $entry->created),
			'entry_course_id' => $course_id,
			'entry_course_number' => $course_number,
			'entry_course_name' => $course_name,
			'entry_body' => $body,
			'entry_link' => $link,
			'entry_private' => ($entry->publishstate == 'draft')
		);
		
		return $entry;
	}
	
	public static function get_current_courses_parameters() {
		return new external_function_parameters(
			array(
				'category_id' => new external_value(PARAM_INT, 'ID of the category required')
			)
		);
	}
	
	public static function get_current_courses_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'course_id' => new external_value(PARAM_INT, 'Course ID'),
					'course_number' => new external_value(PARAM_TEXT, 'Number of course'),
					'course_name' => new external_value(PARAM_TEXT, 'Name of course')
				)
			)
		);
	}

	public static function get_current_courses($category_id) {
		global $CFG, $DB, $USER;

		$params = self::validate_parameters(
			self::get_current_courses_parameters(), array(
				'category_id' => $category_id
			)
		);

		if ($params['category_id'] < 0) {
			throw new invalid_parameter_exception('category_id must not be negative');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		if ($params['category_id'] == 0) {
			// Get user's visible courses
			$sql = 'SELECT c.id, c.fullname, c.shortname '
				. 'FROM {course} c '
				. 'JOIN {enrol} e ON e.courseid = c.id '
				. 'JOIN {user_enrolments} ue ON ue.enrolid = e.id '
				. 'WHERE ue.userid = ? AND c.visible = 1 AND substr(c.shortname, 7, 1) = " " AND substr(c.shortname, 13, 1) = "-" AND length(c.shortname) >= 18';
			$db_ret = $DB->get_records_sql($sql, array($USER->id));
		} else {
			// Get all visible courses for the given category
			$sql = 'SELECT c.id, c.fullname, c.shortname '
				. 'FROM {course} c '
				. 'WHERE c.category = ? AND c.visible = 1 AND substr(c.shortname, 7, 1) = " " AND substr(c.shortname, 13, 1) = "-" AND length(c.shortname) >= 18';
			$db_ret = $DB->get_records_sql($sql, array($params['category_id']));
		}

		$courses = array();
		$now = time();
		foreach ($db_ret as $row) {
			$context = context_course::instance($row->id);
			if (($params['category_id'] != 0) || is_enrolled($context)) {
				// Check that course is currently running
				$course_start = strtotime('01 ' . substr($row->shortname, 7, 3) . ' ' . substr($row->shortname, 10, 2));
				$course_end = strtotime('31 ' .	substr($row->shortname, 13, 3) . ' ' . substr($row->shortname, 16, 2));
				if (($course_start <= $now) && ($course_end >= $now)) {
					$split_pos = strpos($row->fullname, ': ');
					if ($split_pos !== false) {
						$number = substr($row->fullname, 0, $split_pos);
						$name = substr($row->fullname, ($split_pos + 2));
					} else {
						$number = '';
						$name = $row_fullname;
					}
				
					$split_pos = strpos($name, ' (');
					if ($split_pos !== false) {
						$name = substr($name, 0, $split_pos);
					}
				
					$courses[] = array(
						'course_id' => $row->id,
						'course_number' => $number,
						'course_name' => $name
					);
				}
			}
		}

		return $courses;
	}

	public static function save_entry_parameters() {
		return new external_function_parameters(
			array(
				'entry_id' => new external_value(PARAM_INT, 'ID of entry to amend (if any)'),
				'entry_title' => new external_value(PARAM_TEXT, 'Entry title'),
				'entry_course_id' => new external_value(PARAM_INT, 'Entry course ID (zero if none)'),
				'entry_body' => new external_value(PARAM_TEXT, 'Entry body'),
				'entry_link' => new external_value(PARAM_TEXT, 'Entry link'),
				'entry_private' => new external_value(PARAM_BOOL, 'If true (non-zero), entry is not made public'),
				'entry_tag_1' => new external_value(PARAM_TEXT, 'First tag for the entry (empty if none)'),
				'entry_tag_2' => new external_value(PARAM_TEXT, 'Second tag for the entry (empty if none)')
			)
		);
	}

	public static function save_entry_returns() {
		return new external_single_structure(
			array(
				'entry_id' => new external_value(PARAM_INT, 'ID of added or amended entry')
			)
		);
	}

	public static function save_entry($entry_id, $entry_title, $entry_course_id, $entry_body, $entry_link, $entry_private, $entry_tag_1, $entry_tag_2) {
		global $CFG, $DB, $USER;

		// Parameter validation
		$params = self::validate_parameters(
			self::save_entry_parameters(), array(
				'entry_id' => $entry_id,
				'entry_title' => $entry_title,
				'entry_course_id' => $entry_course_id,
				'entry_body' => $entry_body,
				'entry_link' => $entry_link,
				'entry_private' => $entry_private,
				'entry_tag_1' => $entry_tag_1,
				'entry_tag_2' => $entry_tag_2
			)
		);

		if ($params['entry_id'] < 0) {
			throw new invalid_parameter_exception('entry_id must not be negative');
		}

		if (strlen($params['entry_title']) < 1) {
			throw new invalid_parameter_exception('entry_title must be a non-empty string');
		}

		if ($params['entry_course_id'] < 0) {
			throw new invalid_parameter_exception('entry_course_id must not be negative');
		}

		if (strlen($params['entry_body']) < 1) {
			throw new invalid_parameter_exception('entry_body must be a non-empty string');
		}
		
		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		require_once($CFG->dirroot . '/blog/locallib.php');
		require_once($CFG->dirroot . '/tag/locallib.php');
		
		if ($params['entry_id'] == 0) {
			// Adding
			$entry = new blog_entry();
			$entry->userid = $USER->id;
		} else {
			// Get any given entry and check that it belongs to the user
			$entry = new blog_entry($params['entry_id']);
			if ($entry->userid <> $USER->id) {
				throw new invalid_parameter_exception('entry_id not owned by this user');
			}
		}
		
		if (strlen($params['entry_title'] > 128)) {
			$entry->subject = substr($params['entry_title'], 0, 125) . '...';
		} else {
			$entry->subject = $params['entry_title'];
		}
		if ($params['entry_course_id']) {
			$entry->courseassoc = context_course::instance($params['entry_course_id'])->id;
		} else {
			$entry->courseassoc = 0;
		}
		$entry->summary = $params['entry_body'];
		if (strlen($params['entry_link'])) {
			$entry->summary = $entry->summary . '<p><a id="es-link" href="' . $params['entry_link'] . '" target="_blank">' . $params['entry_link'] . '</a></p>';
		}
		$entry->attachment = '';
		$entry->publishstate = ($entry_private) ? 'draft' : 'site';
		
		if ($params['entry_id'] == 0) {
			// Tags can only be set when adding and can't be changed
			if (strlen($params['entry_tag_1']) > 0) {
				$entry->tags[] = $params['entry_tag_1'];
			}
			if (strlen($params['entry_tag_2']) > 0) {
				$entry->tags[] = $params['entry_tag_2'];
			}
			$entry->add(); // Standard function
		} else {
			// Unfortunately we can't use the standard blog entry 'edit' function because we don't have an actual input form
			if (!empty($CFG->useblogassociations)) {
				$entry->add_associations();
			}
			$entry->lastmodified = time();
			$DB->update_record('post', $entry);

			// Record the happy occasion
			$event = \core\event\blog_entry_updated::create(array(
				'objectid'      => $entry->id,
				'relateduserid' => $entry->userid
			));
			$event->set_blog_entry($entry);
			$event->trigger();
		}

		return array('entry_id' => $entry->id);
	}

	public static function delete_entry_parameters() {
		return new external_function_parameters(
			array(
				'entry_id' => new external_value(PARAM_INT, 'ID of entry to delete')
			)
		);
	}

	public static function delete_entry_returns() {
		return null;
	}

	public static function delete_entry($entry_id) {
		global $CFG, $DB, $USER;

		// Parameter validation
		$params = self::validate_parameters(
				self::delete_entry_parameters(), array(
					'entry_id' => $entry_id
				)
		);

		if ($params['entry_id'] < 1) {
			throw new invalid_parameter_exception('entry_id must be a positive integer');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);

		require_once($CFG->dirroot . '/blog/locallib.php');
		require_once($CFG->dirroot . '/tag/locallib.php');

		// Get the given entry and check that it belongs to the user
		$entry = new blog_entry($params['entry_id']);
		if ($entry->userid <> $USER->id) {
			throw new invalid_parameter_exception('entry_id not owned by this user');
		}
		
		// Delete the blog entry
		$entry->delete();

		return;
	}

	private static function get_categories() { // Course categories
		global $DB;

		$categories = array();

		list($ccselect, $ccjoin) = context_instance_preload_sql('cc.id', CONTEXT_COURSECAT, 'ctx');
		$sql = "SELECT cc.* $ccselect FROM {course_categories} cc $ccjoin ORDER BY cc.sortorder ASC";
		$rs = $DB->get_recordset_sql($sql, array());
		foreach($rs as $cat) {
			context_helper::preload_from_record($cat);
			$catcontext = context_coursecat::instance($cat->id);
			if ($cat->visible || has_capability('moodle/category:viewhiddencategories', $catcontext)) {
				$categories[$cat->id] = $cat;
			}
		}
		$rs->close();
		
		return $categories;
	}

	private static function get_stats_parameters() {
		return new external_function_parameters(
			array(
				'stats_type' => new external_value(PARAM_TEXT, 'Type of stats required'),
				'faculty_id' => new external_value(PARAM_INT, 'ID of the faculty required'),
				'category_id' => new external_value(PARAM_INT, 'ID of the skill category required'),
				'course_id' => new external_value(PARAM_INT, 'ID of the course required')
			)
		);
	}

	private static function get_stats_returns() {
		return new external_multiple_structure(
			new external_single_structure(
				array(
					'stat_name' => new external_value(PARAM_TEXT, 'Statistic name'),
					'stat_bloggers' => new external_value(PARAM_INT, 'Bloggers (students with posts)'),
					'stat_students' => new external_value(PARAM_INT, 'Total students (zero for individual skills)'),
					'stat_associations' => new external_value(PARAM_INT, 'Posts with course associations'),
					'stat_posts' => new external_value(PARAM_INT, 'Total posts')
				)
			)
		);
	}

	private static function get_stats($stats_type, $faculty_id, $category_id, $course_id) {
		global $CFG, $DB;

		$params = self::validate_parameters(
			self::get_stats_parameters(), array(
				'stats_type' => $stats_type,
				'faculty_id' => $faculty_id,
				'category_id' => $category_id,
				'course_id' => $course_id
			)
		);
		
		if (strlen($params['stats_type']) < 1) {
			throw new invalid_parameter_exception('stats_type must be a non-empty string');
		}

		if ($params['faculty_id'] < 1) {
			throw new invalid_parameter_exception('faculty_id must be a positive integer');
		}

		if ($params['category_id'] < 1) {
			throw new invalid_parameter_exception('category_id must be a positive integer');
		}

		if ($params['course_id'] < 0) {
			throw new invalid_parameter_exception('course_id must not be negative');
		}

		// Context validation
		$context = context_system::instance();
		self::validate_context($context);

        // Capability checking
		require_capability('moodle/blog:create', $context);
		
		require_once($CFG->dirroot . '/course/externallib.php');
		
		// Get a list of the required courses
		$courses = array();
		if ($params['course_id'] > 0) {
			$courses[] = $params['course_id'];
		} else {
			$db_category_ret = self::get_categories(); // Course categories
			foreach ($db_category_ret as $category) {
				if ($category->parent == $params['faculty_id']) {
					$db_course_ret = self::get_current_courses($category->id);
					foreach ($db_course_ret as $course) {
						$courses[] = $course['course_id'];
					}
				}
			}
		}
		
		// Get a list of all enrolled students for the courses
		$student_ids = array();
		$student_posts = array();
		$student_role = $DB->get_record('role', array('shortname'=>'student'), 'id', MUST_EXIST);
		foreach ($courses as $course_id) {
			$context = context_course::instance($course_id);
			$users = get_role_users($student_role->id, $context, false, 'u.id');
			foreach ($users as $user) {
				if (is_enrolled($context, $user->id)) {
					if (!in_array($user->id, $student_ids, true)) {
						$student_ids[] = $user->id;
						$student_posts[] = 0;
					}
				}
			}
		}

		// Get a list of this and the previous 11 month numbers
		$month_numbers = array();
		$month_stats = array();
		$year = date('Y');
		$month = date('n');
		for ($index = 0; $index < 12; $index++) {
			if ($month < 10) {
				$month_numbers[] = $year . '0' . $month;
			} else {
				$month_numbers[] = $year . $month;
			}
			$month_stats[] = array(
				'month_posts' => 0,
				'month_associations' => 0
			);
			if ($month > 1) {
				$month--;
			} else {
				$year--;
				$month = 12;
			}
		}
		
		// Store the ID of each related tag (skill) in an array
		$criteria = "tagid = '" . $params['category_id'] . "' AND itemtype = 'tag'";
		$db_ret = $DB->get_records_select('tag_instance', $criteria, null, 'itemid');
		$ids = array();
		foreach ($db_ret as $row) {
			$ids[] = $row->itemid;
		}
		
		// Get the statistics
		$skills = array();
		$total_associations = 0;
		$db_skills_ret = $DB->get_records_list('tag', 'id', $ids, 'name', 'id, rawname');
		foreach ($db_skills_ret as $skill) {
			$skill_bloggers = 0;
			$skill_associations = 0;
			$skill_posts = 0;
			foreach ($student_ids as $index => $student_id) {
				$sql = 'SELECT p.id, p.created '
					. 'FROM {post} p '
					. 'JOIN {tag_instance} ti ON ti.itemid = p.id '
					. 'WHERE p.module = "blog" AND p.userid = ? AND ti.itemtype = "post" AND ti.tagid = ?';
				$entries = $DB->get_records_sql($sql, array($student_id, $skill->id));
				if (count($entries)) {
					$skill_bloggers++;
					$student_posts[$index] += count($entries);
					$skill_posts += count($entries);
					foreach ($entries as $entry) {
						$month = date('Ym', $entry->created);
						if (in_array($month, $month_numbers)) {
							$month_stats[array_search($month, $month_numbers)]['month_posts']++;
						}
						if (!empty($CFG->useblogassociations)) {
							$associations = $DB->get_records('blog_association', array('blogid' => $entry->id));
							foreach ($associations as $association) {
								$context = context::instance_by_id($association->contextid);
								if ($context->contextlevel == CONTEXT_COURSE) {
									if (($params['course_id'] == 0) || ($context->instanceid == $params['course_id'])) {
										$skill_associations++;
										$total_associations++;
										if (in_array($month, $month_numbers)) {
											$month_stats[array_search($month, $month_numbers)]['month_associations']++;
										}
									}
								}
							}
						}
					}
				}
			}
			$skills[] = array(
				'skill_name' => $skill->rawname,
				'skill_bloggers' => $skill_bloggers,
				'skill_associations' => $skill_associations,
				'skill_posts' => $skill_posts
			);
		}
		
		$total_bloggers = 0;
		$total_posts = 0;
		foreach ($student_posts as $blog_posts) {
			if ($blog_posts) {
				$total_bloggers++;
				$total_posts += $blog_posts;
			}
		}

		$stats = array();
		$stats[] = array (
			'stat_name' => 'stat_totals',
			'stat_bloggers' => $total_bloggers,
			'stat_students' => count($student_ids),
			'stat_associations' => $total_associations,
			'stat_posts' => $total_posts
		);
		if ($params['stats_type'] == 'faculty') {
			foreach ($month_numbers as $index => $month_number) {
				$stats[] = array(
					'stat_name' => $month_number,
					'stat_bloggers' => 0,
					'stat_students' => 0,
					'stat_associations' => $month_stats[$index]['month_associations'],
					'stat_posts' => $month_stats[$index]['month_posts']
				);
			}
		} else if ($params['stats_type'] == 'skill') {
			foreach ($skills as $skill) {
				$stats[] = array(
					'stat_name' => $skill['skill_name'],
					'stat_bloggers' => $skill['skill_bloggers'],
					'stat_students' => 0,
					'stat_associations' => $skill['skill_associations'],
					'stat_posts' => $skill['skill_posts']
				);
			}
		}
		
		return $stats;
	}
}
