-- ============================================================
--  IFQM Employee Ideation Tool – Database Schema (FIXED)
-- ============================================================

CREATE DATABASE IF NOT EXISTS ifqm_ideation CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ifqm_ideation;

-- ── Users / Employees ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  employee_id     VARCHAR(20)  NOT NULL UNIQUE,
  name            VARCHAR(100) NOT NULL,
  email           VARCHAR(150) NOT NULL UNIQUE,
  password_hash   VARCHAR(255) NOT NULL,
  phone           VARCHAR(20),
  department      VARCHAR(100),
  business_unit   VARCHAR(100),
  location        VARCHAR(100),
  role            ENUM('employee','manager','admin','executive') NOT NULL DEFAULT 'employee',
  manager_id      INT NULL,
  points          INT NOT NULL DEFAULT 0,
  avatar_initials VARCHAR(4),
  created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (manager_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ── Ideas ─────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS ideas (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  idea_code          VARCHAR(20)  NOT NULL UNIQUE,
  title              VARCHAR(255) NOT NULL,
  present_situation  TEXT NOT NULL,
  proposed_solution  TEXT NOT NULL,
  impact_areas       VARCHAR(255),
  impact_level       ENUM('Low','Medium','High') DEFAULT 'Medium',
  tangible_benefit   TEXT,
  intangible_benefit TEXT,
  ai_score           INT DEFAULT 0,
  ai_reason          TEXT,
  status             ENUM('Draft','Submitted','Under Review','Approved','Rejected','Implemented') DEFAULT 'Draft',
  submitter_id       INT NOT NULL,
  co_suggester_1_id  INT NULL,
  co_suggester_2_id  INT NULL,
  points_awarded     INT DEFAULT 0,
  submitted_at       DATETIME NULL,
  created_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at         DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (submitter_id)      REFERENCES users(id),
  FOREIGN KEY (co_suggester_1_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (co_suggester_2_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ── Idea Attachments ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS idea_attachments (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  idea_id     INT NOT NULL,
  section     ENUM('situation','solution') NOT NULL,
  filename    VARCHAR(255) NOT NULL,
  filepath    VARCHAR(500) NOT NULL,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idea_id) REFERENCES ideas(id) ON DELETE CASCADE
);

-- ── Approval Workflow ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS idea_workflow (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  idea_id    INT NOT NULL,
  actor_id   INT NOT NULL,
  action     ENUM('Submitted','Reviewed','Approved','Rejected','Implemented','Commented','Reopened') NOT NULL,
  comment    TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idea_id)  REFERENCES ideas(id) ON DELETE CASCADE,
  FOREIGN KEY (actor_id) REFERENCES users(id)
);

-- ── Notifications ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  title      VARCHAR(255) NOT NULL,
  message    TEXT,
  is_read    TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ── Leaderboard View ──────────────────────────────────────────
CREATE OR REPLACE VIEW leaderboard AS
SELECT
  u.id, u.name, u.department, u.business_unit, u.points,
  COUNT(DISTINCT i.id)                                          AS idea_count,
  SUM(CASE WHEN i.status = 'Implemented' THEN 1 ELSE 0 END)    AS implemented_count,
  ROUND(AVG(CASE WHEN i.status != 'Draft' THEN i.ai_score ELSE NULL END), 1) AS avg_score
FROM users u
LEFT JOIN ideas i ON i.submitter_id = u.id AND i.status != 'Draft'
GROUP BY u.id
ORDER BY u.points DESC;

-- ============================================================
-- SEED DATA
-- Step 1: Insert ALL users with manager_id = NULL first
--         (avoids foreign key constraint error)
-- All passwords = "password"
-- ============================================================

INSERT INTO users
  (employee_id, name, email, password_hash, phone, department, business_unit, location, role, manager_id, points, avatar_initials)
VALUES
  ('EMP-003', 'Bhuvan K H',       'bhuvan.kh@company.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', 'Administration', 'BU-South', 'Bengaluru', 'admin',    NULL, 500, 'BK'),
  ('EMP-002', 'Priya Sharma',     'priya.sharma@company.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543211', 'Production',     'BU-South', 'Bengaluru', 'manager',  NULL, 320, 'PS'),
  ('EMP-001', 'Yashas R',         'yashas.r@company.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'Production',     'BU-South', 'Bengaluru', 'employee', NULL, 145, 'YR'),
  ('EMP-004', 'Adrish Chowdhury', 'adrish.c@company.com',        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543213', 'Strategy',       'BU-East',  'Kolkata',   'executive',NULL, 750, 'AC'),
  ('EMP-005', 'Rahul Mehta',      'rahul.mehta@company.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543214', 'Quality',        'BU-South', 'Bengaluru', 'employee', NULL, 210, 'RM'),
  ('EMP-006', 'Arjun Chopra',     'arjun.chopra@company.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543215', 'Safety',         'BU-North', 'Delhi',     'employee', NULL,  80, 'AC');

-- ============================================================
-- Step 2: Set manager relationships AFTER all rows exist
-- ============================================================

-- Priya (EMP-002) reports to Bhuvan (EMP-003)
UPDATE users
SET manager_id = (SELECT id FROM (SELECT id FROM users WHERE employee_id = 'EMP-003') AS tmp)
WHERE employee_id = 'EMP-002';

-- Yashas, Rahul, Arjun report to Priya (EMP-002)
UPDATE users
SET manager_id = (SELECT id FROM (SELECT id FROM users WHERE employee_id = 'EMP-002') AS tmp)
WHERE employee_id IN ('EMP-001', 'EMP-005', 'EMP-006');

-- ============================================================
-- Step 3: Sample idea
-- submitter_id uses a subquery so it works regardless of
-- which auto-increment ID Yashas was assigned
-- ============================================================

INSERT INTO ideas
  (idea_code, title, present_situation, proposed_solution, impact_areas, impact_level, ai_score, status, submitter_id, co_suggester_1_id, points_awarded, submitted_at)
VALUES (
  'IDA-2025-001',
  'Reduce Rework in Production Line',
  'Current rework rate is approximately 8% on Line 3 due to inconsistent incoming material quality checks.',
  'Introduce a mandatory QC gate at the start of Line 3 with a digital checklist logged in the system.',
  'Quality,Cost',
  'Medium',
  88,
  'Approved',
  (SELECT id FROM (SELECT id FROM users WHERE employee_id = 'EMP-001') AS u1),
  (SELECT id FROM (SELECT id FROM users WHERE employee_id = 'EMP-005') AS u2),
  35,
  NOW() - INTERVAL 30 DAY
);

-- ── Migration: run this once on existing databases ────────────────
-- ALTER TABLE ideas ADD COLUMN IF NOT EXISTS ai_reason TEXT AFTER ai_score;
