# IFQM Employee Ideation Tool

A full-stack internal platform that lets employees submit improvement ideas, get them AI-scored, route them through approval workflows, and track everything on a live leaderboard. Built as a single PHP/MySQL app with no framework dependencies — just drop it into XAMPP and it runs.

---

## What it does

Every organisation has people with great ideas and no clear way to surface them. This tool fixes that. Employees log in, describe a problem they see and what they'd do about it, and the system scores the idea automatically (via GPT-4o-mini or a built-in heuristic fallback), routes it to their manager or a custom committee, and awards points when ideas get approved or implemented.

The whole thing lives in one `index.php` with a small `api/` folder behind it — no build step, no Node, no Composer.

---

## Features at a glance

**For employees**
- Submit ideas with a structured 4-step wizard (situation → solution → impact → review)
- Attach supporting files (images, PDFs, up to 10 MB each)
- Tag up to 2 co-suggesters who share the credit
- See your idea scored in real-time with a breakdown across 6 dimensions
- Community star rating (1–5 ★) on any idea that isn't yours
- In-app notifications when your idea moves through the workflow
- Personal profile with points total and idea history

**For managers / admins**
- Review queue with one-click Approve / Reject + comment
- Route any idea to a named committee (multi-reviewer workflow) with a custom approval threshold (e.g. "needs 2 of 3 reviewers")
- Audit log — immutable record of every action ever taken on every idea
- Analytics dashboard: submission trends, status breakdown, impact area heatmap, quality distribution
- Batch re-score all ideas

**For everyone**
- Leaderboard — individual and department rankings by points, idea count, and average score
- Top Ideas board ranked by AI score
- Light / dark mode
- English / Hindi bilingual UI (`data-i18n` driven, live toggle)
- Collapsible sidebar with tooltips in collapsed mode

---

## Tech stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.1+ (vanilla, no framework) |
| Database | MySQL 8.0 / MariaDB 10.6+ |
| Frontend | Vanilla JS SPA — no React, no Vue, no build tool |
| Styling | Custom CSS with Inter font, CSS custom properties, dark mode |
| AI scoring | OpenAI GPT-4o-mini (optional — heuristic fallback built in) |
| Auth | PHP sessions with bcrypt password hashing |
| Server | Apache via XAMPP (or any PHP-capable web server) |

---

## Scoring engine

Ideas are scored 0–100 across six dimensions. If you have an OpenAI API key, GPT-4o-mini evaluates the idea and returns a score + explanation. If you don't (or the API is down), the built-in heuristic engine runs automatically — no configuration needed.

| Dimension | Max | What it looks at |
|---|---|---|
| Problem Clarity | 20 | Sentence structure, lexical diversity, quantitative evidence, causal language |
| Solution Quality | 20 | Actionable steps, mechanism specificity, vocabulary richness |
| Feasibility | 15 | Resource awareness, realistic scope, depth-vs-claim match |
| Business Impact | 20 | Impact level declared, number of areas touched, tangible benefit |
| Measurability | 10 | Numbers in baseline, from→to targets, benchmark language |
| Innovation | 15 | Technology angle, new process design, cross-functional reach |

The heuristic model penalises generic phrases like "improve efficiency" or "make it better" — ideas need to be specific to score well.

---

## Approval workflows

Two modes, choosable per idea:

**Hierarchical** (default)
Idea goes to the submitter's direct manager. Manager approves or rejects. Simple, fast.

**Multi-reviewer / Committee**
An admin routes the idea to a named group of reviewers and sets an approval threshold (e.g. 60%). Each reviewer votes independently. When all votes are in, the system automatically finalises the status. A threshold of 100% means any single rejection blocks the idea (unanimous required).

---

## Multi-tenancy

The app can run as a single-org install (the default) or serve multiple organisations from one codebase. When multi-tenant mode is active, an `ifqm_master` database maps HTTP hostnames to per-tenant databases and isolated upload directories.

If `ifqm_master` does not exist, the app falls back gracefully to the default `ifqm_ideation` database — existing installs just keep working.

To provision a new tenant:

```bash
php provision_tenant.php \
  --name="Acme Corp" \
  --slug="acme" \
  --domain="acme.yourhost.com" \
  --admin-email="admin@acme.com" \
  --admin-pass="changeme"
```

This creates the database, runs the schema, creates a super_admin account, registers the tenant, and creates the upload folder.

---

## Installation (single tenant, XAMPP)

1. Clone or copy this folder into `C:\xampp\htdocs\ifqm\`

2. Import the database:
```sql
-- In phpMyAdmin or MySQL CLI:
SOURCE /path/to/ifqm/database.sql;
```

3. Set your OpenAI key (optional — app works without it):
```
# Windows: add as a system environment variable
OPENAI_API_KEY=sk-...
```
Or edit `api/config.php` and hardcode it temporarily for local dev.

4. Visit `http://localhost/ifqm/`

No `composer install`, no `npm install`, no `.env` file to create.

---

## Demo accounts

All passwords are `password`.

| Name | Email | Role |
|---|---|---|
| IFQM Super Admin | superadmin@ifqm.com | super_admin |
| Bhuvan K H | bhuvan.kh@ifqm.com | admin |
| Priya Sharma | priya.sharma@ifqm.com | manager |
| Adrish Chowdhury | adrish.c@ifqm.com | executive |
| Yashas R | yashas.r@ifqm.com | employee |
| Rahul Mehta | rahul.mehta@ifqm.com | employee |
| Arjun Chopra | arjun.chopra@ifqm.com | employee |

---

## Roles

| Role | What they can do |
|---|---|
| `employee` | Submit ideas, vote on others' ideas, view leaderboard |
| `manager` | All of the above + review ideas from direct reports, route ideas to committees |
| `admin` | All of the above + manage all users, view all ideas, access analytics |
| `executive` | Read-only analytics + can review any idea |
| `super_admin` | Full access including user hierarchy management and system stats |

---

## Project structure

```
ifqm/
├── index.php              # The entire frontend SPA + login page
├── database.sql           # Schema + seed data for fresh installs
├── schema.sql             # Clean schema template (no seed, for multi-tenant provisioning)
├── master.sql             # ifqm_master schema for multi-tenant mode
├── provision_tenant.php   # CLI script to onboard a new organisation
├── api/
│   ├── config.php         # DB connection, tenant resolution, shared helpers
│   ├── auth.php           # Login / logout / session check
│   ├── ideas.php          # All idea CRUD + workflow actions
│   ├── users.php          # User list, notifications, leaderboard, analytics, audit
│   ├── votes.php          # Community star ratings
│   ├── score.php          # AI scoring engine (OpenAI + heuristic fallback)
│   ├── upload.php         # File attachment handler
│   └── uploads/           # Per-tenant uploaded files (gitignored)
└── assets/
    └── ifqm-logo.png
```

---

## Configuration

Everything lives in `api/config.php`:

```php
// OpenAI — leave empty to use the heuristic scorer only
define('OPENAI_API_KEY', getenv('OPENAI_API_KEY'));

// Points awarded at each milestone
define('POINTS_SUBMIT',      10);
define('POINTS_APPROVED',    25);
define('POINTS_IMPLEMENTED', 65);

// Max upload size per file
define('MAX_FILE_MB', 10);

// Session lifetime in seconds (default 8 hours)
define('SESSION_LIFETIME', 28800);
```

---

## Running migrations on an existing install

If you're updating rather than doing a fresh import, run these manually:

```sql
ALTER TABLE ideas ADD COLUMN IF NOT EXISTS workflow_type
  ENUM('hierarchical','multi_reviewer') NOT NULL DEFAULT 'hierarchical';

ALTER TABLE ideas ADD COLUMN IF NOT EXISTS approval_threshold
  TINYINT NOT NULL DEFAULT 100;

CREATE TABLE IF NOT EXISTS idea_reviewers (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  idea_id      INT NOT NULL,
  reviewer_id  INT NOT NULL,
  decision     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  comment      TEXT,
  decided_at   DATETIME NULL,
  assigned_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_reviewer (idea_id, reviewer_id),
  FOREIGN KEY (idea_id)    REFERENCES ideas(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE
);
```

These are also included at the bottom of `database.sql` as `ALTER TABLE ... IF NOT EXISTS` statements, so re-running the whole file is safe.

---

## Security notes

- Passwords are hashed with bcrypt (`password_hash` / `password_verify`)
- All DB queries use PDO prepared statements — no string interpolation anywhere
- Role checks happen server-side on every API request — the frontend role gates are just UX
- Uploaded files are validated by MIME type before saving
- The audit log table is append-only by design; no row is ever updated or deleted

---

## Known limitations

- No email notifications yet — everything is in-app only
- No OAuth / SSO — email + password only
- Bilingual support is English and Hindi; adding more languages means extending the `TRANSLATIONS` object in `index.php`
- No rate limiting on the scoring endpoint
- File uploads go to disk — swap `upload.php` for S3/R2 if you need horizontal scaling

---

## License

MIT. Do whatever you want with it — just don't ship the demo passwords to production.