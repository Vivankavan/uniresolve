# UniResolve verification checklist
Tick each box after testing on your live site (or localhost).

- [ ] Home page loads, looks right on a phone-sized window (menu button appears)
- [ ] Register a new student with a weak password: error shown
- [ ] Register correctly, then sign in
- [ ] Report an issue: you get a reference like UR-2026-000005
- [ ] The issue shows category, urgency, status Open, department, date
- [ ] Add a comment as the student
- [ ] Sign out, sign in as staff: you see ALL issues
- [ ] Post an update + change status to In Progress; history shows the change
- [ ] Add an internal note; confirm the student cannot see it
- [ ] Sign in as admin: assign an issue to a department and staff member
- [ ] Admin > Users: create a staff account
- [ ] Dashboard shows counts and bars; student sees only their own numbers
- [ ] Student cannot open another student's issue or `admin_users.php` (404 / 403)
- [ ] `/.env` and `/includes/bootstrap.php` in the browser show "Forbidden"
- [ ] `seed.php` has been deleted from the server
