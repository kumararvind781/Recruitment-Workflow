Recruitment Workflow System - PHP + MySQL

Setup:
1. Create database by importing database/schema.sql
2. Import database/seed.sql
3. Update DB credentials in app/config/config.php
4. Place project in htdocs or server root
5. Open public/login.php

Demo login:
Recruiter: recruiter@example.com
Manager: manager@example.com
Password for both seed users: password

Main pages:
- public/apply.php
- public/login.php
- public/recruiter-dashboard.php
- public/recruiter-candidates.php
- public/recruiter-candidate.php?id=1
- public/manager-dashboard.php
- public/manager-review.php?round_id=1

Workflow:
Candidate submits form -> Recruiter reviews -> Recruiter rejects/direct selects/or sends to manager -> Manager adds remark -> Candidate returns to recruiter -> Recruiter takes final action or sends next round.

Updated: admin is super user, new shared dashboard, user management, rejection reason required.

V2 updates: light UNIRE theme, reports export, passport photo upload, position dropdown, reference fields, removed share link block, monthly password expiry, admin reset/delete user.
