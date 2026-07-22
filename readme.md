# Ad-hoc database queries (role-restricted)

This report plugin is a role-restricted variant of the standard ad-hoc database queries report.
It allows Administrators to set up arbitrary database queries as on-demand or scheduled reports,
while also optionally restricting each query to selected system roles.

Users with the right capability can go to Administration -> Reports -> Ad-hoc database queries (role-restricted)
and see only the queries they are allowed to access. Results can be viewed on-screen or downloaded as CSV.

Reports can contain placeholders, in which case the user running the report is presented with a form where
values can be entered before running the query.

Scheduled reports can also be emailed automatically when they are generated.

Original plugin repository:

- https://github.com/moodleou/moodle-report_customsql

## Compatibility

- Minimum Moodle version: 3.9 (`2020061500`).
- Expected to work with Moodle 3.9, 3.10, 3.11, 4.0, and 4.1.
- Tested on Moodle 4.1.12 (Build: 20240812).
- Moodle 4.2 and later, including Moodle 5.x, have not been verified with this release.
- PHP and database requirements follow the requirements of the Moodle version being used.

## Installation

Place this plugin in:

    report/customsqlroles

Then complete the Moodle upgrade from:

    Site administration > Notifications

## Notes

- This is a separate plugin component from `report_customsql`.
- It is intended to be installed side by side with the standard plugin.
- Query access can still use the normal capability gate, with an additional optional system-role allowlist.
