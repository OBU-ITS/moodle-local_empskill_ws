moodle-local_empskill_ws
========================

A Moodle plugin that provides a web service to enable the posting and retrieval of 'employability skill' blog entries and to report statistics.

The web service provides functions to:
  - Get the database tag id of a given tag name
  - Get the tag name for a given database tag id
  - Get a list of faculties for which this user has the 'Course Viewer' role
  - Get monthly averages for blog entries posted by students of a faculty
  - Get blog entry statistics for current courses within a faculty
  - Get blog entry statistics for individual employability skills within a faculty
  - Get a list of employability skills for a given skill category (tag id)
  - Get a list of a student's blog entries relating to a skill category or an individual employability skill
  - Get an individual blog entry for a given database post id
  - Get a list of current courses that the given student is enrolled on
  - Save (add or edit) the given blog entry
  - Delete an individual blog entry with the given database post id

Users must authenticate by sending a GET or POST request to moodle_base_url/login/token.php, passing the parameters username, password and service which should be set to 'empskill-ws'. A token will be returned if successfully authenticated. This token must be passed with each request to the web service.

Web service function calls should be POSTed to moodle_base_url/rest/server.php, passing the parameters moodlewsrestformat set to 'json', wstoken set to the value of the previously obtained token, wsfunction set to the name of the function to call, and any other parameters required by the function.

<h2>INSTALLATION</h2>
This plugin should be installed in the local directory of the Moodle instance.
