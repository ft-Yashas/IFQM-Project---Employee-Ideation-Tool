<?php
session_start();
$loggedIn = !empty($_SESSION['user_id']);
$user     = $_SESSION['user'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<script>
  // Apply saved theme before paint to prevent flash
  (function(){var t=localStorage.getItem('ifqm-theme');if(t)document.documentElement.setAttribute('data-theme',t)})();
</script>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Employee Ideation Tool – IFQM</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root{
    --primary:#4f46e5;--primary-dk:#3730a3;--primary-lt:#eef2ff;
    --success:#059669;--danger:#dc2626;--warning:#d97706;--info:#2563eb;
    --shadow-sm:0 1px 2px rgba(0,0,0,.05),0 1px 3px rgba(0,0,0,.08);
    --shadow-md:0 4px 6px -1px rgba(0,0,0,.07),0 2px 4px -1px rgba(0,0,0,.05);
    --shadow-lg:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -2px rgba(0,0,0,.05);
    --shadow-xl:0 20px 25px -5px rgba(0,0,0,.12),0 10px 10px -5px rgba(0,0,0,.05);
    --r:8px;--r-lg:12px;--r-xl:16px;
    --ease:cubic-bezier(.4,0,.2,1);
    --bg:#f8fafc;--surface:#fff;--border:#e2e8f0;--text:#0f172a;--text-muted:#64748b;
    --topbar-bg:rgba(255,255,255,.85);--topbar-border:#e2e8f0;--input-bg:#fff;--input-border:#cbd5e1;
    --table-header:#f8fafc;--table-row-hover:#f1f5f9;--tag-bg:#eef2ff;
    --heading:#0f172a;--subtext:#475569;--label:#64748b;--subtle:#94a3b8;--chip-bg:#fff;
    --panel-bg:#f8fafc;--bar-track:#f1f5f9;--progress-bg:#e2e8f0;--separator:#f1f5f9;
    --sidebar-bg:#0f172a;--sidebar-border:rgba(255,255,255,.06);
    --sidebar-text:rgba(255,255,255,.72);--sidebar-active-bg:rgba(79,70,229,.18);
    --sidebar-active-text:#a5b4fc;--sidebar-hover-bg:rgba(255,255,255,.06);
    --sidebar-section-text:rgba(148,163,184,.55);
  }
  [data-theme=dark]{
    --bg:#0a0d14;--surface:#111827;--border:#1e293b;--text:#e2e8f0;--text-muted:#64748b;
    --topbar-bg:rgba(17,24,39,.9);--topbar-border:#1e293b;--input-bg:#1e293b;--input-border:#334155;
    --table-header:#111827;--table-row-hover:#1e293b;--tag-bg:#1e1b4b;
    --heading:#e0e7ff;--subtext:#94a3b8;--label:#64748b;--subtle:#475569;--chip-bg:#1e293b;
    --panel-bg:#111827;--bar-track:#1e293b;--progress-bg:#1e293b;--separator:#1e293b;
    --shadow-sm:0 1px 3px rgba(0,0,0,.35),0 1px 2px rgba(0,0,0,.25);
    --shadow-md:0 4px 14px rgba(0,0,0,.45),0 2px 4px rgba(0,0,0,.3);
    --shadow-lg:0 10px 25px rgba(0,0,0,.55),0 4px 8px rgba(0,0,0,.3);
    --sidebar-bg:#0a0d14;
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',system-ui,-apple-system,'Segoe UI',Arial,sans-serif;background:var(--bg);color:var(--text);font-size:14px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;transition:background .3s,color .3s}
  .card,.kpi-card,.idea-card,.modal,.topbar,.nav-item,.login-card,.form-control,.btn-outline{transition-property:background,border-color,box-shadow,color,transform;transition-duration:.2s,.2s,.2s,.2s,.15s;transition-timing-function:var(--ease)}
  #app{display:flex;height:100vh;overflow:hidden}

  /* ── SIDEBAR ── */
  #sidebar{width:224px;background:var(--sidebar-bg);color:#fff;display:flex;flex-direction:column;flex-shrink:0;transition:width .22s var(--ease);border-right:1px solid var(--sidebar-border)}
  #sidebar.collapsed{width:60px}
  #main{flex:1;display:flex;flex-direction:column;overflow:hidden}
  #topbar{background:var(--topbar-bg);padding:10px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--topbar-border);flex-shrink:0;backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px)}
  #content{flex:1;overflow-y:auto;padding:24px 28px}
  .sidebar-logo{padding:18px 16px;display:flex;align-items:center;gap:10px;white-space:nowrap;overflow:hidden;border-bottom:1px solid var(--sidebar-border)}
  .sidebar-logo span{font-size:15px;font-weight:700;color:#fff;letter-spacing:-.3px;transition:opacity .2s}
  #sidebar.collapsed .sidebar-logo span{opacity:0;width:0;overflow:hidden}
  .nav-section{padding:16px 0 4px;font-size:9.5px;text-transform:uppercase;letter-spacing:1.4px;color:var(--sidebar-section-text);padding-left:16px;white-space:nowrap;overflow:hidden;font-weight:600}
  #sidebar.collapsed .nav-section{opacity:0}
  .nav-item{display:flex;align-items:center;gap:10px;padding:8px 10px;margin:2px 8px;cursor:pointer;white-space:nowrap;overflow:hidden;border-radius:var(--r);border:1px solid transparent;color:var(--sidebar-text);transition:background .15s var(--ease),color .15s}
  .nav-item:hover{background:var(--sidebar-hover-bg);color:rgba(255,255,255,.9)}
  .nav-item.active{background:var(--sidebar-active-bg);color:var(--sidebar-active-text);border-color:rgba(99,102,241,.25)}
  .nav-item .icon{flex-shrink:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:7px;transition:background .15s}
  .nav-item.active .icon{background:rgba(79,70,229,.3)}
  .nav-item .icon svg{width:15px;height:15px;fill:none;stroke:currentColor;stroke-width:1.75;stroke-linecap:round;stroke-linejoin:round}
  .nav-item span.label{font-size:13px;font-weight:500;transition:opacity .2s}
  #sidebar.collapsed .nav-item span.label{opacity:0;width:0;overflow:hidden}
  #sidebar.collapsed .nav-item{margin:2px 6px}

  /* ── TOPBAR ── */
  .topbar-left{display:flex;align-items:center;gap:12px}
  .page-title{font-size:15px;font-weight:600;color:var(--heading);letter-spacing:-.2px}
  .topbar-right{display:flex;align-items:center;gap:8px}
  .notif-bell{position:relative;cursor:pointer;font-size:13px;font-weight:600;color:var(--text-muted);padding:6px 10px;border:1px solid var(--input-border);border-radius:var(--r);background:var(--surface);transition:all .15s var(--ease)}
  .notif-bell:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-lt)}
  .notif-badge{position:absolute;top:-5px;right:-5px;background:#ef4444;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;border:2px solid var(--surface);font-weight:700;animation:badge-pop .3s var(--ease),pulse-ring 2s ease-in-out 1s infinite}
  .user-chip{display:flex;align-items:center;gap:8px;background:var(--bg);padding:5px 12px 5px 5px;border-radius:20px;cursor:pointer;transition:all .15s var(--ease);border:1px solid var(--border)}
  .user-chip:hover{background:var(--surface);border-color:var(--input-border);box-shadow:var(--shadow-sm)}
  .avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#4f46e5,#818cf8);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;box-shadow:0 0 0 2px rgba(79,70,229,.2)}
  .role-badge{font-size:10px;background:#4f46e5;color:#fff;padding:2px 8px;border-radius:10px;font-weight:600;letter-spacing:.2px}

  /* ── CARDS ── */
  .card{background:var(--surface);border-radius:var(--r-lg);padding:20px;box-shadow:var(--shadow-sm);margin-bottom:16px;border:1px solid var(--border);transition:box-shadow .2s var(--ease),border-color .2s}
  .card:hover{box-shadow:var(--shadow-md);border-color:var(--input-border)}
  .card-title{font-size:13px;font-weight:700;color:var(--subtext);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;text-transform:uppercase;letter-spacing:.5px}
  .card-title::before{content:'';display:inline-block;width:3px;height:13px;background:linear-gradient(180deg,#4f46e5,#818cf8);border-radius:2px;flex-shrink:0}

  /* ── KPI ── */
  .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px}
  .kpi-card{background:var(--surface);border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--shadow-sm);border:1px solid var(--border);transition:transform .18s var(--ease),box-shadow .18s var(--ease),border-color .18s}
  .kpi-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md);border-color:var(--input-border)}
  .kpi-val{font-size:28px;font-weight:800;color:var(--heading);line-height:1.1;letter-spacing:-.5px}
  .kpi-label{font-size:12px;color:var(--subtle);margin-top:5px;font-weight:500;letter-spacing:-.1px}
  .kpi-delta{font-size:11px;color:#059669;margin-top:5px;font-weight:600}

  /* ── TABLES ── */
  table{width:100%;border-collapse:collapse}
  th{background:var(--table-header);color:var(--label);font-size:11px;padding:10px 14px;text-align:left;font-weight:600;border-bottom:1px solid var(--border);text-transform:uppercase;letter-spacing:.6px}
  td{padding:10px 14px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle;color:var(--text)}
  tr:hover td{background:var(--table-row-hover)}

  /* ── BADGES ── */
  .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.15px;white-space:nowrap}
  .badge-submitted{background:#dbeafe;color:#1d4ed8}
  .badge-review{background:#fef3c7;color:#b45309}
  .badge-approved{background:#dcfce7;color:#15803d}
  .badge-rejected{background:#fee2e2;color:#b91c1c}
  .badge-implemented{background:#f3e8ff;color:#7e22ce}
  .badge-draft{background:var(--bar-track);color:var(--text-muted)}
  .badge-low{background:#dcfce7;color:#15803d}
  .badge-medium{background:#fef3c7;color:#b45309}
  .badge-high{background:#fee2e2;color:#b91c1c}

  /* ── BUTTONS ── */
  .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--r);border:none;cursor:pointer;font-size:13px;font-weight:600;transition:transform .15s var(--ease),box-shadow .15s var(--ease),background .15s;letter-spacing:.1px;font-family:inherit}
  .btn:hover{transform:translateY(-1px)}
  .btn:active{transform:translateY(0)}
  .btn-primary{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;box-shadow:0 1px 3px rgba(79,70,229,.35),0 4px 10px rgba(79,70,229,.2)}
  .btn-primary:hover{box-shadow:0 4px 14px rgba(79,70,229,.45);background:linear-gradient(135deg,#4338ca,#4f46e5)}
  .btn-success{background:linear-gradient(135deg,#059669,#10b981);color:#fff;box-shadow:0 1px 3px rgba(5,150,105,.3)}
  .btn-success:hover{box-shadow:0 4px 14px rgba(5,150,105,.4)}
  .btn-danger{background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;box-shadow:0 1px 3px rgba(220,38,38,.3)}
  .btn-outline{background:var(--surface);border:1px solid var(--border);color:var(--subtext);box-shadow:var(--shadow-sm)}
  .btn-outline:hover{background:var(--bg);border-color:var(--input-border);color:var(--text)}
  .btn-sm{padding:5px 12px;font-size:12px}
  .btn-warning{background:linear-gradient(135deg,#d97706,#f59e0b);color:#fff;box-shadow:0 1px 3px rgba(217,119,6,.3)}

  /* ── FORMS ── */
  .form-group{margin-bottom:16px}
  .form-group label{display:block;font-size:11.5px;font-weight:600;color:var(--label);margin-bottom:5px;letter-spacing:.1px}
  .form-control{width:100%;padding:9px 12px;border:1px solid var(--input-border);border-radius:var(--r);font-size:13px;color:var(--text);outline:none;transition:border-color .15s,box-shadow .15s;background:var(--input-bg);font-family:inherit}
  .form-control:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.12)}
  select.form-control{background:var(--input-bg);color:var(--text)}
  textarea.form-control{resize:vertical;min-height:80px}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}

  /* ── WIZARD ── */
  .wizard-steps{display:flex;gap:0;margin-bottom:24px}
  .w-step{flex:1;text-align:center;padding:10px 4px;font-size:12px;border-bottom:3px solid var(--input-border);color:var(--subtle);cursor:pointer;font-weight:500;transition:color .15s,border-color .15s}
  .w-step.active{border-bottom-color:var(--primary);color:var(--primary);font-weight:700}
  .w-step.done{border-bottom-color:#059669;color:#059669}
  .wizard-body{min-height:280px}
  .wizard-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)}

  /* ── TIMELINE ── */
  .timeline{padding:10px 0}
  .tl-item{display:flex;gap:14px;margin-bottom:18px}
  .tl-dot{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;margin-top:2px}
  .tl-dot-blue{background:#dbeafe;color:#1d4ed8}
  .tl-dot-green{background:#dcfce7;color:#15803d}
  .tl-dot-red{background:#fee2e2;color:#b91c1c}
  .tl-dot-orange{background:#fef3c7;color:#b45309}
  .tl-dot-purple{background:#f3e8ff;color:#7e22ce}
  .tl-title{font-size:13px;font-weight:600;color:var(--text)}
  .tl-meta{font-size:11px;color:var(--subtle);margin-top:3px}
  .tl-comment{font-size:12px;color:var(--subtext);margin-top:6px;background:var(--panel-bg);padding:8px 12px;border-radius:var(--r);border-left:3px solid var(--border)}

  /* ── BAR CHARTS ── */
  .bar-chart{display:flex;flex-direction:column;gap:10px}
  .bar-row{display:flex;align-items:center;gap:10px}
  .bar-label{width:100px;font-size:12px;color:var(--label);text-align:right;flex-shrink:0;font-weight:500}
  .bar-track{flex:1;height:8px;background:var(--bar-track);border-radius:10px;overflow:hidden}
  .bar-fill{height:100%;border-radius:10px;transition:width .6s var(--ease)}
  .bar-val{width:36px;font-size:12px;color:var(--subtext);flex-shrink:0;font-weight:600}

  /* ── LEADERBOARD ── */
  .lb-row{display:flex;align-items:center;gap:12px;padding:11px 6px;border-bottom:1px solid var(--border);transition:background .12s;border-radius:var(--r)}
  .lb-row:hover{background:var(--bg)}
  .lb-rank{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0}
  .rank-1{background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#78350f;box-shadow:0 2px 8px rgba(245,158,11,.35)}
  .rank-2{background:linear-gradient(135deg,#94a3b8,#cbd5e1);color:#334155;box-shadow:0 2px 5px rgba(148,163,184,.3)}
  .rank-3{background:linear-gradient(135deg,#b45309,#d97706);color:#fff;box-shadow:0 2px 5px rgba(180,83,9,.25)}
  .rank-n{background:var(--tag-bg);color:var(--primary)}
  .lb-name{flex:1;font-size:13px;font-weight:600}
  .lb-dept{font-size:11px;color:var(--subtle);margin-top:1px}
  .lb-points{font-size:16px;font-weight:800;color:var(--heading)}
  .lb-ideas{font-size:11px;color:var(--subtle)}
  .progress-bar{height:4px;background:var(--progress-bg);border-radius:2px;margin-top:5px}
  .progress-fill{height:100%;background:linear-gradient(90deg,#4f46e5,#818cf8);border-radius:2px}

  /* ── SCORE BADGES ── */
  .score-badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:8px;font-size:10px;font-weight:700}
  .score-high{background:#dcfce7;color:#15803d}
  .score-med{background:#fef3c7;color:#b45309}
  .score-low{background:#fee2e2;color:#b91c1c}
  .score-none{background:var(--bar-track);color:var(--text-muted)}

  /* ── MODALS ── */
  .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:1000;display:none}
  .modal-overlay.open{display:flex;animation:mo-in .2s var(--ease)}
  @keyframes mo-in{from{opacity:0}to{opacity:1}}
  @keyframes slide-up{from{transform:translateY(18px);opacity:0}to{transform:translateY(0);opacity:1}}
  .modal{background:var(--surface);border-radius:var(--r-xl);width:620px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-xl);animation:slide-up .22s var(--ease);border:1px solid var(--border)}
  .modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);background:var(--surface)}
  .modal-title{font-size:15px;font-weight:700;color:var(--heading)}
  .modal-close{cursor:pointer;font-size:18px;color:var(--subtle);line-height:1;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background .12s,color .12s}
  .modal-close:hover{background:var(--border);color:var(--text)}
  .modal-body{padding:22px}
  .modal-footer{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px;background:var(--bg);border-radius:0 0 var(--r-xl) var(--r-xl)}

  /* ── PAGES ── */
  .page{display:none}
  .page.active{display:block;animation:mo-in .18s var(--ease)}
  .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
  .text-muted{color:var(--subtle);font-size:12px;margin-top:2px}
  .text-center{text-align:center}
  .mt-8{margin-top:8px}
  .mb-8{margin-bottom:8px}
  .tag{display:inline-block;padding:2px 9px;background:var(--tag-bg);color:var(--primary);border-radius:10px;font-size:11px;margin:2px;font-weight:500}

  /* ── ALERTS ── */
  .alert{padding:11px 16px;border-radius:var(--r);font-size:13px;margin-bottom:14px;font-weight:500}
  .alert-info{background:#dbeafe;color:#1d4ed8;border-left:4px solid #2563eb}
  .alert-success{background:#dcfce7;color:#15803d;border-left:4px solid #059669}
  .alert-warning{background:#fef3c7;color:#b45309;border-left:4px solid #d97706}
  .alert-danger{background:#fee2e2;color:#b91c1c;border-left:4px solid #dc2626}

  /* ── AI PANEL ── */
  .ai-panel{background:linear-gradient(135deg,var(--primary-lt) 0%,var(--surface) 100%);border-radius:var(--r-lg);padding:16px;border:1px solid rgba(79,70,229,.15)}
  .ai-panel-title{font-size:11px;font-weight:700;color:var(--primary);margin-bottom:10px;text-transform:uppercase;letter-spacing:.6px}

  /* ── IMPACT CHIPS ── */
  .impact-grid{display:flex;flex-wrap:wrap;gap:8px}
  .impact-chip{padding:5px 13px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid var(--input-border);background:var(--chip-bg);color:var(--subtext);transition:all .15s var(--ease);font-weight:500}
  .impact-chip:hover{border-color:var(--primary);color:var(--primary)}
  .impact-chip.selected{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 8px rgba(79,70,229,.3)}

  /* ── UPLOAD ── */
  .upload-zone{border:2px dashed var(--input-border);border-radius:var(--r-lg);padding:24px;text-align:center;color:var(--subtle);cursor:pointer;transition:all .15s var(--ease)}
  .upload-zone:hover{border-color:var(--primary);background:var(--primary-lt);color:var(--primary)}

  /* ── NOTIFICATIONS ── */
  .notification-panel{position:absolute;top:52px;right:70px;width:320px;background:var(--surface);border-radius:var(--r-lg);box-shadow:var(--shadow-xl);z-index:200;display:none;border:1px solid var(--border);overflow:hidden}
  .notification-panel.open{display:block;animation:slide-up .2s var(--ease)}
  .notif-item{padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .12s;color:var(--text)}
  .notif-item:hover{background:var(--bg)}
  .notif-item.unread{border-left:3px solid var(--primary);background:var(--primary-lt)}
  .notif-item-title{font-size:13px;font-weight:600}
  .notif-item-meta{font-size:11px;color:var(--subtle);margin-top:2px}

  /* ── LOGIN ── */
  .login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;background:none}
  .login-card{background:#fff;border-radius:var(--r-xl);padding:40px;width:430px;box-shadow:0 20px 60px rgba(0,0,0,.28)}
  .login-logo{text-align:center;margin-bottom:28px}
  .login-logo h2{font-size:21px;color:var(--heading);margin-top:10px;font-weight:800}
  .login-logo p{font-size:12px;color:#aaa;margin-top:4px}
  .separator{height:1px;background:var(--separator);margin:16px 0}

  /* ── SIDEBAR USER ── */
  .sidebar-user{padding:13px 14px;border-top:1px solid var(--sidebar-border);display:flex;align-items:center;gap:10px;margin-top:auto;overflow:hidden;background:rgba(0,0,0,.15)}
  .sidebar-user-info span{font-size:12px;color:var(--sidebar-text);display:block;white-space:nowrap;transition:opacity .2s}
  #sidebar.collapsed .sidebar-user-info span{opacity:0;width:0;overflow:hidden}
  .points-badge{background:linear-gradient(135deg,#f59e0b,#fbbf24);color:#78350f;font-size:11px;font-weight:700;padding:2px 7px;border-radius:8px}

  /* ── IDEA CARDS ── */
  .idea-card{background:var(--surface);border-radius:var(--r-lg);border:1px solid var(--border);padding:16px;margin-bottom:10px;cursor:pointer;transition:transform .18s var(--ease),box-shadow .18s var(--ease),border-color .18s}
  .idea-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);border-color:var(--input-border)}
  .idea-card-header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
  .idea-card-id{font-size:10px;color:var(--primary);font-weight:700;text-transform:uppercase;letter-spacing:.5px;opacity:.8}
  .idea-card-title{font-size:14px;font-weight:600;color:var(--heading);margin-top:3px;letter-spacing:-.1px}
  .idea-card-meta{font-size:11px;color:var(--subtle);margin-top:8px}
  .idea-card-footer{display:flex;align-items:center;justify-content:space-between;margin-top:12px}

  /* ── FILTER BAR ── */
  .filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
  .filter-bar input,.filter-bar select{padding:8px 12px;border:1px solid var(--input-border);border-radius:var(--r);font-size:13px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit;background:var(--input-bg);color:var(--text)}
  .filter-bar input:focus,.filter-bar select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.12)}

  /* ── TABS ── */
  .tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:18px;gap:2px}
  .tab{padding:9px 18px;cursor:pointer;font-size:13px;font-weight:500;color:var(--subtle);border-bottom:2px solid transparent;margin-bottom:-1px;transition:color .15s,border-color .15s;border-radius:var(--r) var(--r) 0 0}
  .tab:hover{color:var(--primary);background:var(--primary-lt)}
  .tab.active{color:var(--primary);border-bottom-color:var(--primary);font-weight:600}
  .tab-content{display:none}
  .tab-content.active{display:block}

  /* ── CHIPS ── */
  .chip-filter{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
  .chip{padding:5px 14px;border-radius:20px;font-size:12px;cursor:pointer;border:1px solid var(--input-border);background:var(--chip-bg);color:var(--subtext);transition:all .15s var(--ease);font-weight:500}
  .chip:hover{border-color:var(--primary);color:var(--primary)}
  .chip.active{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 8px rgba(79,70,229,.3)}

  /* ── MINI STATS ── */
  .mini-stat{display:flex;flex-direction:column;align-items:center;padding:16px;background:var(--bg);border-radius:var(--r-lg);flex:1;border:1px solid var(--border)}
  .mini-stat-val{font-size:22px;font-weight:800;color:var(--heading)}
  .mini-stat-label{font-size:11px;color:var(--subtle);margin-top:4px;text-align:center;font-weight:500}

  /* ── SPINNER ── */
  .spinner{display:inline-block;width:20px;height:20px;border:2px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* ── EMPTY STATE ── */
  .empty-state{text-align:center;padding:60px 20px;color:var(--subtle);font-size:13px;font-weight:500}
  .empty-state::before{content:'⊘';display:block;font-size:32px;margin-bottom:10px;opacity:.35}

  /* ── DARK MODE TOGGLE ── */
  .dm-toggle{display:flex;align-items:center;gap:7px;cursor:pointer;padding:6px 11px;border:1px solid var(--input-border);border-radius:var(--r);background:var(--surface);transition:all .15s var(--ease);font-size:13px;font-weight:600;color:var(--text-muted);user-select:none}
  .dm-toggle:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-lt)}
  .dm-track{width:32px;height:18px;background:var(--border);border-radius:9px;position:relative;transition:background .25s;flex-shrink:0}
  .dm-track.on{background:var(--primary)}
  .dm-thumb{position:absolute;top:2px;left:2px;width:14px;height:14px;border-radius:50%;background:#fff;transition:transform .25s var(--ease);box-shadow:0 1px 3px rgba(0,0,0,.25)}
  .dm-track.on .dm-thumb{transform:translateX(14px)}

  /* ── USER SEARCH ── */
  .user-search-results{border:1.5px solid var(--input-border);border-radius:var(--r);background:var(--surface);max-height:160px;overflow-y:auto;display:none;position:absolute;z-index:10;width:100%;box-shadow:var(--shadow-md)}
  .user-search-results .uitem{padding:9px 14px;cursor:pointer;font-size:13px;transition:background .12s;color:var(--text)}
  .user-search-results .uitem:hover{background:var(--bg)}
  .pos-rel{position:relative}

  /* ── SUPER ADMIN PANE ── */
  .sa-pane{display:block;animation:fade-in .25s var(--ease)}

  /* ── STAR RATING ── */
  .star-rating{display:inline-flex;gap:1px;align-items:center;line-height:1}
  .star{font-size:16px;color:#ddd;transition:color .08s;cursor:pointer;user-select:none;-webkit-user-select:none}
  .star.active,.star.hover{color:#d97706}
  .star-rating.readonly .star{cursor:default;pointer-events:none}
  .star-widget{display:flex;align-items:center;gap:6px;flex-wrap:wrap}
  .eng-badge{display:inline-flex;align-items:center;gap:3px;padding:2px 9px;border-radius:12px;font-size:11px;font-weight:700;border:1px solid}

  /* ── LANGUAGE TOGGLE ── */
  .lang-toggle{background:var(--surface);border:1px solid var(--input-border);border-radius:var(--r);padding:5px 11px;cursor:pointer;font-size:11px;font-weight:700;color:var(--text-muted);transition:all .15s var(--ease);letter-spacing:.8px;line-height:1;font-family:inherit}
  .lang-toggle:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-lt)}

  /* ── TOP IDEAS ── */
  .top-idea-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border)}
  .top-idea-rank{width:24px;text-align:center;font-size:11px;font-weight:800;color:#c7d2fe}

  /* ── LOGIN SPLIT LAYOUT ── */
  .login-wrap{min-height:100vh;display:flex;align-items:stretch;background:none}
  .login-left{flex:0 0 460px;background:linear-gradient(145deg,#1e1b4b 0%,#312e81 35%,#3730a3 65%,#4338ca 100%);color:#fff;display:flex;flex-direction:column;justify-content:center;padding:64px 52px;overflow:hidden;position:relative}
  .login-left::before{content:'';position:absolute;top:-120px;right:-80px;width:380px;height:380px;background:radial-gradient(circle,rgba(99,102,241,.35) 0%,transparent 70%);border-radius:50%;pointer-events:none;animation:float 7s ease-in-out infinite}
  .login-left::after{content:'';position:absolute;bottom:-100px;left:-100px;width:320px;height:320px;background:radial-gradient(circle,rgba(139,92,246,.25) 0%,transparent 70%);border-radius:50%;pointer-events:none;animation:float 9s ease-in-out infinite reverse}
  .login-left .bubble{position:absolute;border-radius:50%;background:rgba(255,255,255,.035);pointer-events:none}
  .login-left .bubble-1{width:140px;height:140px;top:25%;left:-50px;animation:float 5s ease-in-out infinite 1s}
  .login-left .bubble-2{width:80px;height:80px;top:65%;right:25px;animation:float 4s ease-in-out infinite .5s}
  .login-feature{display:flex;align-items:center;gap:16px;margin-bottom:22px}
  .login-feature-icon{width:42px;height:42px;background:rgba(255,255,255,.12);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,.15)}
  .login-feature-title{font-size:14px;font-weight:700;margin-bottom:3px}
  .login-feature-sub{font-size:12px;opacity:.6;line-height:1.4}
  .login-right{flex:1;display:flex;align-items:center;justify-content:center;background:var(--bg);padding:40px}
  .login-card{background:var(--surface);border-radius:var(--r-xl);padding:40px;width:100%;max-width:420px;box-shadow:var(--shadow-xl);border:1px solid var(--border)}
  .login-logo{text-align:left;margin-bottom:28px}
  .login-logo h2{font-size:22px;color:var(--heading);font-weight:800;margin-bottom:4px;letter-spacing:-.3px}
  .login-logo p{font-size:13px;color:var(--subtle)}

  /* ── KPI ICON ── */
  .kpi-card{display:flex;align-items:center;gap:14px;position:relative;overflow:hidden}
  .kpi-card::after{content:'';position:absolute;right:-16px;top:-16px;width:72px;height:72px;border-radius:50%;opacity:.05;background:currentColor}
  .kpi-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:transform .2s var(--ease)}
  .kpi-card:hover .kpi-icon{transform:scale(1.08) rotate(-3deg)}
  .kpi-icon svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .kpi-body{flex:1}

  /* ── WIZARD STEP CIRCLES ── */
  .wizard-steps{display:flex;align-items:flex-start;gap:0;margin-bottom:28px;position:relative}
  .wizard-steps::before{content:'';position:absolute;top:16px;left:10%;right:10%;height:2px;background:var(--border);z-index:0}
  .w-step{flex:1;text-align:center;padding:0 4px 10px;font-size:11px;border-bottom:none;color:var(--subtle);cursor:pointer;font-weight:500;transition:color .15s;position:relative;z-index:1}
  .w-step .w-num{display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:var(--bar-track);color:var(--text-muted);font-size:12px;font-weight:700;margin:0 auto 6px;border:2px solid var(--border);transition:all .2s var(--ease)}
  .w-step .w-lbl{font-size:11px;font-weight:500;white-space:nowrap}
  .w-step.active{color:var(--primary)}
  .w-step.active .w-num{background:var(--primary);color:#fff;border-color:var(--primary);box-shadow:0 2px 10px rgba(79,70,229,.4)}
  .w-step.done{color:#059669}
  .w-step.done .w-num{background:#059669;color:#fff;border-color:#059669}

  /* ── SECTION HEADER ACCENT ── */
  .section-header .page-title{position:relative;padding-left:12px}
  .section-header .page-title::before{content:'';position:absolute;left:0;top:2px;bottom:2px;width:3px;background:linear-gradient(180deg,#4f46e5,#818cf8);border-radius:2px}

  /* ── SCROLLBAR ── */
  #content::-webkit-scrollbar{width:6px}
  #content::-webkit-scrollbar-track{background:transparent}
  #content::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px}
  #content::-webkit-scrollbar-thumb:hover{background:var(--text-muted)}

  /* ── GLOBAL ANIMATIONS ── */
  @keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
  @keyframes fadeInDown{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}
  @keyframes fadeInLeft{from{opacity:0;transform:translateX(-16px)}to{opacity:1;transform:translateX(0)}}
  @keyframes fadeInRight{from{opacity:0;transform:translateX(16px)}to{opacity:1;transform:translateX(0)}}
  @keyframes scaleIn{from{opacity:0;transform:scale(.94)}to{opacity:1;transform:scale(1)}}
  @keyframes pulse-ring{0%{box-shadow:0 0 0 0 rgba(244,67,54,.5)}70%{box-shadow:0 0 0 7px rgba(244,67,54,0)}100%{box-shadow:0 0 0 0 rgba(244,67,54,0)}}
  @keyframes shimmer{0%{background-position:-400px 0}100%{background-position:400px 0}}
  @keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
  @keyframes count-up{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
  @keyframes glow{0%,100%{box-shadow:0 0 0 rgba(255,255,255,.15)}50%{box-shadow:0 0 12px rgba(255,255,255,.3)}}
  @keyframes bar-grow{from{width:0}to{width:var(--bar-w,100%)}}
  @keyframes slide-in-sidebar{from{opacity:0;transform:translateX(-8px)}to{opacity:1;transform:translateX(0)}}
  @keyframes badge-pop{0%{transform:scale(0)}60%{transform:scale(1.2)}100%{transform:scale(1)}}
  @keyframes ripple{to{transform:scale(4);opacity:0}}
  @keyframes gradient-shift{0%{background-position:0% 50%}50%{background-position:100% 50%}100%{background-position:0% 50%}}
  @keyframes tl-item-in{from{opacity:0;transform:translateX(-12px)}to{opacity:1;transform:translateX(0)}}
  @keyframes kpi-count{from{opacity:0;transform:translateY(6px) scale(.96)}to{opacity:1;transform:translateY(0) scale(1)}}

  /* ── ANIMATED PAGE TRANSITIONS ── */
  .page.active{animation:fadeInUp .28s var(--ease)}

  /* ── ANIMATED KPI CARDS ── */
  .kpi-card{animation:fadeInUp .35s var(--ease) both}
  .kpi-grid .kpi-card:nth-child(1){animation-delay:.04s}
  .kpi-grid .kpi-card:nth-child(2){animation-delay:.09s}
  .kpi-grid .kpi-card:nth-child(3){animation-delay:.14s}
  .kpi-grid .kpi-card:nth-child(4){animation-delay:.19s}
  .kpi-val{animation:kpi-count .4s var(--ease) both;animation-delay:.25s}

  /* ── ANIMATED CARDS ── */
  .card{animation:scaleIn .3s var(--ease) both}

  /* ── IDEA CARD STAGGER ── */
  .idea-card{animation:fadeInUp .28s var(--ease) both}

  /* ── NAV ITEM ANIMATIONS ── */
  .nav-item{animation:slide-in-sidebar .22s var(--ease) both}
  .nav-item:nth-child(1){animation-delay:.03s}
  .nav-item:nth-child(2){animation-delay:.06s}
  .nav-item:nth-child(3){animation-delay:.09s}
  .nav-item:nth-child(4){animation-delay:.12s}
  .nav-item:nth-child(5){animation-delay:.15s}
  .nav-item:nth-child(6){animation-delay:.18s}
  .nav-item:nth-child(7){animation-delay:.21s}
  .nav-item:nth-child(8){animation-delay:.24s}

  /* ── LEADERBOARD ROW STAGGER ── */
  .lb-row{animation:fadeInLeft .25s var(--ease) both}

  /* ── TIMELINE STAGGER ── */
  .tl-item{animation:tl-item-in .3s var(--ease) both}

  /* ── SKELETON LOADER ── */
  .skeleton{background:linear-gradient(90deg,var(--bar-track) 25%,var(--border) 50%,var(--bar-track) 75%);background-size:400px 100%;animation:shimmer 1.4s infinite linear;border-radius:var(--r);display:inline-block}

  /* ── BAR FILL BASE ── */
  .bar-fill{transition:width .7s cubic-bezier(.4,0,.2,1)}

  /* ── BUTTON RIPPLE ── */
  .btn{position:relative;overflow:hidden}
  .btn .ripple-el{position:absolute;border-radius:50%;background:rgba(255,255,255,.35);transform:scale(0);animation:ripple .6s linear;pointer-events:none}

  /* ── NOTIF BADGE POP ── */
  .notif-badge{animation:badge-pop .3s var(--ease)}

  /* ── ACTIVE NAV GLOW ── */
  .nav-item.active .icon{animation:none}

  /* ── ANIMATED LOGIN ── */
  .login-card{animation:scaleIn .38s var(--ease)}
  .login-left{overflow:hidden}
  .login-feature{animation:fadeInLeft .35s var(--ease) both}
  .login-feature:nth-child(4){animation-delay:.12s}
  .login-feature:nth-child(5){animation-delay:.22s}
  .login-feature:nth-child(6){animation-delay:.32s}
  .login-left h1{animation:fadeInDown .4s var(--ease)}
  .login-left p{animation:fadeInDown .4s var(--ease) .08s both}

  /* ── ANIMATED MODAL ── */
  .modal{animation:scaleIn .22s var(--ease)}

  /* ── ANIMATED TABS ── */
  .tab{position:relative}
  .tab::after{content:'';position:absolute;bottom:-1px;left:50%;right:50%;height:2px;background:var(--primary);transition:left .22s var(--ease),right .22s var(--ease)}
  .tab.active::after{left:0;right:0}

  /* ── WIZARD STEP TRANSITION ── */
  .wizard-body{animation:fadeInUp .22s var(--ease)}

  /* ── TOPBAR SLIDE DOWN ── */
  #topbar{animation:fadeInDown .3s var(--ease)}

  /* ── UPLOAD ZONE PULSE ON DRAG ── */
  @keyframes upload-pulse{0%,100%{box-shadow:0 0 0 0 rgba(79,70,229,.3)}50%{box-shadow:0 0 0 8px rgba(79,70,229,0)}}
  .upload-zone.drag-over{border-color:var(--primary);background:var(--primary-lt);animation:upload-pulse 1s ease-in-out infinite}

  /* ── ANIMATED SCORE BADGE ── */
  .score-badge{transition:transform .15s var(--ease)}
  .score-badge:hover{transform:scale(1.08)}

  /* ── FLOATING ACTION HINT ── */
  .btn-primary.btn-fab{animation:float 3s ease-in-out infinite}

  /* ── CHIP CLICK ANIMATION ── */
  .chip,.impact-chip{transition:all .18s var(--ease),transform .12s}
  .chip:active,.impact-chip:active{transform:scale(.94)}

  /* ── TABLE ROW FADE ── */
  tbody tr{animation:fadeInUp .22s var(--ease) both}
  tbody tr:nth-child(1){animation-delay:.03s}
  tbody tr:nth-child(2){animation-delay:.06s}
  tbody tr:nth-child(3){animation-delay:.09s}
  tbody tr:nth-child(4){animation-delay:.12s}
  tbody tr:nth-child(5){animation-delay:.15s}
  tbody tr:nth-child(6){animation-delay:.18s}
  tbody tr:nth-child(7){animation-delay:.21s}
  tbody tr:nth-child(8){animation-delay:.24s}

  /* ── PROGRESS FILL ANIMATE ── */
  .progress-fill{transition:width .8s var(--ease)}

  /* ── SIDEBAR LOGO ── */
  .sidebar-logo{background:rgba(255,255,255,.03)}

  /* ── SIDEBAR TOOLTIP (collapsed) ── */
  #sidebar.collapsed .nav-item{position:relative}
  #sidebar.collapsed .nav-item:hover::after{content:attr(data-label);position:absolute;left:58px;top:50%;transform:translateY(-50%);background:#0f172a;color:#fff;font-size:12px;font-weight:600;padding:5px 12px;border-radius:7px;white-space:nowrap;pointer-events:none;box-shadow:0 4px 12px rgba(0,0,0,.35);z-index:100;border:1px solid rgba(255,255,255,.08)}

  /* ── STATUS STRIPE ON IDEA CARDS ── */
  .idea-card[data-status="Approved"]{border-left:3px solid #059669}
  .idea-card[data-status="Rejected"]{border-left:3px solid #dc2626}
  .idea-card[data-status="Implemented"]{border-left:3px solid #7c3aed}
  .idea-card[data-status="Under Review"]{border-left:3px solid #d97706}
  .idea-card[data-status="Submitted"]{border-left:3px solid #4f46e5}
  .idea-card[data-status="Draft"]{border-left:3px solid #cbd5e1}
</style>
</head>
<body>

<div id="login-page" class="login-wrap" <?= $loggedIn ? 'style="display:none"' : '' ?>>

  <div class="login-left">
    <div class="bubble bubble-1"></div>
    <div class="bubble bubble-2"></div>
    <img src="assets/ifqm-logo.png" alt="IFQM" style="height:52px;margin-bottom:16px;background:#fff;border-radius:8px;padding:4px 10px;object-fit:contain"/>
    <h1 style="font-size:26px;font-weight:800;line-height:1.25;margin-bottom:10px" data-i18n="login.app_title">Employee Ideation Tool</h1>
    <p style="font-size:14px;opacity:.75;line-height:1.7;margin-bottom:44px" data-i18n="login.tagline">Turn great ideas into real improvements.</p>

    <div class="login-feature">
      <div class="login-feature-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg>
      </div>
      <div><div class="login-feature-title" data-i18n="login.feat1_title">Submit &amp; Track Ideas</div><div class="login-feature-sub" data-i18n="login.feat1_sub">5-step wizard with AI quality scoring</div></div>
    </div>
    <div class="login-feature">
      <div class="login-feature-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <div><div class="login-feature-title" data-i18n="login.feat2_title">Earn Points &amp; Rewards</div><div class="login-feature-sub">+10 submit &nbsp;&middot;&nbsp; +25 approved &nbsp;&middot;&nbsp; +65 implemented</div></div>
    </div>
    <div class="login-feature">
      <div class="login-feature-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div><div class="login-feature-title" data-i18n="login.feat3_title">Analytics &amp; Leaderboard</div><div class="login-feature-sub" data-i18n="login.feat3_sub">Real-time insights across departments</div></div>
    </div>
  </div>

  <div class="login-right">
    <div class="login-card">
      <div class="login-logo">
        <img src="assets/ifqm-logo.png" alt="IFQM" style="height:38px;margin-bottom:10px;object-fit:contain"/>
        <h2 data-i18n="login.welcome">Welcome back</h2>
        <p data-i18n="login.subtitle">Sign in to your IFQM account to continue</p>
      </div>
      <div id="login-error" class="alert alert-danger" style="display:none"></div>
      <div class="form-group">
        <label data-i18n="login.email">Email Address</label>
        <input class="form-control" id="login-email" type="email" placeholder="your.name@ifqm.com" value="yashas.r@ifqm.com"/>
      </div>
      <div class="form-group">
        <label data-i18n="login.password">Password</label>
        <input class="form-control" id="login-pass" type="password" placeholder="••••••••" value="password"/>
      </div>
      <button class="btn btn-primary" id="login-btn" style="width:100%;justify-content:center;padding:11px;font-size:14px" onclick="doLogin()" data-i18n="login.btn">Sign In</button>
      <div class="separator"></div>
      <p style="font-size:11px;color:#aaa;text-align:center">Session-based Auth &middot; Role-Based Access Control &middot; MySQL Backend</p>
      <div class="separator"></div>
      <div style="background:var(--panel-bg);border-radius:8px;padding:12px 14px">
        <p style="font-size:11px;color:#818cf8;margin-bottom:6px"><strong>Demo accounts</strong> &mdash; password: <code style="background:var(--tag-bg);padding:1px 5px;border-radius:4px">password</code></p>
        <p style="font-size:11px;color:var(--label);line-height:1.8">yashas.r@ifqm.com &middot; priya.sharma@ifqm.com<br>bhuvan.kh@ifqm.com &middot; adrish.c@ifqm.com</p>
      </div>
    </div>
  </div>

</div>

<div id="main-app" style="<?= $loggedIn ? '' : 'display:none;' ?>height:100vh">
<div id="app">

  <div id="sidebar">
    <div class="sidebar-logo">
      <img src="assets/ifqm-logo.png" alt="IFQM" style="height:26px;flex-shrink:0;background:#fff;border-radius:4px;padding:2px 6px;object-fit:contain"/>
      <span style="font-weight:700;letter-spacing:-.3px" data-i18n="app.name">IdeaTool</span>
    </div>

    <div class="nav-section" data-i18n="section.main">Main</div>
    <div class="nav-item active" data-label="Dashboard" onclick="navigate('dashboard',this)"><span class="icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span><span class="label" data-i18n="nav.dashboard">Dashboard</span></div>
    <div class="nav-item" id="nav-my-ideas" data-label="My Ideas" onclick="navigate('my-ideas',this)"><span class="icon"><svg viewBox="0 0 24 24"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg></span><span class="label" data-i18n="nav.my_ideas">My Ideas</span></div>
    <div class="nav-item" data-label="Submit Idea" id="nav-submit" onclick="navigate('submit',this)"><span class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg></span><span class="label" data-i18n="nav.submit">Submit Idea</span></div>

    <div class="nav-section" data-i18n="section.workflow">Workflow</div>
    <div class="nav-item" data-label="Review Queue" id="nav-review" onclick="navigate('review',this)" style="display:none"><span class="icon"><svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/><polyline points="9 14 11 16 15 12"/></svg></span><span class="label" data-i18n="nav.review">Review Queue</span></div>
    <div class="nav-item" data-label="All Ideas" id="nav-all" onclick="navigate('ideas-all',this)"><span class="icon"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.5" cy="6" r="1" fill="currentColor" stroke="none"/><circle cx="3.5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="3.5" cy="18" r="1" fill="currentColor" stroke="none"/></svg></span><span class="label" data-i18n="nav.all_ideas">All Ideas</span></div>
    <div class="nav-item" data-label="Audit Trail" id="nav-audit" onclick="navigate('audit',this)"><span class="icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="label" data-i18n="nav.audit">Audit Trail</span></div>

    <div class="nav-section" data-i18n="section.insights">Insights</div>
    <div class="nav-item" data-label="Leaderboard" onclick="navigate('leaderboard',this)"><span class="icon"><svg viewBox="0 0 24 24"><path d="M8 21h8M12 17v4M17 3h3l-1 5a4 4 0 01-4 3M7 3H4l1 5a4 4 0 004 3"/><path d="M7 11a5 5 0 0010 0V3H7v8z"/></svg></span><span class="label" data-i18n="nav.leaderboard">Leaderboard</span></div>
    <div class="nav-item" data-label="Analytics" id="nav-analytics" onclick="navigate('analytics',this)"><span class="icon"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span><span class="label" data-i18n="nav.analytics">Analytics</span></div>

    <div class="nav-section" id="nav-section-admin" style="display:none" data-i18n="section.admin">Admin</div>
    <div class="nav-item" data-label="Admin Panel" id="nav-admin" onclick="navigate('admin',this)" style="display:none"><span class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 115 19.07M12 2v2M12 20v2M2 12h2M20 12h2"/></svg></span><span class="label" data-i18n="nav.admin">Admin Panel</span></div>

    <div class="nav-section" id="nav-section-super-admin" style="display:none" data-i18n="section.super_admin">IFQM Super Admin</div>
    <div class="nav-item" data-label="Org Hierarchy" id="nav-super-admin" onclick="navigate('super-admin',this)" style="display:none"><span class="icon"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></span><span class="label" data-i18n="nav.super_admin">Org Hierarchy</span></div>

    <div class="nav-item" data-label="My Profile" onclick="navigate('profile',this)" style="margin-top:auto"><span class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></span><span class="label" data-i18n="nav.profile">My Profile</span></div>

    <div class="sidebar-user">
      <div class="avatar" id="sb-avatar" style="flex-shrink:0">??</div>
      <div class="sidebar-user-info">
        <span id="sb-name" style="font-size:13px;font-weight:600">Loading…</span>
        <span id="sb-role"></span>
        <span><span class="points-badge" id="sb-points">0 pts</span></span>
      </div>
    </div>
  </div>

  <div id="main">
    <div id="topbar" style="position:relative">
      <div class="topbar-left">
        <button style="background:none;border:none;cursor:pointer;font-size:20px;color:var(--text-muted);padding:4px 6px;border-radius:6px;transition:background .15s;line-height:1" onmouseover="this.style.background='var(--bar-track)'" onmouseout="this.style.background='none'" onclick="toggleSidebar()">&#9776;</button>
        <span class="page-title" id="page-title">Dashboard</span>
      </div>
      <div class="topbar-right">
        <div class="dm-toggle" onclick="toggleDarkMode()" title="Toggle dark mode" id="dm-btn">
          <div class="dm-track" id="dm-track"><div class="dm-thumb"></div></div>
          <span id="dm-label">Dark</span>
        </div>
        <button class="lang-toggle" id="lang-btn" onclick="toggleLang()" title="Switch Language / भाषा बदलें">EN</button>
        <div class="notif-bell" onclick="toggleNotif()" title="Notifications" style="display:flex;align-items:center;gap:6px">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
          <span data-i18n="topbar.notifications">Notifications</span>
          <div class="notif-badge" id="notif-count" style="display:none">0</div>
        </div>
        <div class="user-chip" onclick="navigate('profile',null)">
          <div class="avatar" id="top-avatar">??</div>
          <span id="top-name">Loading…</span>
          <span class="role-badge" id="top-role">-</span>
        </div>
        <button class="btn btn-outline btn-sm" onclick="doLogout()" data-i18n="topbar.logout">Logout</button>
      </div>
      <div class="notification-panel" id="notif-panel">
        <div style="padding:10px 14px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center">
          <strong style="font-size:13px">Notifications</strong>
          <button class="btn btn-outline btn-sm" onclick="markAllRead()" data-i18n="topbar.mark_read">Mark all read</button>
        </div>
        <div id="notif-list"><div class="empty-state">Loading…</div></div>
      </div>
    </div>

    <div id="content">

      <div class="page active" id="page-dashboard">
        <div class="kpi-grid" id="dash-kpis">
          <div class="kpi-card"><div class="spinner"></div></div>
          <div class="kpi-card"><div class="spinner"></div></div>
          <div class="kpi-card"><div class="spinner"></div></div>
          <div class="kpi-card"><div class="spinner"></div></div>
        </div>
        <div class="grid-2">
          <div class="card">
            <div class="card-title">Status Distribution</div>
            <div id="dash-status-chart"><div class="spinner"></div></div>
          </div>
          <div class="card">
            <div class="card-title">Recent Activity</div>
            <div class="timeline" id="dash-activity"><div class="spinner"></div></div>
          </div>
        </div>
      </div>

      <div class="page" id="page-my-ideas">
        <div class="section-header">
          <div><div class="page-title">My Ideas</div><div class="text-muted">Track all ideas you have submitted</div></div>
          <button class="btn btn-primary" onclick="navigate('submit',document.getElementById('nav-submit'))">New Idea</button>
        </div>
        <div class="filter-bar">
          <input id="my-search" placeholder="Search ideas..." style="flex:1;min-width:180px" oninput="filterMyIdeas()"/>
          <select id="my-status-filter" class="form-control" style="width:140px" onchange="filterMyIdeas()">
            <option value="">All Status</option><option>Draft</option><option>Submitted</option><option>Under Review</option><option>Approved</option><option>Rejected</option><option>Implemented</option>
          </select>
        </div>
        <div id="my-ideas-list"><div class="empty-state"><div class="spinner"></div> Loading…</div></div>
      </div>

      <div class="page" id="page-submit">
        <div class="section-header">
          <div><div class="page-title">Submit New Idea</div><div class="text-muted">Fill in all steps to submit your improvement idea</div></div>
        </div>
        <div class="card">
          <div class="alert alert-info" id="submit-user-banner">Auto-fetched from HR Database: Loading…</div>

          <div class="wizard-steps">
            <div class="w-step active" onclick="goStep(1)"><span class="w-num">1</span><span class="w-lbl">Situation</span></div>
            <div class="w-step" onclick="goStep(2)"><span class="w-num">2</span><span class="w-lbl">Solution</span></div>
            <div class="w-step" onclick="goStep(3)"><span class="w-num">3</span><span class="w-lbl">Impact</span></div>
            <div class="w-step" onclick="goStep(4)"><span class="w-num">4</span><span class="w-lbl">Co-Suggesters</span></div>
            <div class="w-step" onclick="goStep(5)"><span class="w-num">5</span><span class="w-lbl">Review &amp; Submit</span></div>
          </div>

          <div class="wizard-body" id="step-1">
            <h3 style="font-size:14px;color:var(--heading);margin-bottom:12px">Step 1: Describe the Present Situation</h3>
            <div class="form-group">
              <label>Situation Title <span style="color:red">*</span></label>
              <input class="form-control" id="idea-title" placeholder="Brief title for your idea"/>
            </div>
            <div class="form-group">
              <label>Current Situation Description <span style="color:red">*</span></label>
              <textarea class="form-control" id="idea-situation" rows="5" placeholder="Describe the current problem or inefficiency in detail (min. 50 chars)…"></textarea>
            </div>
            <div class="form-group">
              <label>Supporting Document (Optional)</label>
              <input type="file" id="file-situation" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.xlsx,.csv,.docx" style="display:none"/>
              <div class="upload-zone" onclick="document.getElementById('file-situation').click()">
                Click to upload or drag &amp; drop<br/><span style="font-size:11px;color:#bbb">PDF, PNG, JPG, XLSX — Max 10 MB</span>
              </div>
              <div id="file-situation-name" style="font-size:12px;color:#059669;margin-top:4px"></div>
            </div>
          </div>

          <div class="wizard-body" id="step-2" style="display:none">
            <h3 style="font-size:14px;color:var(--heading);margin-bottom:12px">Step 2: Proposed Idea / Solution</h3>
            <div class="form-group">
              <label>Proposed Solution <span style="color:red">*</span></label>
              <textarea class="form-control" id="idea-solution" rows="5" placeholder="Describe your proposed improvement in detail…"></textarea>
            </div>
            <div class="form-group">
              <label>Supporting Document (Optional)</label>
              <input type="file" id="file-solution" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.xlsx,.csv,.docx" style="display:none"/>
              <div class="upload-zone" onclick="document.getElementById('file-solution').click()">
                Click to upload or drag &amp; drop<br/><span style="font-size:11px;color:#bbb">PDF, PNG, JPG, XLSX — Max 10 MB</span>
              </div>
              <div id="file-solution-name" style="font-size:12px;color:#059669;margin-top:4px"></div>
            </div>
          </div>

          <div class="wizard-body" id="step-3" style="display:none">
            <h3 style="font-size:14px;color:var(--heading);margin-bottom:12px">Step 3: Impact Areas &amp; Measurable Benefits</h3>
            <div class="form-group">
              <label>Select Impact Areas <span style="color:red">*</span> (select all that apply)</label>
              <div class="impact-grid" style="margin-top:8px">
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Production">Production</div>
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Quality">Quality</div>
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Cost">Cost</div>
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Delivery">Delivery</div>
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Safety">Safety</div>
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Environment">Environment</div>
                <div class="impact-chip" onclick="toggleImpact(this)" data-val="Morale">Morale</div>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Overall Impact Level</label>
                <select class="form-control" id="idea-impact-level">
                  <option>Low</option><option selected>Medium</option><option>High</option>
                </select>
              </div>
              <div class="form-group">
                <label>Tangible Benefit (Optional)</label>
                <input class="form-control" id="idea-tangible" placeholder="e.g. Rs. 50,000 savings/year"/>
              </div>
            </div>
            <div class="form-group">
              <label>Intangible Benefits (Optional)</label>
              <input class="form-control" id="idea-intangible" placeholder="e.g. Improved worker confidence, better audit scores"/>
            </div>
          </div>

          <div class="wizard-body" id="step-4" style="display:none">
            <h3 style="font-size:14px;color:var(--heading);margin-bottom:12px">Step 4: Co-Suggesters (Optional, max 2)</h3>
            <div class="form-group">
              <label>Co-Suggester 1</label>
              <div class="pos-rel">
                <input class="form-control" id="co1-search" placeholder="Search by name or employee ID…" oninput="searchUsers(this,'co1-results','co1-id','co1-name-display')" autocomplete="off"/>
                <div class="user-search-results" id="co1-results"></div>
              </div>
              <div id="co1-name-display" style="font-size:12px;color:#4f46e5;margin-top:4px"></div>
              <input type="hidden" id="co1-id"/>
            </div>
            <div class="form-group">
              <label>Co-Suggester 2</label>
              <div class="pos-rel">
                <input class="form-control" id="co2-search" placeholder="Search by name or employee ID…" oninput="searchUsers(this,'co2-results','co2-id','co2-name-display')" autocomplete="off"/>
                <div class="user-search-results" id="co2-results"></div>
              </div>
              <div id="co2-name-display" style="font-size:12px;color:#4f46e5;margin-top:4px"></div>
              <input type="hidden" id="co2-id"/>
            </div>
          </div>

          <div class="wizard-body" id="step-5" style="display:none">
            <h3 style="font-size:14px;color:var(--heading);margin-bottom:12px">Step 5: Review &amp; Submit</h3>
            <div id="review-preview"></div>
            <div class="alert alert-info mt-8">By submitting, you confirm this idea is original. You will earn <strong>+10 points</strong> on submission. An AI quality score will be automatically computed.</div>
          </div>

          <div class="wizard-footer" id="wizard-nav">
            <button class="btn btn-outline" id="btn-back" onclick="prevStep()" style="visibility:hidden">&#8592; Back</button>
            <button class="btn btn-outline" onclick="saveDraft()">Save Draft</button>
            <button class="btn btn-primary" id="btn-next" onclick="nextStep()">Next &#8594;</button>
          </div>
          <div class="wizard-footer" id="wizard-submit-row" style="display:none">
            <button class="btn btn-outline" id="btn-back-final" onclick="goStep(4)">&#8592; Back</button>
            <button class="btn btn-outline" onclick="saveDraft()">Save Draft</button>
            <button class="btn btn-success" onclick="submitIdea()">Submit Idea</button>
          </div>
        </div>
      </div>

      <div class="page" id="page-review">
        <div class="section-header">
          <div><div class="page-title">Review Queue</div><div class="text-muted">Ideas pending your review — sorted by AI quality score (highest first)</div></div>
        </div>
        <div id="review-list"><div class="empty-state"><div class="spinner"></div> Loading…</div></div>
      </div>

      <div class="page" id="page-ideas-all">
        <div class="section-header">
          <div><div class="page-title">All Ideas</div></div>
        </div>
        <div class="filter-bar">
          <input id="all-search" placeholder="Search…" style="flex:1" oninput="loadAllIdeas()"/>
          <select id="all-status" class="form-control" style="width:140px" onchange="loadAllIdeas()">
            <option value="">All Status</option><option>Submitted</option><option>Under Review</option><option>Approved</option><option>Rejected</option><option>Implemented</option>
          </select>
          <select id="all-impact" class="form-control" style="width:130px" onchange="loadAllIdeas()">
            <option value="">All Impact</option><option>Low</option><option>Medium</option><option>High</option>
          </select>
        </div>
        <div class="card" style="padding:0;overflow:hidden">
          <table>
            <thead><tr><th>Idea ID</th><th>Title</th><th>Submitted By</th><th>Department</th><th>Impact</th><th>AI Score</th><th>Engagement</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody id="all-ideas-tbody"><tr><td colspan="10" class="text-center"><div class="spinner"></div></td></tr></tbody>
          </table>
        </div>
      </div>

      <div class="page" id="page-audit">
        <div class="section-header">
          <div class="page-title">System Audit Trail</div>
          <div class="text-muted">Immutable append-only log of all workflow actions</div>
        </div>
        <div class="card" style="padding:0;overflow:hidden">
          <div style="font-size:11px;color:#b91c1c;background:#fee2e2;padding:8px 14px;border-left:4px solid #dc2626;font-weight:600;letter-spacing:.2px">This log is append-only and tamper-proof. No record can be edited or deleted.</div>
          <table>
            <thead><tr><th>Timestamp</th><th>Idea</th><th>Action</th><th>Actor</th><th>Comment</th></tr></thead>
            <tbody id="audit-tbody"><tr><td colspan="5" class="text-center"><div class="spinner"></div></td></tr></tbody>
          </table>
        </div>
      </div>

      <div class="page" id="page-leaderboard">
        <div class="section-header"><div class="page-title">Leaderboard &amp; Gamification</div></div>
        <div class="filter-bar">
          <div class="chip-filter">
            <div class="chip active" onclick="activateChip(this,'lb-period');" data-val="all">All Time</div>
            <div class="chip" onclick="activateChip(this,'lb-period');" data-val="monthly">Monthly</div>
            <div class="chip" onclick="activateChip(this,'lb-period');" data-val="quarterly">Quarterly</div>
            <div class="chip" onclick="activateChip(this,'lb-period');" data-val="yearly">Yearly</div>
          </div>
        </div>
        <div class="grid-2">
          <div class="card">
            <div class="card-title">Individual Rankings</div>
            <div id="lb-individuals"><div class="spinner"></div></div>
          </div>
          <div class="card">
            <div class="card-title">Department Rankings</div>
            <div id="lb-departments"><div class="spinner"></div></div>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Top Ideas by AI Quality Score</div>
          <div id="lb-top-ideas"><div class="spinner"></div></div>
        </div>
      </div>

      <div class="page" id="page-analytics">
        <div class="section-header">
          <div class="page-title">Analytics Dashboard</div>
        </div>
        <div class="kpi-grid" id="analytics-kpis">
          <div class="kpi-card"><div class="spinner"></div></div>
          <div class="kpi-card"><div class="spinner"></div></div>
          <div class="kpi-card"><div class="spinner"></div></div>
          <div class="kpi-card"><div class="spinner"></div></div>
        </div>
        <div class="grid-2">
          <div class="card">
            <div class="card-title">Impact Area Distribution</div>
            <div class="bar-chart" id="analytics-impact"></div>
          </div>
          <div class="card">
            <div class="card-title">Status Summary</div>
            <div id="analytics-status"></div>
          </div>
        </div>
        <div class="grid-2">
          <div class="card">
            <div class="card-title">Monthly Submission Trend</div>
            <div id="analytics-trend" class="bar-chart"></div>
          </div>
          <div class="card">
            <div class="card-title">AI Quality Score Distribution</div>
            <div id="analytics-score-dist"></div>
          </div>
        </div>
      </div>

      <div class="page" id="page-admin">
        <div class="section-header"><div class="page-title">Admin Panel</div></div>
        <div class="tabs">
          <div class="tab active" onclick="switchTab(this,'atab1')">Users</div>
          <div class="tab" onclick="switchTab(this,'atab2')">Points Config</div>
          <div class="tab" onclick="switchTab(this,'atab3')">HR Sync</div>
          <div class="tab" onclick="switchTab(this,'atab4')">Rescore Ideas</div>
        </div>
        <div class="tab-content active" id="atab1">
          <table>
            <thead><tr><th>Employee</th><th>Dept</th><th>Email</th><th>Role</th><th>Points</th></tr></thead>
            <tbody id="admin-users-tbody"><tr><td colspan="5" class="text-center"><div class="spinner"></div></td></tr></tbody>
          </table>
        </div>
        <div class="tab-content" id="atab2">
          <div class="alert alert-info">Points config (stored in config.php). Restart needed to apply changes.</div>
          <table>
            <thead><tr><th>Event</th><th>Current Points</th></tr></thead>
            <tbody>
              <tr><td>Idea Submitted</td><td><span class="badge badge-submitted">+10 pts</span></td></tr>
              <tr><td>Idea Approved</td><td><span class="badge badge-approved">+25 pts</span></td></tr>
              <tr><td>Idea Implemented</td><td><span class="badge badge-implemented">+65 pts</span></td></tr>
            </tbody>
          </table>
        </div>
        <div class="tab-content" id="atab3">
          <div class="alert alert-info">Employee data is loaded from the <code>users</code> table. Run the SQL seed script to populate.</div>
          <div class="card" style="max-width:480px">
            <div class="card-title">HR Database Sync Status</div>
            <div style="font-size:13px;line-height:2">
              <div><strong>Status:</strong> Connected to MySQL</div>
              <div><strong>Engine:</strong> MySQL 8.x</div>
              <div><strong>Database:</strong> ifqm_ideation</div>
            </div>
          </div>
        </div>
        <div class="tab-content" id="atab4">
          <div class="alert alert-info">Recompute AI quality scores for all existing ideas using the current scoring model. Use this after importing legacy data.</div>
          <button class="btn btn-warning" onclick="batchRescore()">Rescore All Ideas</button>
          <div id="rescore-result" style="margin-top:12px;font-size:13px"></div>
        </div>
      </div>

      <div class="page" id="page-super-admin">

        <!-- ── Banner ── -->
        <div style="background:linear-gradient(135deg,#312e81 0%,#3730a3 55%,#4338ca 100%);border-radius:var(--r-xl);padding:26px 30px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;box-shadow:var(--shadow-lg)">
          <div>
            <div style="font-size:10px;text-transform:uppercase;letter-spacing:2px;color:rgba(255,255,255,.55);font-weight:700;margin-bottom:6px">IFQM &middot; Super Admin Console</div>
            <div style="font-size:22px;font-weight:800;color:#fff;letter-spacing:-.5px;line-height:1.15">Command Center</div>
            <div style="font-size:12px;color:rgba(255,255,255,.6);margin-top:5px">Complete organizational control &amp; oversight across all levels</div>
          </div>
          <div style="text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:8px">
            <div style="background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.18);border-radius:var(--r);padding:8px 18px">
              <div style="font-size:10px;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.8px;margin-bottom:2px">Signed in as</div>
              <div style="font-size:13px;font-weight:700;color:#fff" id="sa-session-user">—</div>
            </div>
            <div style="font-size:11px;color:rgba(255,255,255,.45)" id="sa-last-updated"></div>
          </div>
        </div>

        <!-- ── KPI Strip ── -->
        <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:22px" id="sa-kpi-strip">
          <div class="kpi-card" style="border-left-color:#4f46e5"><div class="spinner"></div></div>
          <div class="kpi-card" style="border-left-color:#dc2626"><div class="spinner"></div></div>
          <div class="kpi-card" style="border-left-color:#d97706"><div class="spinner"></div></div>
          <div class="kpi-card" style="border-left-color:#059669"><div class="spinner"></div></div>
          <div class="kpi-card" style="border-left-color:#7c3aed"><div class="spinner"></div></div>
          <div class="kpi-card" style="border-left-color:#2563eb"><div class="spinner"></div></div>
        </div>

        <!-- ── Tabs ── -->
        <div class="tabs" style="margin-bottom:0">
          <div class="tab active" onclick="switchSaTab(this,'sa-overview')">Overview</div>
          <div class="tab" onclick="switchSaTab(this,'sa-hierarchy')">Org Hierarchy</div>
          <div class="tab" onclick="switchSaTab(this,'sa-users')">User Management</div>
          <div class="tab" onclick="switchSaTab(this,'sa-system')">System</div>
        </div>

        <!-- ── Tab: Overview ── -->
        <div id="sa-overview" class="sa-pane" style="padding-top:20px">
          <div class="grid-2">
            <div class="card">
              <div class="card-title">Idea Status Distribution</div>
              <div id="sa-status-dist"><div class="spinner"></div></div>
            </div>
            <div class="card">
              <div class="card-title">Recent Activity</div>
              <div class="timeline" id="sa-recent-activity" style="max-height:360px;overflow-y:auto"><div class="spinner"></div></div>
            </div>
          </div>
        </div>

        <!-- ── Tab: Org Hierarchy ── -->
        <div id="sa-hierarchy" class="sa-pane" style="display:none;padding-top:20px">
          <div class="card">
            <div class="card-title">Organization Tree &mdash; Admin &rarr; Manager &rarr; Employee</div>
            <div id="hierarchy-tree" style="padding:4px 0"><div class="spinner" style="margin:20px auto"></div></div>
          </div>
        </div>

        <!-- ── Tab: User Management ── -->
        <div id="sa-users" class="sa-pane" style="display:none;padding-top:20px">
          <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
              <div class="card-title" style="margin-bottom:0;padding-bottom:0;border-bottom:none">All Employees</div>
              <input id="sa-user-search" class="form-control" placeholder="Search by name, email or ID…" style="width:260px" oninput="filterSaUsers()"/>
            </div>
            <table id="sa-users-table">
              <thead><tr><th>Employee</th><th>ID</th><th>Role</th><th>Department</th><th>Business Unit</th><th>Email</th><th>Reports To</th><th>Points</th><th>Ideas</th></tr></thead>
              <tbody id="hierarchy-users-tbody"><tr><td colspan="9" class="text-center"><div class="spinner"></div></td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- ── Tab: System ── -->
        <div id="sa-system" class="sa-pane" style="display:none;padding-top:20px">
          <div class="grid-2" style="margin-bottom:16px">
            <div class="card">
              <div class="card-title">Points Configuration</div>
              <table style="font-size:13px">
                <thead><tr><th>Event</th><th>Points Awarded</th></tr></thead>
                <tbody>
                  <tr><td>Idea Submitted</td><td><span class="badge badge-submitted">+10 pts</span></td></tr>
                  <tr><td>Idea Approved</td><td><span class="badge badge-approved">+25 pts</span></td></tr>
                  <tr><td>Idea Implemented</td><td><span class="badge badge-implemented">+65 pts</span></td></tr>
                </tbody>
              </table>
              <div style="font-size:11px;color:var(--subtle);margin-top:12px">Configured in <code>api/config.php</code>. Server restart required to apply changes.</div>
            </div>
            <div class="card">
              <div class="card-title">Database &amp; HR Sync</div>
              <div style="font-size:13px;line-height:2.4">
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border)"><span style="color:var(--subtle)">Status</span><span style="color:#059669;font-weight:700">&#9679; Connected</span></div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border)"><span style="color:var(--subtle)">Engine</span><span>MySQL 8.x</span></div>
                <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border)"><span style="color:var(--subtle)">Database</span><span>ifqm_ideation</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:var(--subtle)">Charset</span><span>utf8mb4_unicode_ci</span></div>
              </div>
              <div style="font-size:11px;color:var(--subtle);margin-top:12px">Employee data is sourced from the <code>users</code> table. Run the SQL seed script to re-populate.</div>
            </div>
          </div>
          <div class="card">
            <div class="card-title">AI Scoring Engine</div>
            <div style="font-size:13px;color:var(--subtext);margin-bottom:16px">Recompute AI quality scores for all existing ideas using the current scoring model. Use this after importing legacy data or after updating the scoring algorithm.</div>
            <div style="display:flex;align-items:center;gap:14px">
              <button class="btn btn-warning" id="sa-rescore-btn" onclick="batchRescoreSa()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg>
                Rescore All Ideas
              </button>
              <div id="sa-rescore-result" style="font-size:13px"></div>
            </div>
          </div>
        </div>

      </div>

      <div class="page" id="page-profile">
        <div class="grid-2">
          <div class="card">
            <div class="card-title">Employee Profile</div>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
              <div class="avatar" id="profile-avatar" style="width:60px;height:60px;font-size:22px;border-radius:50%">??</div>
              <div>
                <div style="font-size:18px;font-weight:700;color:var(--heading)" id="profile-name">Loading…</div>
                <div style="font-size:12px;color:var(--label)" id="profile-empid"></div>
                <span class="badge badge-submitted" id="profile-role-badge"></span>
              </div>
            </div>
            <table style="font-size:13px" id="profile-table"></table>
            <div class="separator"></div>
            <div style="font-size:11px;color:var(--subtle)">Auto-fetched from HR Database. Contact Admin to update.</div>
          </div>
          <div>
            <div class="card">
              <div class="card-title">My Stats</div>
              <div style="display:flex;gap:10px" id="profile-stats"></div>
            </div>
          </div>
        </div>
      </div>

    </div>  </div></div></div>
<div class="modal-overlay" id="modal-idea-detail">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title" id="modal-idea-code">Idea Detail</div>
        <div style="font-size:12px;color:var(--subtle)" id="modal-idea-title-sub"></div>
      </div>
      <span class="modal-close" onclick="closeModal('modal-idea-detail')">&#10005;</span>
    </div>
    <div class="modal-body">
      <div class="tabs">
        <div class="tab active" onclick="switchTab(this,'dtab1')">Details</div>
        <div class="tab" onclick="switchTab(this,'dtab2')">Timeline</div>
        <div class="tab" onclick="switchTab(this,'dtab3')">Attachments</div>
      </div>
      <div class="tab-content active" id="dtab1">
        <div id="modal-detail-body"><div class="spinner"></div></div>
      </div>
      <div class="tab-content" id="dtab2">
        <div class="timeline" id="modal-timeline"></div>
      </div>
      <div class="tab-content" id="dtab3">
        <div id="modal-attachments"></div>
      </div>
    </div>
    <div class="modal-footer" id="idea-detail-footer">
      <button class="btn btn-outline" onclick="closeModal('modal-idea-detail')">Close</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-review">
  <div class="modal" style="width:480px">
    <div class="modal-header">
      <div class="modal-title">Review Decision &mdash; <span id="review-id"></span></div>
      <span class="modal-close" onclick="closeModal('modal-review')">&#10005;</span>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Decision <span style="color:red">*</span></label>
        <select class="form-control" id="review-decision">
          <option value="Under Review">Move to Under Review</option>
          <option value="Approved">Approve</option>
          <option value="Rejected">Reject</option>
          <option value="Implemented">Mark as Implemented</option>
        </select>
      </div>
      <div class="form-group">
        <label>Comment / Feedback</label>
        <textarea class="form-control" id="review-comment" rows="3" placeholder="Optional comments for the submitter…"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modal-review')">Cancel</button>
      <button class="btn btn-primary" onclick="submitReview()">Submit Decision</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-success">
  <div class="modal" style="width:400px;text-align:center">
    <div class="modal-body" style="padding:36px 32px">
      <div style="width:72px;height:72px;background:linear-gradient(135deg,#059669,#10b981);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;animation:badge-pop .4s cubic-bezier(.4,0,.2,1),glow 2s ease-in-out .5s infinite;box-shadow:0 4px 20px rgba(5,150,105,.35)" id="success-icon">
        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div style="font-size:18px;font-weight:700;color:var(--heading);margin:12px 0 6px;animation:fadeInUp .3s ease .15s both" id="success-title"></div>
      <div style="font-size:13px;color:var(--subtext);animation:fadeInUp .3s ease .25s both" id="success-msg"></div>
      <button class="btn btn-primary mt-8" style="margin-top:20px;animation:fadeInUp .3s ease .35s both" onclick="closeModal('modal-success');navigate('my-ideas',null)">View My Ideas</button>
    </div>
  </div>
</div>

<script>
let currentUser   = <?= $loggedIn ? json_encode($user) : 'null' ?>;
let currentStep   = 1;
const totalSteps  = 5;
let draftIdeaId   = null;
let pendingIdeaId = null;
let allMyIdeas    = [];

/* ── RIPPLE EFFECT ── */
document.addEventListener('click', function(e) {
  const btn = e.target.closest('.btn');
  if (!btn) return;
  const r = document.createElement('span');
  r.className = 'ripple-el';
  const rect = btn.getBoundingClientRect();
  const size = Math.max(rect.width, rect.height);
  r.style.cssText = `width:${size}px;height:${size}px;left:${e.clientX-rect.left-size/2}px;top:${e.clientY-rect.top-size/2}px`;
  btn.appendChild(r);
  r.addEventListener('animationend', () => r.remove());
});

/* ── DRAG & DROP UPLOAD ── */
document.querySelectorAll('.upload-zone').forEach(zone => {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => { e.preventDefault(); zone.classList.remove('drag-over'); });
});

/* ── TOAST NOTIFICATIONS ── */
function showToast(msg, type='info', duration=3200) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = 'position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none';
    document.body.appendChild(container);
  }
  const colors = {success:'#059669',danger:'#dc2626',warning:'#d97706',info:'#4f46e5'};
  const icons = {success:'✓',danger:'✕',warning:'⚠',info:'ℹ'};
  const t = document.createElement('div');
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const bgColor = isDark ? '#1e293b' : '#fff';
  const txColor = isDark ? '#e2e8f0' : '#0f172a';
  t.style.cssText = `background:${bgColor};border-left:4px solid ${colors[type]||colors.info};color:${txColor};padding:12px 18px 12px 14px;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.14),0 2px 8px rgba(0,0,0,.08);font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px;pointer-events:auto;max-width:340px;animation:fadeInRight .28s cubic-bezier(.4,0,.2,1);font-family:Inter,system-ui,sans-serif`;
  t.innerHTML = `<span style="font-size:16px;color:${colors[type]||colors.info}">${icons[type]||icons.info}</span><span>${msg}</span>`;
  container.appendChild(t);
  setTimeout(() => {
    t.style.animation = 'fadeInRight .28s cubic-bezier(.4,0,.2,1) reverse';
    t.addEventListener('animationend', () => t.remove());
  }, duration);
}

/* ── ANIMATED COUNTER ── */
function animateCounter(el, target, duration=900) {
  const start = performance.now();
  const from = parseInt(el.textContent) || 0;
  function step(now) {
    const p = Math.min((now - start) / duration, 1);
    const ease = 1 - Math.pow(1 - p, 3);
    el.textContent = Math.round(from + (target - from) * ease);
    if (p < 1) requestAnimationFrame(step);
    else el.textContent = target;
  }
  requestAnimationFrame(step);
}

/* ── STAGGER ANIMATE LIST ── */
function staggerAnimate(els, baseDelay=40) {
  els.forEach((el, i) => {
    el.style.animation = 'none';
    el.style.opacity = '0';
    setTimeout(() => {
      el.style.animation = `fadeInUp .28s cubic-bezier(.4,0,.2,1) both`;
      el.style.opacity = '';
    }, i * baseDelay);
  });
}

async function doLogin() {
  const email = document.getElementById('login-email').value.trim();
  const pass  = document.getElementById('login-pass').value.trim();
  const btn   = document.getElementById('login-btn');
  const err   = document.getElementById('login-error');
  err.style.display = 'none';
  btn.disabled = true; btn.textContent = 'Logging in…';
  try {
    const r = await fetch('api/auth.php?action=login', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({email, password: pass})
    });
    const d = await r.json();
    if (d.success) {
      currentUser = d.user;
      const lp = document.getElementById('login-page');
      lp.style.transition = 'opacity .35s ease';
      lp.style.opacity = '0';
      setTimeout(() => {
        lp.style.display = 'none';
        lp.style.opacity = '';
        document.getElementById('main-app').style.display = '';
        initApp();
      }, 350);
    } else {
      err.textContent = d.error || 'Login failed.';
      err.style.display = 'block';
      err.style.animation = 'none';
      requestAnimationFrame(() => { err.style.animation = 'fadeInDown .25s ease'; });
    }
  } catch(e) {
    err.textContent = 'Server error. Is the web server running?';
    err.style.display = 'block';
  }
  btn.disabled = false; btn.innerHTML = t('login.btn');
}

// ═══════════════════════════════════════════════════════════════
// BILINGUAL SYSTEM — English / Hindi
// ═══════════════════════════════════════════════════════════════
let lang = localStorage.getItem('ifqm-lang') || 'en';
const TRANSLATIONS = {
  en: {
    'app.name':'IdeaTool',
    'nav.dashboard':'Dashboard','nav.my_ideas':'My Ideas','nav.submit':'Submit Idea',
    'nav.review':'Review Queue','nav.all_ideas':'All Ideas','nav.audit':'Audit Trail',
    'nav.leaderboard':'Leaderboard','nav.analytics':'Analytics','nav.admin':'Admin Panel',
    'nav.super_admin':'Org Hierarchy','nav.profile':'My Profile',
    'section.main':'Main','section.workflow':'Workflow','section.insights':'Insights',
    'section.admin':'Admin','section.super_admin':'IFQM Super Admin',
    'login.app_title':'Employee Ideation Tool',
    'login.tagline':'Turn great ideas into real improvements.',
    'login.welcome':'Welcome back','login.subtitle':'Sign in to your IFQM account to continue',
    'login.email':'Email Address','login.password':'Password','login.btn':'Sign In',
    'login.feat1_title':'Submit & Track Ideas','login.feat1_sub':'5-step wizard with AI quality scoring',
    'login.feat2_title':'Earn Points & Rewards',
    'login.feat3_title':'Analytics & Leaderboard','login.feat3_sub':'Real-time insights across departments',
    'topbar.dark':'Dark','topbar.light':'Light','topbar.notifications':'Notifications',
    'topbar.logout':'Logout','topbar.mark_read':'Mark all read',
    'dash.total':'Total Ideas','dash.approved':'Approved','dash.implemented':'Implemented',
    'dash.status_dist':'Status Distribution','dash.recent':'Recent Activity',
    'status.submitted':'Submitted','status.review':'Under Review','status.approved':'Approved',
    'status.rejected':'Rejected','status.implemented':'Implemented','status.draft':'Draft',
    'idea.view':'View','idea.review':'Review','idea.votes':'votes',
    'vote.title':'Community Rating','vote.your_rating':'Your Rating',
    'vote.avg':'Avg','vote.engagement_idx':'Engagement Index',
    'vote.no_self':'You cannot rate your own idea','vote.submit':'Submit Rating',
    'vote.rated':'Rated','vote.stars':'stars',
    'lb.individual':'Individual Rankings','lb.dept':'Department Rankings',
    'lb.top_ideas':'Top Scored Ideas','lb.points':'pts','lb.ideas':'ideas',
    'lb.avg_score':'Avg Score','lb.engagement':'Engagement',
    'form.save_draft':'Save Draft','form.next':'Next','form.back':'Back',
    'form.submit_idea':'Submit New Idea',
    'detail.submitted_by':'Submitted by','detail.situation':'Present Situation',
    'detail.solution':'Proposed Solution','detail.impact_areas':'Impact Areas',
    'detail.impact_level':'Impact Level','detail.tangible':'Tangible Benefit',
    'detail.intangible':'Intangible Benefit','detail.co_suggesters':'Co-Suggesters',
    'detail.ai_eval':'AI Evaluation','detail.score':'Score','detail.close':'Close',
    'profile.title':'Employee Profile','profile.stats':'My Stats',
  },
  hi: {
    'app.name':'आइडियाटूल',
    'nav.dashboard':'डैशबोर्ड','nav.my_ideas':'मेरे विचार','nav.submit':'विचार प्रस्तुत करें',
    'nav.review':'समीक्षा सूची','nav.all_ideas':'सभी विचार','nav.audit':'ऑडिट ट्रेल',
    'nav.leaderboard':'लीडरबोर्ड','nav.analytics':'विश्लेषण','nav.admin':'एडमिन पैनल',
    'nav.super_admin':'संगठन संरचना','nav.profile':'मेरी प्रोफ़ाइल',
    'section.main':'मुख्य','section.workflow':'वर्कफ़्लो','section.insights':'अंतर्दृष्टि',
    'section.admin':'एडमिन','section.super_admin':'IFQM सुपर एडमिन',
    'login.app_title':'कर्मचारी विचार मंच',
    'login.tagline':'महान विचारों को वास्तविक सुधारों में बदलें।',
    'login.welcome':'वापस स्वागत है','login.subtitle':'जारी रखने के लिए अपने IFQM खाते में साइन इन करें',
    'login.email':'ईमेल पता','login.password':'पासवर्ड','login.btn':'साइन इन',
    'login.feat1_title':'विचार सबमिट और ट्रैक करें','login.feat1_sub':'एआई स्कोरिंग सहित 5-चरण विज़ार्ड',
    'login.feat2_title':'अंक और पुरस्कार अर्जित करें',
    'login.feat3_title':'विश्लेषण और लीडरबोर्ड','login.feat3_sub':'विभागों में रीयल-टाइम जानकारी',
    'topbar.dark':'डार्क','topbar.light':'लाइट','topbar.notifications':'सूचनाएं',
    'topbar.logout':'लॉगआउट','topbar.mark_read':'सभी पढ़ा हुआ चिह्नित करें',
    'dash.total':'कुल विचार','dash.approved':'स्वीकृत','dash.implemented':'लागू किए गए',
    'dash.status_dist':'स्थिति वितरण','dash.recent':'हालिया गतिविधि',
    'status.submitted':'सबमिट किया गया','status.review':'समीक्षाधीन','status.approved':'स्वीकृत',
    'status.rejected':'अस्वीकृत','status.implemented':'लागू किया गया','status.draft':'मसौदा',
    'idea.view':'देखें','idea.review':'समीक्षा','idea.votes':'वोट',
    'vote.title':'सामुदायिक रेटिंग','vote.your_rating':'आपकी रेटिंग',
    'vote.avg':'औसत','vote.engagement_idx':'सहभागिता सूचकांक',
    'vote.no_self':'आप अपने विचार को रेट नहीं कर सकते','vote.submit':'रेटिंग सबमिट करें',
    'vote.rated':'रेट किया','vote.stars':'सितारे',
    'lb.individual':'व्यक्तिगत रैंकिंग','lb.dept':'विभाग रैंकिंग',
    'lb.top_ideas':'शीर्ष स्कोर विचार','lb.points':'अंक','lb.ideas':'विचार',
    'lb.avg_score':'औसत स्कोर','lb.engagement':'सहभागिता',
    'form.save_draft':'मसौदा सहेजें','form.next':'अगला','form.back':'वापस',
    'form.submit_idea':'नया विचार सबमिट करें',
    'detail.submitted_by':'द्वारा सबमिट','detail.situation':'वर्तमान स्थिति',
    'detail.solution':'प्रस्तावित समाधान','detail.impact_areas':'प्रभाव क्षेत्र',
    'detail.impact_level':'प्रभाव स्तर','detail.tangible':'मूर्त लाभ',
    'detail.intangible':'अमूर्त लाभ','detail.co_suggesters':'सह-सुझावकर्ता',
    'detail.ai_eval':'एआई मूल्यांकन','detail.score':'स्कोर','detail.close':'बंद करें',
    'profile.title':'कर्मचारी प्रोफ़ाइल','profile.stats':'मेरे आँकड़े',
  }
};

function t(key) {
  return (TRANSLATIONS[lang] && TRANSLATIONS[lang][key]) || TRANSLATIONS.en[key] || key;
}
function applyTranslations() {
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const k = el.dataset.i18n;
    const v = t(k);
    if (el.tagName === 'INPUT' || el.tagName === 'BUTTON') el.value = v;
    else el.textContent = v;
  });
  document.getElementById('lang-btn').textContent = lang === 'en' ? 'हि' : 'EN';
  document.getElementById('dm-label').textContent = document.getElementById('dm-track').classList.contains('on') ? t('topbar.light') : t('topbar.dark');
}
function toggleLang() {
  lang = lang === 'en' ? 'hi' : 'en';
  localStorage.setItem('ifqm-lang', lang);
  applyTranslations();
}
function getPageTitle(page) {
  const m = {
    dashboard: t('nav.dashboard'), 'my-ideas': t('nav.my_ideas'),
    submit: t('form.submit_idea'), review: t('nav.review'),
    'ideas-all': t('nav.all_ideas'), audit: t('nav.audit'),
    leaderboard: t('nav.leaderboard'), analytics: t('nav.analytics'),
    admin: t('nav.admin'), 'super-admin': t('nav.super_admin'), profile: t('nav.profile'),
  };
  return m[page] || page;
}

// ═══════════════════════════════════════════════════════════════
// ENGAGEMENT INDEX & VOTING
// ═══════════════════════════════════════════════════════════════
function engagementIndex(ai_score, avg_rating, vote_count) {
  const a = (parseFloat(avg_rating) || 0) * 20;
  const v = Math.min(parseInt(vote_count) || 0, 20) / 20 * 100;
  return Math.round(a * 0.4 + v * 0.3 + (parseInt(ai_score) || 0) * 0.3);
}
function engBadge(ai, avg, vc) {
  const idx = engagementIndex(ai, avg, vc);
  if (!idx) return '';
  const col = idx >= 70 ? '#059669' : idx >= 40 ? '#d97706' : '#94a3b8';
  return `<span class="eng-badge" style="background:${col}18;color:${col};border-color:${col}44" title="${t('vote.engagement_idx')}">&#9889; ${idx}</span>`;
}
function engMiniStats(avg_rating, vote_count) {
  if (!parseInt(vote_count)) return '';
  const stars = parseFloat(avg_rating) ? `<span style="color:#d97706;font-weight:700;font-size:12px">${avg_rating}&#9733;</span>` : '';
  return `${stars}<span style="font-size:11px;color:var(--subtle)">${vote_count} ${t('idea.votes')}</span>`;
}
function starWidget(ideaId, isSelf, userRating) {
  const stars = [1,2,3,4,5].map(n => {
    const active = n <= (userRating || 0) ? ' active' : '';
    const handlers = isSelf ? '' :
      ` onclick="event.stopPropagation();castVote(${ideaId},${n})" onmouseenter="hoverStar(this)" onmouseleave="unhoverStar(this)"`;
    return `<span class="star${active}" data-n="${n}"${handlers}>&#9733;</span>`;
  }).join('');
  return `<div class="star-rating${isSelf ? ' readonly' : ''}" id="stars-${ideaId}">${stars}</div>`;
}
async function castVote(ideaId, rating) {
  const r = await fetch('api/votes.php?action=vote', {
    method:'POST', headers:{'Content-Type':'application/json'},
    credentials:'same-origin', body: JSON.stringify({idea_id:ideaId, rating})
  });
  const d = await r.json();
  if (d.success) {
    updateStarWidget(ideaId, d.user_rating);
    const voteInfoEl = document.getElementById('vote-info-' + ideaId);
    if (voteInfoEl) voteInfoEl.innerHTML = engMiniStats(d.avg_rating, d.vote_count);
    const engEl = document.getElementById('eng-' + ideaId);
    if (engEl) engEl.outerHTML = engBadge(engEl.dataset.ai || 0, d.avg_rating, d.vote_count);
    showToast(`${t('vote.rated')} ${rating} ${t('vote.stars')} — thank you!`, 'success');
  } else {
    showToast(d.error || 'Vote failed.', 'danger');
  }
}
function updateStarWidget(ideaId, userRating) {
  const container = document.getElementById('stars-' + ideaId);
  if (!container) return;
  container.querySelectorAll('.star').forEach(s =>
    s.classList.toggle('active', parseInt(s.dataset.n) <= (userRating || 0))
  );
}
function hoverStar(el) {
  const n = parseInt(el.dataset.n);
  el.closest('.star-rating').querySelectorAll('.star').forEach(s =>
    s.classList.toggle('hover', parseInt(s.dataset.n) <= n)
  );
}
function unhoverStar(el) {
  el.closest('.star-rating').querySelectorAll('.star').forEach(s => s.classList.remove('hover'));
}

// ── COMMITTEE / MULTI-REVIEWER WORKFLOW ────────────────────────────
let pendingAssignIdeaId = null;
let assignedReviewers   = {};

function openAssignReviewersModal(ideaId, ideaCode) {
  pendingAssignIdeaId = ideaId;
  assignedReviewers   = {};
  document.getElementById('ar-idea-code').textContent = '#' + ideaCode;
  document.getElementById('ar-search').value = '';
  document.getElementById('ar-results').style.display = 'none';
  document.getElementById('ar-selected-list').innerHTML = '<div style="font-size:12px;color:var(--subtle);padding:6px 0">No reviewers added yet.</div>';
  document.getElementById('ar-threshold').value = '100';
  const btn = document.getElementById('ar-submit-btn');
  btn.disabled = false; btn.textContent = 'Route to Committee';
  document.getElementById('modal-assign-reviewers').classList.add('open');
}

let _arTimer = null;
function searchReviewersForAssign(q) {
  clearTimeout(_arTimer);
  const res = document.getElementById('ar-results');
  if (q.length < 2) { res.style.display = 'none'; return; }
  _arTimer = setTimeout(async () => {
    const r = await fetch('api/users.php?action=list&q=' + encodeURIComponent(q), {credentials:'same-origin'});
    const d = await r.json();
    if (!d.users?.length) { res.style.display = 'none'; return; }
    res.innerHTML = (d.users||[]).map(u =>
      `<div class="uitem" onclick='addReviewerToAssign(${u.id},${JSON.stringify(u.name)},${JSON.stringify(u.department||"")},${JSON.stringify(u.avatar_initials||u.name[0])},${JSON.stringify(u.role)})'>
        <strong>${escHtml(u.name)}</strong>
        <span style="color:var(--subtle);font-size:11px"> · ${escHtml(u.department||'')} · ${formatRole(u.role)}</span>
      </div>`
    ).join('');
    res.style.display = 'block';
  }, 280);
}

function addReviewerToAssign(id, name, dept, initials, role) {
  if (assignedReviewers[id]) return;
  assignedReviewers[id] = {id, name, dept, initials, role};
  document.getElementById('ar-results').style.display = 'none';
  document.getElementById('ar-search').value = '';
  renderAssignedReviewers();
}

function removeReviewerFromAssign(id) {
  delete assignedReviewers[id];
  renderAssignedReviewers();
}

function renderAssignedReviewers() {
  const el = document.getElementById('ar-selected-list');
  const list = Object.values(assignedReviewers);
  if (!list.length) {
    el.innerHTML = '<div style="font-size:12px;color:var(--subtle);padding:6px 0">No reviewers added yet.</div>';
    return;
  }
  el.innerHTML = `
    <div style="font-size:11px;font-weight:700;color:var(--label);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px">${list.length} Reviewer${list.length>1?'s':''} Selected</div>
    ${list.map(u=>`
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border)">
        <div class="avatar" style="width:28px;height:28px;font-size:10px;flex-shrink:0">${escHtml(u.initials)}</div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600">${escHtml(u.name)}</div>
          <div style="font-size:11px;color:var(--subtle)">${escHtml(u.dept)} · ${formatRole(u.role)}</div>
        </div>
        <button class="btn btn-outline btn-sm" style="padding:3px 9px;line-height:1" onclick="removeReviewerFromAssign(${u.id})">&#10005;</button>
      </div>`).join('')}`;
}

async function submitAssignReviewers() {
  const reviewerIds = Object.keys(assignedReviewers).map(Number);
  if (!reviewerIds.length) { showToast('Add at least one reviewer.', 'warning'); return; }
  const threshold = parseInt(document.getElementById('ar-threshold').value);
  const btn = document.getElementById('ar-submit-btn');
  btn.disabled = true; btn.textContent = 'Routing…';
  try {
    const r = await fetch('api/ideas.php?action=assign_reviewers', {
      method:'POST', headers:{'Content-Type':'application/json'},
      credentials:'same-origin',
      body: JSON.stringify({idea_id: pendingAssignIdeaId, reviewer_ids: reviewerIds, threshold})
    });
    const d = await r.json();
    if (d.success) {
      closeModal('modal-assign-reviewers');
      showToast(`Routed to committee of ${d.reviewer_count} reviewer${d.reviewer_count>1?'s':''}. They have been notified.`, 'success');
      loadReviewQueue();
    } else {
      showToast(d.error || 'Failed to assign reviewers.', 'danger');
      btn.disabled = false; btn.textContent = 'Route to Committee';
    }
  } catch(e) {
    showToast('Network error.', 'danger');
    btn.disabled = false; btn.textContent = 'Route to Committee';
  }
}

function openReviewerDecisionModal(ideaId, ideaCode) {
  pendingIdeaId = ideaId;
  document.getElementById('rd-idea-code').textContent = '#' + ideaCode;
  document.getElementById('rd-decision').value = '';
  document.getElementById('rd-comment').value = '';
  document.getElementById('rd-approve-btn').className = 'btn btn-outline';
  document.getElementById('rd-approve-btn').style.flex = '1';
  document.getElementById('rd-approve-btn').style.padding = '10px';
  document.getElementById('rd-approve-btn').style.fontSize = '14px';
  document.getElementById('rd-reject-btn').className = 'btn btn-outline';
  document.getElementById('rd-reject-btn').style.flex = '1';
  document.getElementById('rd-reject-btn').style.padding = '10px';
  document.getElementById('rd-reject-btn').style.fontSize = '14px';
  document.getElementById('rd-submit-btn').disabled = true;
  document.getElementById('modal-reviewer-decision').classList.add('open');
}

function selectReviewerDecision(dec) {
  document.getElementById('rd-decision').value = dec;
  const ab = document.getElementById('rd-approve-btn');
  const rb = document.getElementById('rd-reject-btn');
  ab.className = dec==='approved' ? 'btn btn-success' : 'btn btn-outline';
  rb.className = dec==='rejected' ? 'btn btn-danger'  : 'btn btn-outline';
  ab.style.flex='1'; ab.style.padding='10px'; ab.style.fontSize='14px';
  rb.style.flex='1'; rb.style.padding='10px'; rb.style.fontSize='14px';
  document.getElementById('rd-submit-btn').disabled = false;
}

async function submitReviewerDecision() {
  const decision = document.getElementById('rd-decision').value;
  const comment  = document.getElementById('rd-comment').value;
  if (!decision) { showToast('Please select Approve or Reject.', 'warning'); return; }
  const label = decision === 'approved' ? 'Approve' : 'Reject';
  if (!confirm(`Confirm: ${label} this idea? Your decision is final and will be recorded in the audit trail.`)) return;
  const btn = document.getElementById('rd-submit-btn');
  btn.disabled = true; btn.textContent = 'Submitting…';
  try {
    const r = await fetch('api/ideas.php?action=reviewer_decision', {
      method:'POST', headers:{'Content-Type':'application/json'},
      credentials:'same-origin',
      body: JSON.stringify({idea_id: pendingIdeaId, decision, comment})
    });
    const d = await r.json();
    if (d.success) {
      closeModal('modal-reviewer-decision');
      const statusPart = d.new_status
        ? ` — idea is now <strong>${d.new_status}</strong> (${d.approved}/${d.total} approved)`
        : ` — ${d.pending} reviewer${d.pending!==1?'s':''} still pending`;
      showToast('Decision recorded' + statusPart, 'success');
      loadReviewQueue();
      loadNotifications();
    } else {
      showToast(d.error || 'Failed to submit decision.', 'danger');
      btn.disabled = false; btn.textContent = 'Submit Decision';
    }
  } catch(e) {
    showToast('Network error.', 'danger');
    btn.disabled = false; btn.textContent = 'Submit Decision';
  }
}

async function doLogout() {
  await fetch('api/auth.php?action=logout', {method:'POST'});
  currentUser = null;
  const app = document.getElementById('main-app');
  app.style.transition = 'opacity .3s ease';
  app.style.opacity = '0';
  setTimeout(() => {
    app.style.display = 'none';
    app.style.opacity = '';
    const lp = document.getElementById('login-page');
    lp.style.display = 'flex';
    lp.style.opacity = '0';
    lp.style.transition = 'opacity .35s ease';
    requestAnimationFrame(() => { lp.style.opacity = '1'; });
    setTimeout(() => { lp.style.transition = ''; lp.style.opacity = ''; }, 400);
  }, 300);
}

function initApp() {
  if (!currentUser) return;
  const u = currentUser;

  document.getElementById('sb-avatar').textContent  = u.avatar_initials || u.name[0];
  document.getElementById('sb-name').textContent    = u.name;
  document.getElementById('sb-role').textContent    = formatRole(u.role);
  document.getElementById('sb-points').textContent  = u.points + ' pts';
  document.getElementById('top-avatar').textContent = u.avatar_initials || u.name[0];
  document.getElementById('top-name').textContent   = u.name;
  document.getElementById('top-role').textContent   = formatRole(u.role);

  document.getElementById('submit-user-banner').innerHTML =
    `Auto-fetched from HR Database: <strong>${u.name}</strong> &middot; ${u.employee_id} &middot; ${u.department || '–'} &middot; Reporting to: ${u.manager_name || '–'} &middot; ${u.business_unit || '–'}`;

  const isPriv       = ['manager','admin','executive','super_admin'].includes(u.role);
  const isAdmin      = u.role === 'admin';
  const isSuperAdmin = u.role === 'super_admin';

  document.getElementById('nav-my-ideas').style.display           = isSuperAdmin ? 'none' : '';
  document.getElementById('nav-submit').style.display             = isSuperAdmin ? 'none' : '';
  document.getElementById('nav-review').style.display             = isPriv ? '' : 'none';
  document.getElementById('nav-analytics').style.display          = isPriv ? '' : 'none';
  document.getElementById('nav-audit').style.display              = isPriv ? '' : 'none';
  document.getElementById('nav-admin').style.display              = isAdmin ? '' : 'none';
  document.getElementById('nav-section-admin').style.display      = isAdmin ? '' : 'none';
  document.getElementById('nav-super-admin').style.display        = isSuperAdmin ? '' : 'none';
  document.getElementById('nav-section-super-admin').style.display= isSuperAdmin ? '' : 'none';

  if (isSuperAdmin) {
    document.getElementById('sa-session-user').textContent = u.name;
    document.getElementById('sb-points').style.display = 'none';
  }

  loadNotifications();
  applyTranslations();
  if (isSuperAdmin) {
    navigate('super-admin', document.getElementById('nav-super-admin'));
  } else {
    loadDashboard();
    loadMyIdeas();
  }
}

const pageTitles = {
  dashboard:'Dashboard', 'my-ideas':'My Ideas', submit:'Submit New Idea',
  review:'Review Queue', 'ideas-all':'All Ideas', audit:'Audit Trail',
  leaderboard:'Leaderboard & Gamification', analytics:'Analytics Dashboard',
  admin:'Admin Panel', 'super-admin':'Command Center', profile:'My Profile'
};

function navigate(page, navEl) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const el = document.getElementById('page-' + page);
  if (el) el.classList.add('active');
  if (navEl) navEl.classList.add('active');
  document.getElementById('page-title').textContent = getPageTitle(page);
  document.getElementById('notif-panel').classList.remove('open');
  document.getElementById('content').scrollTo({ top: 0, behavior: 'smooth' });

  if (page === 'dashboard')   loadDashboard();
  if (page === 'my-ideas')    loadMyIdeas();
  if (page === 'review')      loadReviewQueue();
  if (page === 'ideas-all')   loadAllIdeas();
  if (page === 'audit')       loadAudit();
  if (page === 'leaderboard') loadLeaderboard();
  if (page === 'analytics')   loadAnalytics();
  if (page === 'admin')       loadAdminUsers();
  if (page === 'super-admin') loadHierarchy();
  if (page === 'profile')     renderProfile();
  if (page === 'submit')      resetWizard();
}

function toggleDarkMode() {
  const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
  const next = isDark ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('ifqm-theme', next);
  const track = document.getElementById('dm-track');
  const label = document.getElementById('dm-label');
  if (next === 'dark') { track.classList.add('on'); label.textContent = 'Light'; }
  else { track.classList.remove('on'); label.textContent = 'Dark'; }
  applyTranslations();
}
// Sync toggle button state with saved theme
document.addEventListener('DOMContentLoaded', function() {
  if (localStorage.getItem('ifqm-theme') === 'dark') {
    const t = document.getElementById('dm-track');
    const l = document.getElementById('dm-label');
    if (t) t.classList.add('on');
    if (l) l.textContent = 'Light';
  }
});

function toggleSidebar() { document.getElementById('sidebar').classList.toggle('collapsed'); }

function toggleNotif() {
  document.getElementById('notif-panel').classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.topbar-right'))
    document.getElementById('notif-panel').classList.remove('open');
});

async function loadDashboard() {
  let r, d;
  try {
    r = await fetch('api/ideas.php?action=dashboard', {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    document.getElementById('dash-kpis').innerHTML = '<div class="alert alert-danger" style="grid-column:1/-1">Failed to load dashboard. Is the server running?</div>';
    return;
  }
  if (!d.success) return;

  const counts = d.counts;
  const total  = Object.values(counts).reduce((a,b)=>a+b,0);
  document.getElementById('dash-kpis').innerHTML = `
    <div class="kpi-card" style="border-left-color:#4f46e5">
      <div class="kpi-icon" style="background:#eef2ff;color:#4f46e5"><svg viewBox="0 0 24 24"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${total}">0</div><div class="kpi-label" data-i18n="dash.total">Total Ideas</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#d97706">
      <div class="kpi-icon" style="background:#fef3c7;color:#d97706"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${counts['Under Review']||0}">0</div><div class="kpi-label" data-i18n="dash.review">Under Review</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#059669">
      <div class="kpi-icon" style="background:#dcfce7;color:#059669"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${counts['Approved']||0}">0</div><div class="kpi-label" data-i18n="dash.approved">Approved</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#7c3aed">
      <div class="kpi-icon" style="background:#f3e8ff;color:#7c3aed"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${counts['Implemented']||0}">0</div><div class="kpi-label" data-i18n="dash.implemented">Implemented</div></div>
    </div>
  `;
  document.querySelectorAll('#dash-kpis .kpi-val[data-target]').forEach(el => {
    animateCounter(el, parseInt(el.dataset.target), 900);
  });

  const statusColors = {'Submitted':'#4f46e5','Under Review':'#d97706','Approved':'#059669','Rejected':'#dc2626','Implemented':'#7c3aed'};
  const maxCount = Math.max(...Object.values(counts), 1);
  document.getElementById('dash-status-chart').innerHTML =
    Object.entries(counts).map(([s,c]) => `
      <div class="bar-row">
        <span class="bar-label">${s}</span>
        <div class="bar-track"><div class="bar-fill" style="width:0%;background:${statusColors[s]||'#ccc'}" data-w="${Math.round(c/maxCount*100)}"></div></div>
        <span class="bar-val">${c}</span>
      </div>`).join('');
  setTimeout(() => {
    document.querySelectorAll('#dash-status-chart .bar-fill[data-w]').forEach((bar, i) => {
      setTimeout(() => {
        bar.style.transition = 'width .7s cubic-bezier(.4,0,.2,1)';
        bar.style.width = bar.dataset.w + '%';
      }, i * 80);
    });
  }, 150);

  const actEl = document.getElementById('dash-activity');
  actEl.innerHTML = d.recent.length
    ? d.recent.map(r => `
        <div class="tl-item">
          <div class="tl-dot tl-dot-blue">${actionLabel(r.action)}</div>
          <div>
            <div class="tl-title">${r.idea_code} — ${r.action}</div>
            <div class="tl-meta">${r.actor_name} · ${timeAgo(r.created_at)}</div>
            ${r.comment ? `<div class="tl-comment">${escHtml(r.comment)}</div>` : ''}
          </div>
        </div>`).join('')
    : '<div class="empty-state">No activity yet</div>';
  staggerAnimate([...actEl.querySelectorAll('.tl-item')], 70);
}

async function loadMyIdeas() {
  let r, d;
  try {
    r = await fetch('api/ideas.php?action=my', {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    document.getElementById('my-ideas-list').innerHTML = '<div class="alert alert-danger">Failed to load ideas. Check server.</div>';
    return;
  }
  allMyIdeas = d.ideas || [];
  renderMyIdeas(allMyIdeas);
}

function filterMyIdeas() {
  const q  = document.getElementById('my-search').value.toLowerCase();
  const st = document.getElementById('my-status-filter').value;
  renderMyIdeas(allMyIdeas.filter(i =>
    (i.title.toLowerCase().includes(q) || i.idea_code.toLowerCase().includes(q)) &&
    (!st || i.status === st)
  ));
}

function renderMyIdeas(ideas) {
  const el = document.getElementById('my-ideas-list');
  if (!ideas.length) { el.innerHTML = '<div class="empty-state">No ideas found. Submit your first idea!</div>'; return; }
  el.innerHTML = ideas.map(i => `
    <div class="idea-card" data-status="${i.status}" onclick="openIdeaDetail(${i.id})">
      <div class="idea-card-header">
        <div>
          <div class="idea-card-id">#${i.idea_code}</div>
          <div class="idea-card-title">${escHtml(i.title)}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
          <span class="badge ${statusBadge(i.status)}">${i.status}</span>
          ${i.ai_score > 0 ? `<span class="${scoreBadgeClass(i.ai_score)}">${i.ai_score}/100</span>` : ''}
          ${i.status !== 'Draft' ? engBadge(i.ai_score, i.avg_rating, i.vote_count) : ''}
        </div>
      </div>
      <div class="idea-card-meta">${i.impact_areas || '—'} · ${i.submitted_at ? fmtDate(i.submitted_at) : 'Draft'}</div>
      ${i.status !== 'Draft' ? `<div style="margin-top:4px">${engMiniStats(i.avg_rating, i.vote_count)}</div>` : ''}
      <div class="idea-card-footer">
        <span class="badge ${impactBadge(i.impact_level)}">${i.impact_level||'–'} Impact</span>
        <div style="display:flex;gap:8px;align-items:center">
          ${i.points_awarded ? `<span class="points-badge">+${i.points_awarded} pts</span>` : ''}
          <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();openIdeaDetail(${i.id})">View</button>
        </div>
      </div>
    </div>`).join('');
  staggerAnimate([...el.querySelectorAll('.idea-card')], 55);
}

async function loadAllIdeas() {
  const s  = document.getElementById('all-search').value;
  const st = document.getElementById('all-status').value;
  const im = document.getElementById('all-impact').value;
  const p  = new URLSearchParams({action:'list'});
  if (s)  p.append('search', s);
  if (st) p.append('status', st);
  if (im) p.append('impact', im);
  const tbody = document.getElementById('all-ideas-tbody');
  tbody.innerHTML = '<tr><td colspan="10" class="text-center"><div class="spinner"></div></td></tr>';
  let r, d;
  try {
    r = await fetch('api/ideas.php?' + p, {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    tbody.innerHTML = '<tr><td colspan="10" class="text-center"><div class="alert alert-danger">Failed to load ideas.</div></td></tr>';
    return;
  }
  tbody.innerHTML = (d.ideas||[]).map(i => `
    <tr>
      <td><strong>${i.idea_code}</strong></td>
      <td title="${escHtml(i.title)}">${i.title.length > 60 ? escHtml(i.title).substring(0,60) + '…' : escHtml(i.title)}</td>
      <td>${escHtml(i.submitter_name)}</td>
      <td>${i.department||'–'}</td>
      <td><span class="badge ${impactBadge(i.impact_level)}">${i.impact_level||'–'}</span></td>
      <td>${i.ai_score > 0 ? `<span class="${scoreBadgeClass(i.ai_score)}">${i.ai_score}/100</span>` : '<span class="score-none">—</span>'}</td>
      <td>${engMiniStats(i.avg_rating, i.vote_count)}</td>
      <td><span class="badge ${statusBadge(i.status)}">${i.status}</span></td>
      <td>${i.submitted_at ? fmtDate(i.submitted_at) : '–'}</td>
      <td><button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View</button></td>
    </tr>`).join('') || '<tr><td colspan="10" class="text-center">No ideas found.</td></tr>';
  staggerAnimate([...tbody.querySelectorAll('tr')], 40);
}

async function openIdeaDetail(id) {
  pendingIdeaId = id;
  document.getElementById('modal-idea-code').textContent = 'Loading…';
  document.getElementById('modal-idea-title-sub').textContent = '';
  document.getElementById('modal-detail-body').innerHTML = '<div class="spinner"></div>';
  document.getElementById('modal-timeline').innerHTML    = '<div class="spinner"></div>';
  document.getElementById('modal-attachments').innerHTML = '<div class="spinner"></div>';

  document.querySelectorAll('#modal-idea-detail .tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('#modal-idea-detail .tab').forEach(t => t.classList.remove('active'));
  document.getElementById('dtab1').classList.add('active');
  document.querySelectorAll('#modal-idea-detail .tab')[0].classList.add('active');

  document.getElementById('modal-idea-detail').classList.add('open');

  const r = await fetch('api/ideas.php?action=get&id=' + id, {credentials:'same-origin'});
  const d = await r.json();
  if (!d.success) { document.getElementById('modal-detail-body').textContent = 'Error loading idea.'; return; }
  const idea = d.idea;

  document.getElementById('modal-idea-code').textContent      = '#' + idea.idea_code;
  document.getElementById('modal-idea-title-sub').textContent = idea.title;

  const aiReason  = (idea.ai_reason  && idea.ai_reason.trim())
                    ? escHtml(idea.ai_reason)
                    : 'No AI evaluation available.';
  const aiScoreBadge = idea.ai_score > 0
                    ? `<span class="${scoreBadgeClass(idea.ai_score)}">${idea.ai_score}/100</span>`
                    : '';

  document.getElementById('modal-detail-body').innerHTML = `
    <div class="form-row" style="margin-bottom:12px">
      <div><strong>Submitted by:</strong> ${escHtml(idea.submitter_name)} (${idea.department||'–'})</div>
      <div style="display:flex;align-items:center;gap:8px">
        <strong>Status:</strong> <span class="badge ${statusBadge(idea.status)}">${idea.status}</span>
      </div>
    </div>

    <div class="form-group"><label>Present Situation</label>
      <div style="background:#f8f9fe;padding:10px;border-radius:6px;font-size:13px">${escHtml(idea.present_situation)}</div>
    </div>
    <div class="form-group"><label>Proposed Solution</label>
      <div style="background:#f8f9fe;padding:10px;border-radius:6px;font-size:13px">${escHtml(idea.proposed_solution)}</div>
    </div>
    <div class="form-row" style="margin-bottom:10px">
      <div><strong>Impact Areas:</strong> ${idea.impact_areas||'–'}</div>
      <div><strong>Impact Level:</strong> <span class="badge ${impactBadge(idea.impact_level)}">${idea.impact_level||'–'}</span></div>
    </div>
    ${idea.tangible_benefit   ? `<div class="mt-8"><strong>Tangible Benefit:</strong> ${escHtml(idea.tangible_benefit)}</div>`   : ''}
    ${idea.intangible_benefit ? `<div class="mt-8"><strong>Intangible Benefit:</strong> ${escHtml(idea.intangible_benefit)}</div>` : ''}
    ${idea.co1_name           ? `<div class="mt-8"><strong>Co-Suggesters:</strong> ${escHtml(idea.co1_name)}${idea.co2_name ? ', ' + escHtml(idea.co2_name) : ''}</div>` : ''}

    <div class="ai-panel" style="margin-top:14px">
      <div class="ai-panel-title">AI Evaluation</div>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
        <strong style="font-size:13px">Score:</strong>
        ${aiScoreBadge || '<span class="score-badge score-none">Not scored</span>'}
      </div>
      <div style="font-size:13px;color:var(--text);line-height:1.5">${aiReason}</div>
    </div>
    <div id="community-engagement-panel" style="margin-top:14px"></div>
    ${(idea.workflow_type === 'multi_reviewer' && (idea.reviewers||[]).length > 0) ? `
    <div class="ai-panel" style="margin-top:14px;border-left-color:#0284c7;background:linear-gradient(135deg,#eff6ff,var(--panel-bg))">
      <div class="ai-panel-title" style="color:#0284c7">&#9632; Committee Review &mdash; ${idea.approval_threshold}% approval required</div>
      <div style="font-size:12px;color:var(--subtle);margin-bottom:12px">
        ${(idea.reviewers||[]).filter(r=>r.decision==='approved').length} approved &middot;
        ${(idea.reviewers||[]).filter(r=>r.decision==='rejected').length} rejected &middot;
        ${(idea.reviewers||[]).filter(r=>r.decision==='pending').length} pending
      </div>
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        ${(idea.reviewers||[]).map(rv => `
          <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:var(--r);border:1px solid var(--border);background:var(--surface);min-width:160px">
            <div class="avatar" style="width:28px;height:28px;font-size:10px;flex-shrink:0;background:${rv.decision==='approved'?'linear-gradient(135deg,#059669,#10b981)':rv.decision==='rejected'?'linear-gradient(135deg,#dc2626,#ef4444)':'linear-gradient(135deg,#94a3b8,#cbd5e1)'}">${escHtml(rv.avatar_initials||rv.reviewer_name[0])}</div>
            <div>
              <div style="font-size:12px;font-weight:600">${escHtml(rv.reviewer_name)}</div>
              <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:${rv.decision==='approved'?'#059669':rv.decision==='rejected'?'#dc2626':'#94a3b8'}">${rv.decision}</div>
              ${rv.comment ? `<div style="font-size:11px;color:var(--subtext);margin-top:2px;font-style:italic">&ldquo;${escHtml(rv.comment)}&rdquo;</div>` : ''}
            </div>
          </div>`).join('')}
      </div>
    </div>` : ''}
  `;

  // Community engagement panel + voting widget
  try {
    const vr = await fetch('api/votes.php?action=stats&idea_id=' + id, {credentials:'same-origin'});
    const vd = await vr.json();
    const isSelf = parseInt(idea.submitter_id) === parseInt(currentUser?.id);
    const vc  = vd.vote_count  || 0;
    const ar  = vd.avg_rating  || 0;
    const ur  = vd.user_rating ?? null;
    const ei  = engagementIndex(idea.ai_score, ar, vc);
    const panel = document.getElementById('community-engagement-panel');
    if (panel) {
      panel.innerHTML = `
        <div class="ai-panel" style="border-left-color:#7c3aed">
          <div class="ai-panel-title" style="color:#7c3aed">Community Engagement</div>
          <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:10px">
            <div style="text-align:center">
              <div style="font-size:20px;font-weight:700;color:#4f46e5">${vc}</div>
              <div style="font-size:11px;color:var(--subtle)">Votes</div>
            </div>
            <div style="text-align:center">
              <div style="font-size:20px;font-weight:700;color:#d97706">${ar > 0 ? ar.toFixed(1) : '—'}</div>
              <div style="font-size:11px;color:var(--subtle)">Avg Rating</div>
            </div>
            <div style="text-align:center">
              <div style="font-size:20px;font-weight:700;color:#7c3aed">${ei}</div>
              <div style="font-size:11px;color:var(--subtle)">Engagement Index</div>
            </div>
            <div style="margin-left:auto">${engBadge(idea.ai_score, ar, vc)}</div>
          </div>
          ${!isSelf && idea.status !== 'Draft' ? `
            <div style="margin-top:8px">
              <div style="font-size:12px;color:var(--subtle);margin-bottom:6px">${ur ? 'Your rating:' : 'Rate this idea:'}</div>
              ${starWidget(id, false, ur)}
            </div>` : (isSelf ? `<div style="font-size:12px;color:var(--subtle);font-style:italic">You cannot rate your own idea.</div>` : '')}
        </div>`;
    }
  } catch(e) { /* vote stats non-critical */ }

  document.getElementById('modal-timeline').innerHTML = (idea.workflow||[]).map(w => `
    <div class="tl-item">
      <div class="tl-dot tl-dot-blue">${actionLabel(w.action)}</div>
      <div>
        <div class="tl-title">${w.action}</div>
        <div class="tl-meta">${escHtml(w.actor_name)} · ${fmtDate(w.created_at)}</div>
        ${w.comment ? `<div class="tl-comment">${escHtml(w.comment)}</div>` : ''}
      </div>
    </div>`).join('') || '<div class="empty-state">No workflow history yet.</div>';

  document.getElementById('modal-attachments').innerHTML = (idea.attachments||[]).length
    ? idea.attachments.map(a => {
        const url      = 'api/uploads/' + a.filepath;
        const ext      = a.filename.split('.').pop().toLowerCase();
        const isImage  = ['png','jpg','jpeg','gif','webp'].includes(ext);
        const preview  = isImage
          ? `<div style="margin-top:8px">
               <img src="${url}" alt="${escHtml(a.filename)}"
                    style="max-width:100%;max-height:320px;border-radius:6px;border:1px solid #e0e0e0;display:block"/>
             </div>`
          : '';
        return `
          <div style="padding:10px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;justify-content:space-between">
              <div>
                <span style="font-size:12px;color:var(--subtle);text-transform:uppercase;margin-right:6px">${a.section}</span>
                <a href="${url}" target="_blank" style="font-size:13px;color:#4f46e5">${escHtml(a.filename)}</a>
              </div>
              <a href="${url}" download="${escHtml(a.filename)}" class="btn btn-outline btn-sm">Download</a>
            </div>
            ${preview}
          </div>`;
      }).join('')
    : '<div class="empty-state">No attachments.</div>';

  const isPriv    = ['manager','admin','executive','super_admin'].includes(currentUser?.role);
  const isSelf    = parseInt(idea.submitter_id) === parseInt(currentUser?.id);
  const isMultiRv = idea.workflow_type === 'multi_reviewer';
  const isAssignedReviewer = isPriv && !isSelf && (idea.reviewers||[]).some(
    rv => parseInt(rv.reviewer_id) === parseInt(currentUser?.id) && rv.decision === 'pending'
  );
  const canDirectReview  = isPriv && !isSelf && !isMultiRv && ['Submitted','Under Review'].includes(idea.status);
  const canRouteReviewers= isPriv && !isSelf && !isMultiRv && ['Submitted','Under Review'].includes(idea.status);
  const selfNote  = isPriv && isSelf && ['Submitted','Under Review'].includes(idea.status)
    ? `<span style="font-size:12px;color:#d97706;margin-right:10px">You cannot review your own idea</span>` : '';
  document.getElementById('idea-detail-footer').innerHTML = `
    <button class="btn btn-outline" onclick="closeModal('modal-idea-detail')">Close</button>
    ${selfNote}
    ${canRouteReviewers ? `<button class="btn btn-outline" style="border-color:#0284c7;color:#0284c7" onclick="closeModal('modal-idea-detail');openAssignReviewersModal(${idea.id},'${idea.idea_code}')">Route to Committee</button>` : ''}
    ${isAssignedReviewer ? `<button class="btn btn-primary" onclick="closeModal('modal-idea-detail');openReviewerDecisionModal(${idea.id},'${idea.idea_code}')">Submit My Review</button>` : ''}
    ${canDirectReview ? `<button class="btn btn-success" onclick="closeModal('modal-idea-detail');openReviewModal(${idea.id},'${idea.idea_code}')">Review / Decide</button>` : ''}
  `;
}

async function loadReviewQueue() {
  const el = document.getElementById('review-list');
  el.innerHTML = '<div class="empty-state"><div class="spinner"></div> Loading review queue…</div>';
  let r, d;
  try {
    r = await fetch('api/ideas.php?action=review', {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    el.innerHTML = '<div class="alert alert-danger">Failed to load review queue. Check server connection.</div>';
    return;
  }
  if (!d.success) {
    el.innerHTML = `<div class="alert alert-danger">${d.error||'Error loading review queue.'}</div>`;
    return;
  }
  if (!d.ideas?.length) { el.innerHTML = '<div class="empty-state">No ideas pending review.</div>'; return; }
  el.innerHTML = d.ideas.map(i => {
    const isSelf        = parseInt(i.submitter_id) === parseInt(currentUser?.id);
    const isMultiRv     = i.workflow_type === 'multi_reviewer';
    const isMyPending   = i.my_reviewer_decision === 'pending';
    const pending       = Math.max(0, (parseInt(i.reviewer_count)||0) - (parseInt(i.approved_count)||0) - (parseInt(i.rejected_count)||0));
    const committeeInfo = isMultiRv ? `
      <div style="margin-top:6px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <span style="font-size:11px;background:#eff6ff;color:#1d4ed8;padding:2px 9px;border-radius:var(--r-full);font-weight:600;border:1px solid #bfdbfe">Committee</span>
        <span style="font-size:11px;color:var(--subtle)">${i.approved_count||0} approved &middot; ${i.rejected_count||0} rejected &middot; ${pending} pending</span>
        ${isMyPending ? `<span style="font-size:11px;background:#fef3c7;color:#92400e;padding:2px 9px;border-radius:var(--r-full);font-weight:600;border:1px solid #fde68a">Your vote needed</span>` : ''}
      </div>` : '';
    const actionBtns = isSelf
      ? `<span style="font-size:11px;color:#d97706">Your own idea</span>
         <button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View</button>`
      : isMultiRv && isMyPending
      ? `<button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View</button>
         <button class="btn btn-primary btn-sm" onclick="openReviewerDecisionModal(${i.id},'${i.idea_code}')">My Review</button>`
      : isMultiRv
      ? `<button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View Details</button>`
      : `<button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View Details</button>
         <button class="btn btn-success btn-sm" onclick="openReviewModal(${i.id},'${i.idea_code}')">Review</button>`;
    return `
    <div class="idea-card" data-status="${i.status}">
      <div class="idea-card-header">
        <div>
          <div class="idea-card-id">#${i.idea_code}</div>
          <div class="idea-card-title">${escHtml(i.title)}</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px">
          <span class="badge ${statusBadge(i.status)}">${i.status}</span>
          ${i.ai_score > 0 ? `<span class="${scoreBadgeClass(i.ai_score)}">AI: ${i.ai_score}/100</span>` : ''}
        </div>
      </div>
      <div class="idea-card-meta">By ${escHtml(i.submitter_name)} · ${i.department||'–'} · ${i.submitted_at ? fmtDate(i.submitted_at) : '–'}</div>
      ${committeeInfo}
      <div style="margin-top:4px">${engMiniStats(i.avg_rating, i.vote_count)}</div>
      <div class="idea-card-footer">
        <div style="display:flex;align-items:center;gap:8px">
          <span class="badge ${impactBadge(i.impact_level)}">${i.impact_level||'–'} Impact</span>
          ${engBadge(i.ai_score, i.avg_rating, i.vote_count)}
        </div>
        <div style="display:flex;gap:8px;align-items:center">${actionBtns}</div>
      </div>
    </div>`;
  }).join('');
}

function openReviewModal(id, code) {
  pendingIdeaId = id;
  document.getElementById('review-id').textContent    = '#' + code;
  document.getElementById('review-comment').value     = '';
  document.getElementById('review-decision').value    = 'Approved';
  const submitBtn = document.querySelector('#modal-review .modal-footer .btn-primary');
  if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Decision'; }
  document.getElementById('modal-review').classList.add('open');
}

async function submitReview() {
  const decision  = document.getElementById('review-decision').value;
  const comment   = document.getElementById('review-comment').value;
  const submitBtn = document.querySelector('#modal-review .modal-footer .btn-primary');

  const label = {'Approved':'Approve','Rejected':'Reject','Implemented':'Mark as Implemented','Under Review':'Move to Under Review'}[decision] || decision;
  if (!confirm(`Confirm: ${label} this idea?\n\nThis action will be recorded in the audit trail and the submitter will be notified.`)) return;

  if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Submitting…'; }

  let r, d;
  try {
    r = await fetch('api/ideas.php?action=review_action', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      credentials: 'same-origin',
      body: JSON.stringify({idea_id: pendingIdeaId, decision, comment})
    });
    d = await r.json();
  } catch(e) {
    alert('Server error. Please try again.');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Decision'; }
    return;
  }

  closeModal('modal-review');
  if (d.success) {
    document.getElementById('success-title').textContent = 'Decision Submitted';
    document.getElementById('success-msg').textContent   = `Idea marked as "${decision}". Submitter notified.${d.points_awarded ? ' +' + d.points_awarded + ' pts awarded.' : ''}`;
    document.getElementById('modal-success').classList.add('open');
    showToast(`Decision recorded: ${decision}${d.points_awarded ? ` · +${d.points_awarded} pts` : ''}`, decision === 'Approved' || decision === 'Implemented' ? 'success' : 'info');
    loadReviewQueue();
    loadDashboard();
  } else {
    showToast('Error: ' + (d.error || 'Unknown error'), 'danger');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Decision'; }
  }
}

function resetWizard() {
  draftIdeaId = null;
  ['idea-title','idea-situation','idea-solution','idea-tangible','idea-intangible','co1-search','co2-search'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('co1-id').value = '';
  document.getElementById('co2-id').value = '';
  document.getElementById('co1-name-display').textContent = '';
  document.getElementById('co2-name-display').textContent = '';
  document.querySelectorAll('.impact-chip').forEach(c => c.classList.remove('selected'));
  ['situation','solution'].forEach(s => {
    const inp = document.getElementById('file-'+s);
    if (inp) inp.value = '';
    const lbl = document.getElementById('file-'+s+'-name');
    if (lbl) lbl.textContent = '';
  });
  goStep(1);
}

function goStep(n) {
  for (let i = 1; i <= totalSteps; i++) {
    const el = document.getElementById('step-' + i);
    if (el) el.style.display = 'none';
  }
  const stepEl = document.getElementById('step-' + n);
  if (stepEl) {
    stepEl.style.display = 'block';
    stepEl.style.animation = 'none';
    requestAnimationFrame(() => { stepEl.style.animation = 'fadeInUp .25s cubic-bezier(.4,0,.2,1)'; });
  }
  document.querySelectorAll('.w-step').forEach((s,idx) => {
    s.classList.remove('active','done');
    if (idx+1 < n) s.classList.add('done');
    else if (idx+1 === n) s.classList.add('active');
  });
  currentStep = n;
  const isLast = n === totalSteps;
  document.getElementById('wizard-nav').style.display        = isLast ? 'none' : 'flex';
  document.getElementById('wizard-submit-row').style.display = isLast ? 'flex' : 'none';
  if (!isLast) {
    document.getElementById('btn-back').style.visibility = n > 1 ? 'visible' : 'hidden';
    document.getElementById('btn-next').textContent      = n === totalSteps-1 ? 'Review' : 'Next';
  }
  if (isLast) buildReviewPreview();
}

function nextStep() {
  if (currentStep === 1) {
    if (!document.getElementById('idea-title').value.trim() ||
        document.getElementById('idea-situation').value.trim().length < 20) {
      alert('Please fill in the title and situation description (min 20 chars).'); return;
    }
  }
  if (currentStep === 2 && !document.getElementById('idea-solution').value.trim()) {
    alert('Please fill in the proposed solution.'); return;
  }
  if (currentStep < totalSteps) goStep(currentStep + 1);
}
function prevStep() { if (currentStep > 1) goStep(currentStep - 1); }

function toggleImpact(el) { el.classList.toggle('selected'); }

function buildReviewPreview() {
  const impacts = [...document.querySelectorAll('.impact-chip.selected')].map(c => c.dataset.val).join(', ');
  document.getElementById('review-preview').innerHTML = `
    <div class="form-group"><label>Title</label><div class="form-control" style="background:#f8f9fe">${escHtml(document.getElementById('idea-title').value)}</div></div>
    <div class="form-group"><label>Situation</label><div class="form-control" style="background:#f8f9fe;height:auto;min-height:60px">${escHtml(document.getElementById('idea-situation').value)}</div></div>
    <div class="form-group"><label>Solution</label><div class="form-control" style="background:#f8f9fe;height:auto;min-height:60px">${escHtml(document.getElementById('idea-solution').value)}</div></div>
    <div class="form-row">
      <div><label>Impact Areas</label><div class="form-control" style="background:#f8f9fe">${impacts||'None selected'}</div></div>
      <div><label>Impact Level</label><div class="form-control" style="background:#f8f9fe">${document.getElementById('idea-impact-level').value}</div></div>
    </div>
    ${document.getElementById('co1-name-display').textContent ? `<div><label>Co-Suggesters</label><div class="form-control" style="background:#f8f9fe">${document.getElementById('co1-name-display').textContent}${document.getElementById('co2-name-display').textContent ? ', ' + document.getElementById('co2-name-display').textContent : ''}</div></div>` : ''}
  `;
}

async function saveDraft() {
  const body = buildIdeaPayload();
  body.id = draftIdeaId;
  const r = await fetch('api/ideas.php?action=draft', {
    method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body: JSON.stringify(body)
  });
  const d = await r.json();
  if (d.success) { draftIdeaId = d.idea_id; alert('Draft saved! Idea code: ' + d.idea_code); }
}

async function submitIdea() {
  const submitBtn = document.querySelector('#wizard-submit-row .btn-success');
  if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Submitting…'; }

  const body = buildIdeaPayload();
  body.id = draftIdeaId;
  let r, d;
  try {
    r = await fetch('api/ideas.php?action=submit', {
      method:'POST', headers:{'Content-Type':'application/json'},
      credentials:'same-origin', body: JSON.stringify(body)
    });
    d = await r.json();
  } catch(e) {
    alert('Server error. Please try again.');
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Idea'; }
    return;
  }

  if (d.success) {
    await uploadFiles(d.idea_id);
    currentUser.points += (d.points_added || 0);
    document.getElementById('sb-points').textContent = currentUser.points + ' pts';
    closeModal('modal-idea-detail');
    document.getElementById('success-title').textContent = 'Idea Submitted Successfully';
    document.getElementById('success-msg').textContent   = `Idea #${d.idea_code} submitted and routed to your manager for review. +${d.points_added} points credited. AI Quality Score: ${d.ai_score}/100.`;
    document.getElementById('modal-success').classList.add('open');
    showToast(`Idea #${d.idea_code} submitted! +${d.points_added} pts earned`, 'success');
    draftIdeaId = null;
    loadMyIdeas();
    loadDashboard();
  } else {
    alert('Error: ' + d.error);
    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Submit Idea'; }
  }
}

function buildIdeaPayload() {
  const impacts = [...document.querySelectorAll('.impact-chip.selected')].map(c => c.dataset.val).join(',');
  return {
    title:              document.getElementById('idea-title').value,
    present_situation:  document.getElementById('idea-situation').value,
    proposed_solution:  document.getElementById('idea-solution').value,
    impact_areas:       impacts,
    impact_level:       document.getElementById('idea-impact-level').value,
    tangible_benefit:   document.getElementById('idea-tangible').value,
    intangible_benefit: document.getElementById('idea-intangible').value,
    co_suggester_1_id:  document.getElementById('co1-id').value || null,
    co_suggester_2_id:  document.getElementById('co2-id').value || null,
  };
}

async function uploadFiles(ideaId) {
  for (const [inputId, section] of [['file-situation','situation'],['file-solution','solution']]) {
    const input = document.getElementById(inputId);
    if (!input.files.length) continue;
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('idea_id', ideaId);
    fd.append('section', section);
    await fetch('api/upload.php?action=upload', {method:'POST', credentials:'same-origin', body: fd});
  }
}

const searchTimers = {};
async function searchUsers(input, resultsId, hiddenId, displayId) {
  clearTimeout(searchTimers[resultsId]);
  const q = input.value.trim();
  const el = document.getElementById(resultsId);
  if (q.length < 2) { el.style.display = 'none'; return; }
  searchTimers[resultsId] = setTimeout(async () => {
    const r = await fetch('api/users.php?action=list&q=' + encodeURIComponent(q), {credentials:'same-origin'});
    const d = await r.json();
    if (!d.users?.length) { el.innerHTML = '<div class="uitem">No users found.</div>'; el.style.display = 'block'; return; }
    el.innerHTML = d.users.map(u => `
      <div class="uitem"
        data-id="${u.id}"
        data-name="${escHtml(u.name)}"
        data-empid="${escHtml(u.employee_id)}"
        data-hidden="${hiddenId}"
        data-display="${displayId}"
        data-results="${resultsId}"
        data-input="${input.id}"
        onclick="selectUserFromEl(this)">
        ${escHtml(u.name)} · ${escHtml(u.employee_id)} · ${escHtml(u.department||'–')}
      </div>`).join('');
    el.style.display = 'block';
  }, 300);
}

function selectUserFromEl(el) {
  const d = el.dataset;
  selectUser(d.id, d.name, d.empid, d.hidden, d.display, d.results, d.input);
}

function selectUser(id, name, empId, hiddenId, displayId, resultsId, inputId) {
  document.getElementById(hiddenId).value          = id;
  document.getElementById(displayId).textContent   = `${name} (${empId})`;
  document.getElementById(resultsId).style.display = 'none';
  document.getElementById(inputId).value           = name;
}

document.addEventListener('click', e => {
  if (!e.target.closest('.pos-rel')) {
    document.querySelectorAll('.user-search-results').forEach(el => el.style.display='none');
  }
});

async function loadAudit() {
  if (!['manager','admin','executive'].includes(currentUser?.role)) {
    document.getElementById('audit-tbody').innerHTML = '<tr><td colspan="5" class="text-center"><div class="alert alert-warning">Audit Trail is only available to Managers, Admins and Executives.</div></td></tr>';
    return;
  }
  document.getElementById('audit-tbody').innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner"></div> Loading audit records…</td></tr>';
  let r, d;
  try {
    r = await fetch('api/users.php?action=audit', {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    document.getElementById('audit-tbody').innerHTML = '<tr><td colspan="5" class="text-center alert alert-danger">Failed to load. Check server connection.</td></tr>';
    return;
  }
  if (!d.success) {
    document.getElementById('audit-tbody').innerHTML = `<tr><td colspan="5" class="text-center"><div class="alert alert-danger">${d.error||'Error loading audit.'}</div></td></tr>`;
    return;
  }
  document.getElementById('audit-tbody').innerHTML = (d.audit||[]).map(w => `
    <tr>
      <td>${fmtDate(w.created_at)}</td>
      <td><strong>${w.idea_code}</strong><br><small>${escHtml(w.idea_title||'').substring(0,40)}</small></td>
      <td><span class="badge ${statusBadge(w.action)}">${w.action}</span></td>
      <td>${escHtml(w.actor_name)} <small>(${w.actor_role})</small></td>
      <td>${w.comment ? escHtml(w.comment) : '—'}</td>
    </tr>`).join('') || '<tr><td colspan="5" class="text-center">No audit records.</td></tr>';
}

let lbPeriod = 'all';
async function loadLeaderboard() {
  document.getElementById('lb-individuals').innerHTML = '<div class="spinner"></div>';
  document.getElementById('lb-departments').innerHTML  = '<div class="spinner"></div>';
  document.getElementById('lb-top-ideas').innerHTML    = '<div class="spinner"></div>';
  let r, d;
  try {
    r = await fetch('api/users.php?action=leaderboard&period=' + lbPeriod, {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    document.getElementById('lb-individuals').innerHTML = '<div class="alert alert-danger">Failed to load leaderboard.</div>';
    return;
  }

  const maxPts = Math.max(...(d.individuals||[]).map(u=>u.points), 1);
  const lbEl = document.getElementById('lb-individuals');
  lbEl.innerHTML = (d.individuals||[]).map((u,i) => {
    const ei = engagementIndex(u.avg_score, u.avg_community_rating, u.total_votes_received);
    return `
    <div class="lb-row">
      <div class="lb-rank ${i===0?'rank-1':i===1?'rank-2':i===2?'rank-3':'rank-n'}">${i+1}</div>
      <div class="avatar">${u.avatar_initials||u.name[0]}</div>
      <div style="flex:1">
        <div class="lb-name">${escHtml(u.name)} ${u.id==currentUser?.id?'<span style="font-size:11px;color:#d97706">(You)</span>':''}</div>
        <div class="lb-dept">${u.department||'–'}</div>
        <div class="progress-bar mt-8"><div class="progress-fill" style="width:0%" data-w="${Math.round(u.points/maxPts*100)}"></div></div>
        <div style="margin-top:4px">${engMiniStats(u.avg_community_rating, u.total_votes_received)}</div>
      </div>
      <div style="text-align:right">
        <div class="lb-points">${u.points} pts</div>
        <div class="lb-ideas">${u.idea_count||0} ideas</div>
        ${u.avg_score > 0 ? `<span class="${scoreBadgeClass(u.avg_score)}" style="margin-top:2px;display:inline-block">Avg ${u.avg_score}</span>` : ''}
        <div style="margin-top:4px">${engBadge(u.avg_score, u.avg_community_rating, u.total_votes_received)}</div>
      </div>
    </div>`;
  }).join('') || '<div class="empty-state">No data yet.</div>';
  staggerAnimate([...lbEl.querySelectorAll('.lb-row')], 60);
  setTimeout(() => {
    lbEl.querySelectorAll('.progress-fill[data-w]').forEach(bar => {
      bar.style.transition = 'width .8s cubic-bezier(.4,0,.2,1)';
      bar.style.width = bar.dataset.w + '%';
    });
  }, 200);

  const maxDeptPts = Math.max(...(d.departments||[]).map(dep=>dep.dept_points), 1);
  document.getElementById('lb-departments').innerHTML = `
    <div class="bar-chart">${(d.departments||[]).map(dept => `
      <div class="bar-row">
        <span class="bar-label">${escHtml(dept.department||'–').substring(0,12)}</span>
        <div class="bar-track"><div class="bar-fill" style="width:0%;background:linear-gradient(90deg,#4f46e5,#818cf8)" data-w="${Math.round((dept.dept_points||0)/maxDeptPts*100)}"></div></div>
        <span class="bar-val">${dept.dept_points||0}</span>
      </div>`).join('')}</div>`;
  setTimeout(() => {
    document.querySelectorAll('#lb-departments .bar-fill[data-w]').forEach((bar, i) => {
      setTimeout(() => {
        bar.style.transition = 'width .7s cubic-bezier(.4,0,.2,1)';
        bar.style.width = bar.dataset.w + '%';
      }, i * 80);
    });
  }, 100);

  if (d.top_ideas && d.top_ideas.length) {
    document.getElementById('lb-top-ideas').innerHTML = d.top_ideas.map((idea, idx) => `
      <div class="top-idea-row">
        <div class="top-idea-rank">#${idx+1}</div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600;color:var(--heading)">${escHtml(idea.title)}</div>
          <div style="font-size:11px;color:var(--subtle)">${idea.idea_code} · ${escHtml(idea.submitter_name)} · ${idea.department||'–'}</div>
        </div>
        <div style="text-align:right">
          <span class="${scoreBadgeClass(idea.ai_score)}">${idea.ai_score}/100</span>
          <div style="font-size:11px;color:var(--subtle);margin-top:2px"><span class="badge ${statusBadge(idea.status)}">${idea.status}</span></div>
        </div>
      </div>`).join('');
  } else {
    document.getElementById('lb-top-ideas').innerHTML = '<div class="empty-state">No scored ideas yet. Submit ideas to see rankings.</div>';
  }
}

function activateChip(el, group) {
  document.querySelectorAll(`.chip`).forEach(c => { if (c.closest('.chip-filter') === el.closest('.chip-filter')) c.classList.remove('active'); });
  el.classList.add('active');
  if (group === 'lb-period') { lbPeriod = el.dataset.val; loadLeaderboard(); }
}

async function loadAnalytics() {
  if (!['manager','admin','executive'].includes(currentUser?.role)) {
    document.getElementById('analytics-kpis').innerHTML = '<div class="alert alert-warning" style="grid-column:1/-1">Analytics is only available to Managers, Admins and Executives.</div>';
    return;
  }
  document.getElementById('analytics-kpis').innerHTML = '<div class="kpi-card"><div class="spinner"></div></div><div class="kpi-card"><div class="spinner"></div></div><div class="kpi-card"><div class="spinner"></div></div><div class="kpi-card"><div class="spinner"></div></div>';
  document.getElementById('analytics-impact').innerHTML  = '<div class="spinner"></div>';
  document.getElementById('analytics-status').innerHTML  = '<div class="spinner"></div>';
  document.getElementById('analytics-trend').innerHTML   = '<div class="spinner"></div>';
  document.getElementById('analytics-score-dist').innerHTML = '<div class="spinner"></div>';
  let r, d;
  try {
    r = await fetch('api/users.php?action=analytics', {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    document.getElementById('analytics-kpis').innerHTML = '<div class="alert alert-danger" style="grid-column:1/-1">Failed to load analytics. Check server connection.</div>';
    return;
  }
  if (!d.success) {
    document.getElementById('analytics-kpis').innerHTML = `<div class="alert alert-danger" style="grid-column:1/-1">${d.error || 'Failed to load analytics.'}</div>`;
    return;
  }

  const counts = {};
  (d.status_summary||[]).forEach(s => counts[s.status] = s.cnt);
  const total   = Object.values(counts).reduce((a,b)=>a+b,0);
  const approved = (counts['Approved']||0) + (counts['Implemented']||0);
  const impl    = counts['Implemented']||0;
  const ss      = d.score_stats || {};

  document.getElementById('analytics-kpis').innerHTML = `
    <div class="kpi-card" style="border-left-color:#4f46e5">
      <div class="kpi-icon" style="background:#eef2ff;color:#4f46e5"><svg viewBox="0 0 24 24"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${total}">0</div><div class="kpi-label">Total Ideas</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#059669">
      <div class="kpi-icon" style="background:#dcfce7;color:#059669"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${total ? Math.round(approved/total*100) : 0}" data-suffix="%">0%</div><div class="kpi-label">Approval Rate</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#7c3aed">
      <div class="kpi-icon" style="background:#f3e8ff;color:#7c3aed"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${total ? Math.round(impl/total*100) : 0}" data-suffix="%">0%</div><div class="kpi-label">Implementation Rate</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#4f46e5">
      <div class="kpi-icon" style="background:#eef2ff;color:#4f46e5"><svg viewBox="0 0 24 24"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg></div>
      <div class="kpi-body"><div class="kpi-val" data-target="${ss.overall_avg || 0}">0</div><div class="kpi-label">Avg AI Quality Score</div></div>
    </div>
  `;
  document.querySelectorAll('#analytics-kpis .kpi-val[data-target]').forEach(el => {
    const suffix = el.dataset.suffix || '';
    const target = parseInt(el.dataset.target);
    const start = performance.now();
    (function step(now) {
      const p = Math.min((now - start) / 900, 1);
      const ease = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * ease) + suffix;
      if (p < 1) requestAnimationFrame(step);
    })(start);
  });

  const impColors = ['#4f46e5','#6366f1','#818cf8','#a5b4fc','#c7d2fe','#eef2ff'];
  const maxImp = Math.max(...Object.values(d.impact_distribution||{}), 1);
  document.getElementById('analytics-impact').innerHTML = Object.entries(d.impact_distribution||{}).map(([k,v],i) => `
    <div class="bar-row">
      <span class="bar-label">${k}</span>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.round(v/maxImp*100)}%;background:${impColors[i%impColors.length]}"></div></div>
      <span class="bar-val">${v}</span>
    </div>`).join('') || '<div class="empty-state">No data yet.</div>';

  const statusColors = {'Submitted':'#4f46e5','Under Review':'#d97706','Approved':'#059669','Rejected':'#dc2626','Implemented':'#7c3aed','Draft':'#94a3b8'};
  document.getElementById('analytics-status').innerHTML = `
    <div class="bar-chart">${(d.status_summary||[]).map(s => `
      <div class="bar-row">
        <span class="bar-label">${s.status}</span>
        <div class="bar-track"><div class="bar-fill" style="width:0%;background:${statusColors[s.status]||'#ccc'}" data-w="${Math.round(s.cnt/Math.max(total,1)*100)}"></div></div>
        <span class="bar-val">${s.cnt}</span>
      </div>`).join('')}</div>`;
  setTimeout(() => {
    document.querySelectorAll('#analytics-status .bar-fill[data-w]').forEach((bar, i) => {
      setTimeout(() => { bar.style.transition='width .7s cubic-bezier(.4,0,.2,1)'; bar.style.width=bar.dataset.w+'%'; }, i*80);
    });
  }, 150);

  const maxTrend = Math.max(...(d.trend||[]).map(t=>t.total), 1);
  document.getElementById('analytics-trend').innerHTML = (d.trend||[]).reverse().map(t => `
    <div class="bar-row">
      <span class="bar-label">${t.month}</span>
      <div class="bar-track"><div class="bar-fill" style="width:0%;background:linear-gradient(90deg,#4f46e5,#818cf8)" data-w="${Math.round(t.total/maxTrend*100)}"></div></div>
      <span class="bar-val">${t.total}</span>
    </div>`).join('') || '<div class="empty-state">No trend data yet.</div>';
  setTimeout(() => {
    document.querySelectorAll('#analytics-trend .bar-fill[data-w]').forEach((bar, i) => {
      setTimeout(() => { bar.style.transition='width .7s cubic-bezier(.4,0,.2,1)'; bar.style.width=bar.dataset.w+'%'; }, i*80);
    });
  }, 150);

  const hq = parseInt(ss.high_quality || 0);
  const mq = parseInt(ss.medium_quality || 0);
  const lq = parseInt(ss.low_quality || 0);
  const maxQ = Math.max(hq, mq, lq, 1);
  document.getElementById('analytics-score-dist').innerHTML = `
    <div class="bar-chart">
      <div class="bar-row"><span class="bar-label">High (75+)</span><div class="bar-track"><div class="bar-fill" style="width:${Math.round(hq/maxQ*100)}%;background:#059669"></div></div><span class="bar-val">${hq}</span></div>
      <div class="bar-row"><span class="bar-label">Med (50-74)</span><div class="bar-track"><div class="bar-fill" style="width:${Math.round(mq/maxQ*100)}%;background:#d97706"></div></div><span class="bar-val">${mq}</span></div>
      <div class="bar-row"><span class="bar-label">Low (&lt;50)</span><div class="bar-track"><div class="bar-fill" style="width:${Math.round(lq/maxQ*100)}%;background:#dc2626"></div></div><span class="bar-val">${lq}</span></div>
    </div>
    <div style="font-size:11px;color:var(--subtle);margin-top:8px">Overall average AI score: <strong>${ss.overall_avg || 0}/100</strong></div>`;
}

function formatRole(role) {
  const map = {super_admin:'Super Admin',admin:'Admin',manager:'Manager',employee:'Employee',executive:'Executive'};
  return map[role] || (role.charAt(0).toUpperCase() + role.slice(1));
}

async function loadAdminUsers() {
  const r = await fetch('api/users.php?action=admin_users', {credentials:'same-origin'});
  const d = await r.json();
  document.getElementById('admin-users-tbody').innerHTML = (d.users||[]).map(u => `
    <tr>
      <td><div style="display:flex;align-items:center;gap:8px"><div class="avatar">${u.avatar_initials||escHtml(u.name[0])}</div>${escHtml(u.name)}</div></td>
      <td>${escHtml(u.department||'–')}</td>
      <td>${escHtml(u.email)}</td>
      <td><span class="badge badge-submitted">${formatRole(u.role)}</span></td>
      <td>${u.points}</td>
    </tr>`).join('') || '<tr><td colspan="5" class="text-center">No users found.</td></tr>';
}

const ROLE_COLORS = {admin:'#4f46e5',manager:'#d97706',employee:'#059669',executive:'#7c3aed'};

function switchSaTab(el, tabId) {
  document.querySelectorAll('#page-super-admin .tab').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.sa-pane').forEach(p => p.style.display = 'none');
  el.classList.add('active');
  const pane = document.getElementById(tabId);
  if (pane) pane.style.display = 'block';
}

let _saUsersData = [];
function filterSaUsers() {
  const q = (document.getElementById('sa-user-search').value || '').toLowerCase();
  const rows = document.querySelectorAll('#hierarchy-users-tbody tr');
  rows.forEach(r => {
    r.style.display = !q || r.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
}

function renderHierarchyNode(node, depth) {
  const color = ROLE_COLORS[node.role] || '#888';
  const ml = depth * 36;
  let connector = '';
  if (depth > 0) {
    connector = `<div style="position:absolute;left:${ml - 18}px;top:50%;width:14px;height:1px;background:var(--border)"></div>`;
  }
  let html = `
    <div style="position:relative;margin-left:${ml}px;margin-bottom:8px">
      ${connector}
      <div style="border-left:3px solid ${color};padding:11px 16px;background:var(--surface);border-radius:var(--r);box-shadow:var(--shadow-sm);display:flex;align-items:center;gap:12px;flex-wrap:wrap;transition:box-shadow .15s">
        <div class="avatar" style="background:linear-gradient(135deg,${color},${color}cc);flex-shrink:0;font-weight:800">${escHtml(node.avatar_initials||node.name[0])}</div>
        <div style="flex:1;min-width:180px">
          <div style="font-weight:700;font-size:13px;color:var(--text)">${escHtml(node.name)}</div>
          <div style="font-size:11px;color:var(--subtle);margin-top:2px">${escHtml(node.employee_id)} &middot; ${escHtml(node.department||'–')} &middot; ${escHtml(node.location||'–')}</div>
        </div>
        <span class="badge" style="background:${color}18;color:${color};border:1px solid ${color}40;font-weight:700">${formatRole(node.role)}</span>
        <div style="display:flex;gap:16px;font-size:12px;color:var(--subtle)">
          <span title="Points"><strong style="color:var(--text)">${node.points}</strong> pts</span>
          <span title="Ideas submitted"><strong style="color:var(--text)">${node.idea_count}</strong> ideas</span>
        </div>
      </div>
    </div>`;
  if (node.children && node.children.length) {
    const sorted = [...node.children].sort((a,b) => {
      const o = {manager:0,employee:1};
      return (o[a.role]??2) - (o[b.role]??2) || a.name.localeCompare(b.name);
    });
    sorted.forEach(c => { html += renderHierarchyNode(c, depth + 1); });
  }
  return html;
}

async function loadHierarchy() {
  const [hierRes, dashRes] = await Promise.all([
    fetch('api/users.php?action=hierarchy',    {credentials:'same-origin'}),
    fetch('api/ideas.php?action=dashboard',    {credentials:'same-origin'}),
  ]);
  const hier = await hierRes.json();
  const dash = await dashRes.json();
  if (!hier.success) return;

  // ── KPI Strip ──────────────────────────────────────────────────
  const s = hier.stats;
  const counts = dash.counts || {};
  const pending = (counts['Submitted']||0) + (counts['Under Review']||0);
  document.getElementById('sa-last-updated').textContent =
    'Last refreshed: ' + new Date().toLocaleTimeString();
  document.getElementById('sa-kpi-strip').innerHTML = [
    ['Total Ideas',    dash.total||0,            '#4f46e5', 'Excluding drafts'],
    ['Pending Review', pending,                  '#dc2626', 'Submitted + Under Review'],
    ['Approved',       counts['Approved']||0,    '#d97706', 'Awaiting implementation'],
    ['Implemented',    counts['Implemented']||0, '#059669', 'Completed ideas'],
    ['Total Users',    s.total,                  '#7c3aed', `${s.admins} admins · ${s.managers} mgrs · ${s.employees} emp`],
    ['Executives',     s.executives,             '#2563eb', 'Executive-level accounts'],
  ].map(([label, val, color, sub]) => `
    <div class="kpi-card" style="border-left-color:${color}">
      <div class="kpi-val" style="color:${color}">${val}</div>
      <div class="kpi-label">${label}</div>
      <div style="font-size:10px;color:var(--subtle);margin-top:3px">${sub}</div>
    </div>`).join('');

  // ── Overview Tab ───────────────────────────────────────────────
  const statuses = ['Submitted','Under Review','Approved','Rejected','Implemented'];
  const statusColors = {'Submitted':'#2563eb','Under Review':'#d97706','Approved':'#059669','Rejected':'#dc2626','Implemented':'#7c3aed'};
  const maxCount = Math.max(...statuses.map(s => counts[s]||0), 1);
  document.getElementById('sa-status-dist').innerHTML = `
    <div class="bar-chart">
      ${statuses.map(s => `
        <div class="bar-row">
          <span class="bar-label">${s}</span>
          <div class="bar-track">
            <div class="bar-fill" style="width:${Math.round((counts[s]||0)/maxCount*100)}%;background:${statusColors[s]}"></div>
          </div>
          <span class="bar-val">${counts[s]||0}</span>
        </div>`).join('')}
    </div>`;

  const tlColors = {Submitted:'blue',Approved:'green',Rejected:'red',Implemented:'purple',Reviewed:'orange',Commented:'orange',Reopened:'blue'};
  document.getElementById('sa-recent-activity').innerHTML = (dash.recent||[]).length
    ? (dash.recent||[]).map(r => `
        <div class="tl-item">
          <div class="tl-dot tl-dot-${tlColors[r.action]||'blue'}" style="font-size:9px;font-weight:800">${(r.action||'').substring(0,3).toUpperCase()}</div>
          <div>
            <div class="tl-title">${escHtml(r.idea_code)} — ${escHtml(r.title||'')}</div>
            <div style="font-size:11px;color:var(--subtle);margin-top:2px">${r.action} by <strong>${escHtml(r.actor_name)}</strong> &middot; ${timeAgo(r.created_at)}</div>
          </div>
        </div>`).join('')
    : '<div style="color:var(--subtle);font-size:13px;padding:10px 0">No recent activity.</div>';

  // ── Hierarchy Tree ─────────────────────────────────────────────
  const byId = {};
  hier.users.forEach(u => { byId[u.id] = {...u, children: []}; });
  const roots = [];
  hier.users.forEach(u => {
    if (u.manager_id && byId[u.manager_id]) byId[u.manager_id].children.push(byId[u.id]);
    else roots.push(byId[u.id]);
  });
  const rootOrder = {admin:0,executive:1,manager:2,employee:3};
  roots.sort((a,b) => (rootOrder[a.role]??9) - (rootOrder[b.role]??9) || a.name.localeCompare(b.name));
  document.getElementById('hierarchy-tree').innerHTML = roots.length
    ? roots.map(n => renderHierarchyNode(n, 0)).join('')
    : '<div style="color:var(--subtle);padding:16px">No users found.</div>';

  // ── User Management Table ──────────────────────────────────────
  _saUsersData = hier.users;
  document.getElementById('hierarchy-users-tbody').innerHTML = hier.users.map(u => `
    <tr>
      <td><div style="display:flex;align-items:center;gap:9px">
        <div class="avatar" style="background:linear-gradient(135deg,${ROLE_COLORS[u.role]||'#888'},${ROLE_COLORS[u.role]||'#888'}99)">${escHtml(u.avatar_initials||u.name[0])}</div>
        <div>
          <div style="font-weight:600">${escHtml(u.name)}</div>
          <div style="font-size:11px;color:var(--subtle)">${escHtml(u.email)}</div>
        </div>
      </div></td>
      <td style="font-size:12px;color:var(--subtle)">${escHtml(u.employee_id)}</td>
      <td><span class="badge" style="background:${ROLE_COLORS[u.role]||'#888'}18;color:${ROLE_COLORS[u.role]||'#888'};border:1px solid ${ROLE_COLORS[u.role]||'#888'}40">${formatRole(u.role)}</span></td>
      <td>${escHtml(u.department||'–')}</td>
      <td>${escHtml(u.business_unit||'–')}</td>
      <td style="font-size:12px">${escHtml(u.email)}</td>
      <td style="font-size:12px;color:var(--subtle)">${escHtml(u.manager_name||'—')}</td>
      <td><strong>${u.points}</strong></td>
      <td>${u.idea_count}</td>
    </tr>`).join('') || `<tr><td colspan="9" class="text-center">No users found.</td></tr>`;
}

async function batchRescoreSa() {
  const btn = document.getElementById('sa-rescore-btn');
  if (btn) { btn.disabled = true; btn.textContent = 'Rescoring…'; }
  try {
    const r = await fetch('api/score.php?action=batch_rescore', {method:'POST', credentials:'same-origin'});
    const d = await r.json();
    document.getElementById('sa-rescore-result').innerHTML = d.success
      ? `<span class="alert alert-success" style="display:inline-block;padding:6px 14px">Rescored ${d.updated} ideas successfully.</span>`
      : `<span class="alert alert-danger"  style="display:inline-block;padding:6px 14px">${escHtml(d.error||'Error.')}</span>`;
  } catch(e) {
    document.getElementById('sa-rescore-result').innerHTML = '<span class="alert alert-danger" style="display:inline-block;padding:6px 14px">Server error — check API connection.</span>';
  }
  if (btn) {
    btn.disabled = false;
    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/></svg> Rescore All Ideas';
  }
}

async function batchRescore() {
  const btn = document.querySelector('#atab4 .btn-warning');
  if (btn) { btn.disabled = true; btn.textContent = 'Rescoring…'; }
  try {
    const r = await fetch('api/score.php?action=batch_rescore', {method:'POST', credentials:'same-origin'});
    const d = await r.json();
    document.getElementById('rescore-result').innerHTML = d.success
      ? `<span class="alert alert-success" style="display:inline-block">Rescored ${d.updated} ideas successfully.</span>`
      : `<span class="alert alert-danger" style="display:inline-block">${d.error || 'Error.'}</span>`;
  } catch(e) {
    document.getElementById('rescore-result').innerHTML = '<span class="alert alert-danger" style="display:inline-block">Server error.</span>';
  }
  if (btn) { btn.disabled = false; btn.textContent = 'Rescore All Ideas'; }
}

function renderProfile() {
  if (!currentUser) return;
  const u = currentUser;
  document.getElementById('profile-avatar').textContent   = u.avatar_initials || u.name[0];
  document.getElementById('profile-name').textContent     = u.name;
  document.getElementById('profile-empid').textContent    = u.employee_id;
  document.getElementById('profile-role-badge').textContent = formatRole(u.role);
  document.getElementById('profile-table').innerHTML = `
    <tr><td style="color:var(--subtle);padding:5px 0">Department</td><td>${u.department||'–'}</td></tr>
    <tr><td style="color:var(--subtle);padding:5px 0">Email</td><td>${u.email}</td></tr>
    <tr><td style="color:var(--subtle);padding:5px 0">Phone</td><td>${u.phone||'–'}</td></tr>
    <tr><td style="color:var(--subtle);padding:5px 0">Reporting To</td><td>${u.manager_name||'–'}</td></tr>
    <tr><td style="color:var(--subtle);padding:5px 0">Business Unit</td><td>${u.business_unit||'–'}</td></tr>
    <tr><td style="color:var(--subtle);padding:5px 0">Location</td><td>${u.location||'–'}</td></tr>
  `;
  document.getElementById('profile-stats').innerHTML = `
    <div class="mini-stat"><div class="mini-stat-val">${u.points}</div><div class="mini-stat-label">Total Points</div></div>
  `;
}

async function loadNotifications() {
  const r = await fetch('api/users.php?action=notifications', {credentials:'same-origin'});
  const d = await r.json();
  const count = d.unread_count || 0;
  const badge = document.getElementById('notif-count');
  badge.textContent    = count;
  badge.style.display  = count > 0 ? 'flex' : 'none';
  document.getElementById('notif-list').innerHTML = (d.notifications||[]).map(n => `
    <div class="notif-item ${n.is_read ? '' : 'unread'}" ${n.idea_id ? `onclick="openNotifIdea(${n.idea_id})"` : ''} style="${n.idea_id ? 'cursor:pointer' : 'cursor:default'}">
      <div class="notif-item-title">${escHtml(n.title)}</div>
      <div class="notif-item-meta">${n.message ? escHtml(n.message).substring(0,80) : ''}</div>
      <div class="notif-item-meta">${timeAgo(n.created_at)}</div>
    </div>`).join('') || '<div class="empty-state">No notifications</div>';
}

async function markAllRead() {
  await fetch('api/users.php?action=mark_read', {method:'POST', credentials:'same-origin'});
  document.getElementById('notif-count').style.display = 'none';
  document.getElementById('notif-panel').classList.remove('open');
  loadNotifications();
}

function openNotifIdea(ideaId) {
  document.getElementById('notif-panel').classList.remove('open');
  navigate('my-ideas', document.querySelector('.nav-item[onclick*="my-ideas"]'));
  openIdeaDetail(ideaId);
}

function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

function switchTab(el, tabId) {
  const parent = el.closest('.card, .modal-body, .page');
  parent.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  parent.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById(tabId).classList.add('active');
}

['situation','solution'].forEach(s => {
  document.getElementById('file-'+s).addEventListener('change', function() {
    document.getElementById('file-'+s+'-name').textContent =
      this.files.length ? 'Attached: ' + this.files[0].name : '';
  });
});

function statusBadge(s) {
  return {'Submitted':'badge-submitted','Under Review':'badge-review','Approved':'badge-approved',
          'Rejected':'badge-rejected','Implemented':'badge-implemented','Draft':'badge-draft',
          'Reviewed':'badge-review'}[s] || 'badge-draft';
}
function impactBadge(l) {
  return {'Low':'badge-low','Medium':'badge-medium','High':'badge-high'}[l]||'badge-draft';
}
function scoreBadgeClass(score) {
  const n = parseInt(score) || 0;
  if (n >= 75) return 'score-badge score-high';
  if (n >= 50) return 'score-badge score-med';
  if (n >  0)  return 'score-badge score-low';
  return 'score-badge score-none';
}
function actionLabel(a) {
  return {'Submitted':'SUB','Approved':'APR','Rejected':'REJ',
          'Implemented':'IMP','Reviewed':'REV','Commented':'CMT','Reopened':'ROP'}[a] || 'ACT';
}
function escHtml(str) {
  return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDate(dt) {
  if (!dt) return '–';
  return new Date(dt).toLocaleDateString('en-IN',{day:'2-digit',month:'short',year:'numeric'});
}
function timeAgo(dt) {
  if (!dt) return '–';
  const diff = (Date.now() - new Date(dt)) / 1000;
  if (diff < 60) return 'Just now';
  if (diff < 3600) return Math.floor(diff/60) + ' min ago';
  if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
  return Math.floor(diff/86400) + 'd ago';
}

if (currentUser) initApp();
</script>

<div class="modal-overlay" id="modal-assign-reviewers">
  <div class="modal" style="width:560px">
    <div class="modal-header">
      <div class="modal-title">Route to Committee — <span id="ar-idea-code"></span></div>
      <span class="modal-close" onclick="closeModal('modal-assign-reviewers')">&#10005;</span>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Search &amp; Add Reviewers</label>
        <div class="pos-rel">
          <input class="form-control" id="ar-search" placeholder="Search by name or employee ID…" autocomplete="off" oninput="searchReviewersForAssign(this.value)"/>
          <div class="user-search-results" id="ar-results"></div>
        </div>
      </div>
      <div id="ar-selected-list" style="min-height:36px;margin-bottom:16px">
        <div style="font-size:12px;color:var(--subtle);padding:6px 0">No reviewers added yet.</div>
      </div>
      <div class="form-group">
        <label>Approval Threshold</label>
        <select class="form-control" id="ar-threshold">
          <option value="100">All reviewers must approve (unanimous)</option>
          <option value="67">Supermajority — at least 2/3 must approve</option>
          <option value="50">Simple majority — more than half must approve</option>
        </select>
      </div>
      <div class="alert alert-info" style="font-size:12px;margin:0">Idea moves to <strong>Under Review</strong>. All assigned reviewers will be notified immediately.</div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modal-assign-reviewers')">Cancel</button>
      <button class="btn btn-primary" id="ar-submit-btn" onclick="submitAssignReviewers()">Route to Committee</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="modal-reviewer-decision">
  <div class="modal" style="width:460px">
    <div class="modal-header">
      <div class="modal-title">My Review — <span id="rd-idea-code"></span></div>
      <span class="modal-close" onclick="closeModal('modal-reviewer-decision')">&#10005;</span>
    </div>
    <div class="modal-body">
      <div class="form-group" style="margin-bottom:20px">
        <label>Your Decision</label>
        <div style="display:flex;gap:10px;margin-top:8px">
          <button class="btn btn-outline" id="rd-approve-btn" style="flex:1;padding:10px;font-size:14px" onclick="selectReviewerDecision('approved')">&#10003; Approve</button>
          <button class="btn btn-outline" id="rd-reject-btn" style="flex:1;padding:10px;font-size:14px" onclick="selectReviewerDecision('rejected')">&#10007; Reject</button>
        </div>
        <input type="hidden" id="rd-decision" value=""/>
      </div>
      <div class="form-group">
        <label>Feedback / Comment <span style="color:var(--subtle);font-weight:400;text-transform:none">(optional)</span></label>
        <textarea class="form-control" id="rd-comment" rows="3" placeholder="Share your reasoning with the submitter…"></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('modal-reviewer-decision')">Cancel</button>
      <button class="btn btn-primary" id="rd-submit-btn" disabled onclick="submitReviewerDecision()">Submit Decision</button>
    </div>
  </div>
</div>

</body>
</html>
