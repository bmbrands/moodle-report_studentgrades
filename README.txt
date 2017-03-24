My Grades report for Moodle 2.6, 2.7 and 2.8
------------------------------------------

License: GPL v3
@copyright 2015: Learning Technology Services, www.lts.ie
Lead Developer: Bas Brands

Development sponsored by DCU, http://www.dcu.ie

This is a grade report designed to report on all enrolled available courses. 

The report then displays, per course, all graded activities including the student grade, feedback and Z-Score, which shows the student grade's relative position within the course.

To test:
1- Install the plugin in the moodle/report/ folder
2- Find the My Grades report link under Administration -> My Profile settings -> Activity reports -> My Grades

Logic

Visible to teachers and admins

When a teachers views the report it looks up all students in the teacher courses.

1. get my courses
2. lookup current course category (1 level up)
3. filter my courses on current course category.
4. find students enrolled in these courses.
5. get student grades for these coures.
6. display students, grades per course, badges.