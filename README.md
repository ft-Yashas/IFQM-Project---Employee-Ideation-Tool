# IFQM Employee Ideation Tool — Setup Guide
## Full-stack: HTML + CSS + PHP + MySQL (XAMPP)

---

## 📁 Project Structure

```
ifqm/
├── index.php               ← Main app (login + all pages)
├── database.sql            ← Run once to create DB + seed data
├── api/
│   ├── config.php          ← DB connection, helpers, constants
│   ├── auth.php            ← Login / Logout / Session
│   ├── ideas.php           ← Idea CRUD, submit, review, dashboard
│   ├── users.php           ← Users, leaderboard, analytics, audit, notifications
│   └── upload.php          ← File attachment upload/delete
├── uploads/                ← Auto-created on first upload (gitignore this)
└── README.md               ← This file
```

---

## 🚀 Setup Steps (XAMPP)

### Step 1 — Copy project
Place the entire `ifqm/` folder inside:
```
C:\xampp\htdocs\ifqm\
```

### Step 2 — Start XAMPP
Open XAMPP Control Panel and start:
- ✅ **Apache**
- ✅ **MySQL**

### Step 3 — Create the database
1. Open your browser → `http://localhost/phpmyadmin`
2. Click **Import** (top tab)
3. Click **Choose File** → select `ifqm/database.sql`
4. Click **Go** (scroll down)

This creates the `ifqm_ideation` database with all tables and demo users.

### Step 4 — Open the app
Navigate to: **`http://localhost/ifqm/`**

---

## 🔑 Demo Login Credentials
All accounts use password: **`password`**

| Email | Role |
|---|---|
| yashas.r@company.com | Employee |
| priya.sharma@company.com | Manager |
| bhuvan.kh@company.com | Admin |
| adrish.c@company.com | Executive |

---

## 🗄️ Database Tables

| Table | Purpose |
|---|---|
| `users` | Employees with roles, points, manager hierarchy |
| `ideas` | All submitted ideas with status tracking |
| `idea_attachments` | File uploads per idea |
| `idea_workflow` | Append-only audit trail of all actions |
| `notifications` | In-app notifications per user |
| `leaderboard` (VIEW) | Pre-computed rankings |

---

## 🔌 API Endpoints

### Auth
- `POST api/auth.php?action=login` — `{email, password}` → session
- `POST api/auth.php?action=logout`
- `GET  api/auth.php?action=me` — returns current session user

### Ideas
- `GET  api/ideas.php?action=my` — logged-in user's ideas
- `GET  api/ideas.php?action=list` — all ideas (role-filtered)
- `GET  api/ideas.php?action=review` — ideas pending review (manager+)
- `GET  api/ideas.php?action=get&id=X` — single idea with workflow + attachments
- `GET  api/ideas.php?action=dashboard` — KPI counts + recent activity
- `POST api/ideas.php?action=submit` — submit idea `{title, present_situation, proposed_solution, impact_areas, impact_level, ...}`
- `POST api/ideas.php?action=draft` — save draft
- `POST api/ideas.php?action=review_action` — approve/reject/implement `{idea_id, decision, comment}`

### Users / Analytics
- `GET  api/users.php?action=list&q=search` — user search (for co-suggesters)
- `GET  api/users.php?action=leaderboard&period=all|monthly|quarterly|yearly`
- `GET  api/users.php?action=notifications`
- `POST api/users.php?action=mark_read`
- `GET  api/users.php?action=analytics` — impact distribution, trends, status summary
- `GET  api/users.php?action=audit` — full audit trail

### Uploads
- `POST api/upload.php?action=upload` — multipart form with `file`, `idea_id`, `section`
- `POST api/upload.php?action=delete` — `{attachment_id}`

---

## 🎮 Points System

| Event | Points |
|---|---|
| Idea Submitted | +10 |
| Idea Approved | +25 |
| Idea Implemented | +65 |

Configurable in `api/config.php` → `POINTS_SUBMIT`, `POINTS_APPROVED`, `POINTS_IMPLEMENTED`.

---

## 🔐 Role-Based Access

| Feature | Employee | Manager | Admin | Executive |
|---|---|---|---|---|
| Submit ideas | ✅ | ✅ | ✅ | ✅ |
| View own ideas | ✅ | ✅ | ✅ | ✅ |
| Review queue | ❌ | ✅ | ✅ | ✅ |
| Approve/Reject | ❌ | ✅ | ✅ | ✅ |
| All ideas view | own | dept | all | all |
| Analytics | ❌ | ✅ | ✅ | ✅ |
| Admin panel | ❌ | ❌ | ✅ | ❌ |
| Audit trail | ❌ | ✅ | ✅ | ✅ |

---

## 📂 File Uploads
- Stored in `ifqm/uploads/` (auto-created)
- Allowed: PDF, PNG, JPG, JPEG, XLSX, CSV, DOCX
- Max size: 10 MB per file
- Accessed via: `http://localhost/ifqm/uploads/{filename}`
