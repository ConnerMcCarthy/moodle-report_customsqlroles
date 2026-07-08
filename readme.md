# Ad-hoc database queries (role-restricted)

This report plugin is a role-restricted variant of the standard ad-hoc database queries report.
It allows Administrators to set up arbitrary database queries as on-demand or scheduled reports,
while also optionally restricting each query to selected system roles.

Users with the right capability can go to Administration -> Reports -> Ad-hoc database queries (role-restricted)
and see only the queries they are allowed to access. Results can be viewed on-screen or downloaded as CSV.

Reports can contain placeholders, in which case the user running the report is presented with a form where
values can be entered before running the query.

Scheduled reports can also be emailed automatically when they are generated.

## Installation

Place this plugin in:

    report/customsqlroles

Then complete the Moodle upgrade from:

    Site administration > Notifications

## Notes

- This is a separate plugin component from `report_customsql`.
- It is intended to be installed side by side with the standard plugin.
- Query access can still use the normal capability gate, with an additional optional system-role allowlist.
