-- ============================================================
--  IFQM Schema Updates — Features 1-15
--  Run against each tenant database.
--  All statements are idempotent (IF NOT EXISTS / IGNORE).
-- ============================================================

-- ── Feature 9: Anonymous submissions ─────────────────────────
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS is_anonymous TINYINT(1) NOT NULL DEFAULT 0;

-- ── Features 1 & 2: Escalation + SLA ─────────────────────────
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS escalation_level     INT NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS current_reviewer_id  INT NULL,
  ADD COLUMN IF NOT EXISTS review_due_date      DATE NULL;

-- ── Feature 3: Implementation tracking ───────────────────────
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS implementation_owner_id    INT NULL,
  ADD COLUMN IF NOT EXISTS implementation_target_date DATE NULL,
  ADD COLUMN IF NOT EXISTS implementation_status      ENUM('not_started','in_progress','completed','on_hold') NULL;

-- ── Feature 10: ROI tracking ──────────────────────────────────
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS roi_value       DECIMAL(15,2) NULL,
  ADD COLUMN IF NOT EXISTS roi_type        ENUM('cost_saving','time_saving','quality_improvement','revenue_increase','other') NULL,
  ADD COLUMN IF NOT EXISTS roi_description TEXT NULL;

-- ── Feature 6: Challenge link ─────────────────────────────────
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS challenge_id INT NULL;

-- ── Feature 8: Template type ──────────────────────────────────
ALTER TABLE ideas
  ADD COLUMN IF NOT EXISTS template_type VARCHAR(50) NULL;

-- ── Feature 5: Discussion threads ────────────────────────────
CREATE TABLE IF NOT EXISTS idea_comments (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  idea_id    INT NOT NULL,
  user_id    INT NOT NULL,
  parent_id  INT NULL,
  content    TEXT NOT NULL,
  is_deleted TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (idea_id)   REFERENCES ideas(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id)   REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES idea_comments(id) ON DELETE SET NULL
);

-- ── Feature 6: Innovation challenges ─────────────────────────
CREATE TABLE IF NOT EXISTS challenges (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(255) NOT NULL,
  description TEXT,
  created_by  INT NOT NULL,
  deadline    DATE NULL,
  status      ENUM('active','closed','draft') NOT NULL DEFAULT 'active',
  created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- (challenge_id is a plain INT; integrity enforced at application level)

-- ── Feature 13: Org-level settings ───────────────────────────
CREATE TABLE IF NOT EXISTS org_settings (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  key_name   VARCHAR(100) NOT NULL UNIQUE,
  value      TEXT,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO org_settings (key_name, value) VALUES
  ('review_sla_days',       '7'),
  ('escalation_days',       '14'),
  ('anonymous_allowed',     '1'),
  ('public_board_enabled',  '1'),
  ('challenges_enabled',    '1'),
  ('email_enabled',         '0'),
  ('smtp_host',             ''),
  ('smtp_port',             '587'),
  ('smtp_user',             ''),
  ('smtp_pass',             ''),
  ('smtp_from',             ''),
  ('smtp_from_name',        'IFQM Ideation');

-- ── Feature 4: Email queue ────────────────────────────────────
CREATE TABLE IF NOT EXISTS email_queue (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  to_email   VARCHAR(150) NOT NULL,
  to_name    VARCHAR(100),
  subject    VARCHAR(255) NOT NULL,
  body       TEXT NOT NULL,
  status     ENUM('pending','sent','failed') NOT NULL DEFAULT 'pending',
  attempts   INT NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  sent_at    DATETIME NULL
);
