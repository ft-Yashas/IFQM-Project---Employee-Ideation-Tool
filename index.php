<?php
session_start();
$loggedIn = !empty($_SESSION['user_id']);
$user     = $_SESSION['user'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Employee Ideation Tool – IFQM</title>
<style>
  :root{
    --primary:#3f51b5;--primary-dk:#1a237e;--primary-lt:#e8eaf6;
    --success:#43a047;--danger:#e53935;--warning:#fb8c00;--info:#1976d2;
    --shadow-sm:0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
    --shadow-md:0 4px 14px rgba(0,0,0,.1),0 2px 4px rgba(0,0,0,.06);
    --shadow-lg:0 8px 32px rgba(0,0,0,.15),0 4px 8px rgba(0,0,0,.06);
    --r:8px;--r-lg:12px;--r-xl:16px;
    --ease:cubic-bezier(.4,0,.2,1);
  }
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Segoe UI',system-ui,-apple-system,Arial,sans-serif;background:#f0f2f5;color:#222;font-size:14px;-webkit-font-smoothing:antialiased}
  #app{display:flex;height:100vh;overflow:hidden}

  /* ── SIDEBAR ── */
  #sidebar{width:220px;background:linear-gradient(180deg,#1a237e 0%,#283593 100%);color:#fff;display:flex;flex-direction:column;flex-shrink:0;transition:width .22s var(--ease);box-shadow:2px 0 10px rgba(0,0,0,.18)}
  #sidebar.collapsed{width:56px}
  #main{flex:1;display:flex;flex-direction:column;overflow:hidden}
  #topbar{background:#fff;padding:10px 20px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid #e8eaf6;flex-shrink:0;box-shadow:0 1px 4px rgba(0,0,0,.06)}
  #content{flex:1;overflow-y:auto;padding:22px 24px}
  .sidebar-logo{padding:17px 14px;font-size:15px;font-weight:800;background:rgba(0,0,0,.18);display:flex;align-items:center;gap:9px;white-space:nowrap;overflow:hidden;letter-spacing:-.5px}
  .sidebar-logo span{transition:opacity .2s}
  #sidebar.collapsed .sidebar-logo span{opacity:0;width:0;overflow:hidden}
  .nav-section{padding:10px 0 3px;font-size:10px;text-transform:uppercase;letter-spacing:1.2px;color:rgba(159,168,218,.75);padding-left:14px;white-space:nowrap;overflow:hidden}
  #sidebar.collapsed .nav-section{opacity:0}
  .nav-item{display:flex;align-items:center;gap:10px;padding:9px 14px;cursor:pointer;white-space:nowrap;overflow:hidden;border-left:3px solid transparent;transition:background .15s var(--ease);margin:1px 0}
  .nav-item:hover{background:rgba(255,255,255,.12)}
  .nav-item.active{background:rgba(255,255,255,.18);border-left-color:#7986cb;box-shadow:inset 0 1px 0 rgba(255,255,255,.06),inset 0 -1px 0 rgba(0,0,0,.08)}
  .nav-item .icon{flex-shrink:0;width:26px;height:26px;display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,.12);border-radius:6px;transition:background .15s}
  .nav-item:hover .icon,.nav-item.active .icon{background:rgba(255,255,255,.22)}
  .nav-item .icon svg{width:14px;height:14px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .nav-item span.label{font-size:13px;font-weight:500;transition:opacity .2s}
  #sidebar.collapsed .nav-item span.label{opacity:0;width:0;overflow:hidden}

  /* ── TOPBAR ── */
  .topbar-left{display:flex;align-items:center;gap:12px}
  .page-title{font-size:16px;font-weight:700;color:#1a237e}
  .topbar-right{display:flex;align-items:center;gap:10px}
  .notif-bell{position:relative;cursor:pointer;font-size:13px;font-weight:600;color:#555;padding:6px 11px;border:1.5px solid #e0e0e0;border-radius:var(--r);background:#fff;transition:all .15s var(--ease)}
  .notif-bell:hover{border-color:#3f51b5;color:#3f51b5;background:#f8f9fe}
  .notif-badge{position:absolute;top:-5px;right:-5px;background:#f44336;color:#fff;border-radius:50%;width:18px;height:18px;font-size:10px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;font-weight:700}
  .user-chip{display:flex;align-items:center;gap:8px;background:#f0f2f5;padding:5px 12px 5px 5px;border-radius:20px;cursor:pointer;transition:all .15s var(--ease);border:1.5px solid transparent}
  .user-chip:hover{background:#e8eaf6;border-color:#c5cae9}
  .avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#3f51b5,#7986cb);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0;box-shadow:0 2px 5px rgba(63,81,181,.3)}
  .role-badge{font-size:10px;background:#3f51b5;color:#fff;padding:2px 8px;border-radius:10px;font-weight:600;letter-spacing:.3px}

  /* ── CARDS ── */
  .card{background:#fff;border-radius:var(--r-lg);padding:20px;box-shadow:var(--shadow-sm);margin-bottom:16px;border:1px solid rgba(0,0,0,.04)}
  .card-title{font-size:14px;font-weight:700;color:#1a237e;margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid #f0f2f5}

  /* ── KPI ── */
  .kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:20px}
  .kpi-card{background:#fff;border-radius:var(--r-lg);padding:18px 20px;box-shadow:var(--shadow-sm);border:1px solid rgba(0,0,0,.04);border-left:4px solid #3f51b5;transition:transform .18s var(--ease),box-shadow .18s var(--ease)}
  .kpi-card:hover{transform:translateY(-2px);box-shadow:var(--shadow-md)}
  .kpi-val{font-size:30px;font-weight:800;color:#1a237e;line-height:1.1}
  .kpi-label{font-size:12px;color:#888;margin-top:5px;font-weight:500}
  .kpi-delta{font-size:11px;color:#43a047;margin-top:5px;font-weight:600}

  /* ── TABLES ── */
  table{width:100%;border-collapse:collapse}
  th{background:#f5f6fb;color:#1a237e;font-size:11px;padding:10px 12px;text-align:left;font-weight:700;border-bottom:2px solid #e8eaf6;text-transform:uppercase;letter-spacing:.6px}
  td{padding:10px 12px;border-bottom:1px solid #f5f5f5;font-size:13px;vertical-align:middle}
  tr:hover td{background:#fafbff}

  /* ── BADGES ── */
  .badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.2px}
  .badge-submitted{background:#e3f2fd;color:#1565c0}
  .badge-review{background:#fff8e1;color:#e65100}
  .badge-approved{background:#e8f5e9;color:#2e7d32}
  .badge-rejected{background:#ffebee;color:#c62828}
  .badge-implemented{background:#f3e5f5;color:#6a1b9a}
  .badge-draft{background:#f5f5f5;color:#757575}
  .badge-low{background:#e8f5e9;color:#2e7d32}
  .badge-medium{background:#fff8e1;color:#e65100}
  .badge-high{background:#ffebee;color:#c62828}

  /* ── BUTTONS ── */
  .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:var(--r);border:none;cursor:pointer;font-size:13px;font-weight:600;transition:transform .15s var(--ease),box-shadow .15s var(--ease),opacity .15s;letter-spacing:.2px;font-family:inherit}
  .btn:hover{transform:translateY(-1px)}
  .btn:active{transform:translateY(0)}
  .btn-primary{background:linear-gradient(135deg,#3f51b5,#5c6bc0);color:#fff;box-shadow:0 2px 6px rgba(63,81,181,.3)}
  .btn-primary:hover{box-shadow:0 4px 14px rgba(63,81,181,.4)}
  .btn-success{background:linear-gradient(135deg,#43a047,#66bb6a);color:#fff;box-shadow:0 2px 6px rgba(67,160,71,.3)}
  .btn-success:hover{box-shadow:0 4px 14px rgba(67,160,71,.4)}
  .btn-danger{background:linear-gradient(135deg,#e53935,#ef5350);color:#fff;box-shadow:0 2px 6px rgba(229,57,53,.3)}
  .btn-outline{background:#fff;border:1.5px solid #3f51b5;color:#3f51b5}
  .btn-outline:hover{background:#e8eaf6}
  .btn-sm{padding:5px 12px;font-size:12px}
  .btn-warning{background:linear-gradient(135deg,#fb8c00,#ffa726);color:#fff;box-shadow:0 2px 6px rgba(251,140,0,.3)}

  /* ── FORMS ── */
  .form-group{margin-bottom:16px}
  .form-group label{display:block;font-size:11px;font-weight:700;color:#666;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px}
  .form-control{width:100%;padding:9px 12px;border:1.5px solid #e0e0e0;border-radius:var(--r);font-size:13px;color:#333;outline:none;transition:border-color .15s,box-shadow .15s;background:#fff;font-family:inherit}
  .form-control:focus{border-color:#3f51b5;box-shadow:0 0 0 3px rgba(63,81,181,.1)}
  select.form-control{background:#fff}
  textarea.form-control{resize:vertical;min-height:80px}
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}

  /* ── WIZARD ── */
  .wizard-steps{display:flex;gap:0;margin-bottom:24px}
  .w-step{flex:1;text-align:center;padding:10px 4px;font-size:12px;border-bottom:3px solid #e0e0e0;color:#bbb;cursor:pointer;font-weight:500;transition:color .15s,border-color .15s}
  .w-step.active{border-bottom-color:#3f51b5;color:#3f51b5;font-weight:700}
  .w-step.done{border-bottom-color:#43a047;color:#43a047}
  .wizard-body{min-height:280px}
  .wizard-footer{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #f0f0f0}

  /* ── TIMELINE ── */
  .timeline{padding:10px 0}
  .tl-item{display:flex;gap:14px;margin-bottom:18px}
  .tl-dot{width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;flex-shrink:0;margin-top:2px}
  .tl-dot-blue{background:#e3f2fd;color:#1565c0}
  .tl-dot-green{background:#e8f5e9;color:#2e7d32}
  .tl-dot-red{background:#ffebee;color:#c62828}
  .tl-dot-orange{background:#fff8e1;color:#f57f17}
  .tl-dot-purple{background:#f3e5f5;color:#6a1b9a}
  .tl-title{font-size:13px;font-weight:600;color:#222}
  .tl-meta{font-size:11px;color:#999;margin-top:3px}
  .tl-comment{font-size:12px;color:#555;margin-top:6px;background:#f8f9fe;padding:8px 12px;border-radius:var(--r);border-left:3px solid #c5cae9}

  /* ── BAR CHARTS ── */
  .bar-chart{display:flex;flex-direction:column;gap:10px}
  .bar-row{display:flex;align-items:center;gap:10px}
  .bar-label{width:100px;font-size:12px;color:#666;text-align:right;flex-shrink:0;font-weight:500}
  .bar-track{flex:1;height:20px;background:#f0f2f5;border-radius:10px;overflow:hidden}
  .bar-fill{height:100%;border-radius:10px;transition:width .6s var(--ease)}
  .bar-val{width:36px;font-size:12px;color:#555;flex-shrink:0;font-weight:600}

  /* ── LEADERBOARD ── */
  .lb-row{display:flex;align-items:center;gap:12px;padding:11px 6px;border-bottom:1px solid #f5f5f5;transition:background .12s;border-radius:var(--r)}
  .lb-row:hover{background:#f8f9fe}
  .lb-rank{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0}
  .rank-1{background:linear-gradient(135deg,#ffd700,#ffca28);color:#7a5800;box-shadow:0 2px 6px rgba(255,193,7,.4)}
  .rank-2{background:linear-gradient(135deg,#c0c0c0,#bdbdbd);color:#444;box-shadow:0 2px 5px rgba(189,189,189,.4)}
  .rank-3{background:linear-gradient(135deg,#cd7f32,#a1887f);color:#fff;box-shadow:0 2px 5px rgba(161,136,127,.3)}
  .rank-n{background:#e8eaf6;color:#3f51b5}
  .lb-name{flex:1;font-size:13px;font-weight:600}
  .lb-dept{font-size:11px;color:#999;margin-top:1px}
  .lb-points{font-size:16px;font-weight:800;color:#1a237e}
  .lb-ideas{font-size:11px;color:#999}
  .progress-bar{height:4px;background:#e8eaf6;border-radius:2px;margin-top:5px}
  .progress-fill{height:100%;background:linear-gradient(90deg,#3f51b5,#7986cb);border-radius:2px}

  /* ── SCORE BADGES ── */
  .score-badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:8px;font-size:10px;font-weight:700}
  .score-high{background:#e8f5e9;color:#2e7d32}
  .score-med{background:#fff8e1;color:#e65100}
  .score-low{background:#ffebee;color:#c62828}
  .score-none{background:#f5f5f5;color:#999}

  /* ── MODALS ── */
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.52);backdrop-filter:blur(3px);display:flex;align-items:center;justify-content:center;z-index:1000;display:none}
  .modal-overlay.open{display:flex;animation:mo-in .2s var(--ease)}
  @keyframes mo-in{from{opacity:0}to{opacity:1}}
  @keyframes slide-up{from{transform:translateY(18px);opacity:0}to{transform:translateY(0);opacity:1}}
  .modal{background:#fff;border-radius:var(--r-xl);width:620px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-lg);animation:slide-up .22s var(--ease)}
  .modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid #f0f0f0;background:linear-gradient(135deg,#f8f9fe,#fff)}
  .modal-title{font-size:15px;font-weight:700;color:#1a237e}
  .modal-close{cursor:pointer;font-size:18px;color:#aaa;line-height:1;width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:50%;transition:background .12s,color .12s}
  .modal-close:hover{background:#f0f0f0;color:#333}
  .modal-body{padding:22px}
  .modal-footer{padding:14px 22px;border-top:1px solid #f0f0f0;display:flex;justify-content:flex-end;gap:10px;background:#fafafa;border-radius:0 0 var(--r-xl) var(--r-xl)}

  /* ── PAGES ── */
  .page{display:none}
  .page.active{display:block;animation:mo-in .18s var(--ease)}
  .section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
  .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
  .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px}
  .text-muted{color:#999;font-size:12px;margin-top:2px}
  .text-center{text-align:center}
  .mt-8{margin-top:8px}
  .mb-8{margin-bottom:8px}
  .tag{display:inline-block;padding:2px 9px;background:#e8eaf6;color:#3f51b5;border-radius:10px;font-size:11px;margin:2px;font-weight:500}

  /* ── ALERTS ── */
  .alert{padding:11px 16px;border-radius:var(--r);font-size:13px;margin-bottom:14px;font-weight:500}
  .alert-info{background:#e3f2fd;color:#1565c0;border-left:4px solid #1976d2}
  .alert-success{background:#e8f5e9;color:#2e7d32;border-left:4px solid #43a047}
  .alert-warning{background:#fff8e1;color:#e65100;border-left:4px solid #fb8c00}
  .alert-danger{background:#ffebee;color:#c62828;border-left:4px solid #e53935}

  /* ── AI PANEL ── */
  .ai-panel{background:linear-gradient(135deg,#e8eaf6,#e3f2fd);border-radius:var(--r-lg);padding:16px;border:1px solid #c5cae9}
  .ai-panel-title{font-size:11px;font-weight:700;color:#3f51b5;margin-bottom:10px;text-transform:uppercase;letter-spacing:.6px}

  /* ── IMPACT CHIPS ── */
  .impact-grid{display:flex;flex-wrap:wrap;gap:8px}
  .impact-chip{padding:5px 13px;border-radius:20px;font-size:12px;cursor:pointer;border:1.5px solid #e0e0e0;background:#fff;transition:all .15s var(--ease);font-weight:500}
  .impact-chip:hover{border-color:#3f51b5;color:#3f51b5}
  .impact-chip.selected{background:#3f51b5;color:#fff;border-color:#3f51b5;box-shadow:0 2px 6px rgba(63,81,181,.3)}

  /* ── UPLOAD ── */
  .upload-zone{border:2px dashed #c5cae9;border-radius:var(--r-lg);padding:24px;text-align:center;color:#9fa8da;cursor:pointer;transition:all .15s var(--ease)}
  .upload-zone:hover{border-color:#3f51b5;background:#f8f9fe;color:#3f51b5}

  /* ── NOTIFICATIONS ── */
  .notification-panel{position:absolute;top:52px;right:70px;width:310px;background:#fff;border-radius:var(--r-lg);box-shadow:var(--shadow-lg);z-index:200;display:none;border:1px solid #eee;overflow:hidden}
  .notification-panel.open{display:block;animation:slide-up .2s var(--ease)}
  .notif-item{padding:12px 16px;border-bottom:1px solid #f5f5f5;cursor:pointer;transition:background .12s}
  .notif-item:hover{background:#f8f9fe}
  .notif-item.unread{border-left:3px solid #3f51b5;background:#fafbff}
  .notif-item-title{font-size:13px;font-weight:600}
  .notif-item-meta{font-size:11px;color:#999;margin-top:2px}

  /* ── LOGIN ── */
  .login-wrap{min-height:100vh;background:linear-gradient(135deg,#1a237e 0%,#283593 55%,#3949ab 100%);display:flex;align-items:center;justify-content:center}
  .login-card{background:#fff;border-radius:var(--r-xl);padding:40px;width:430px;box-shadow:0 20px 60px rgba(0,0,0,.28)}
  .login-logo{text-align:center;margin-bottom:28px}
  .login-logo h2{font-size:21px;color:#1a237e;margin-top:10px;font-weight:800}
  .login-logo p{font-size:12px;color:#aaa;margin-top:4px}
  .separator{height:1px;background:#f0f0f0;margin:16px 0}

  /* ── SIDEBAR USER ── */
  .sidebar-user{padding:13px 14px;border-top:1px solid rgba(255,255,255,.1);display:flex;align-items:center;gap:10px;margin-top:auto;overflow:hidden;background:rgba(0,0,0,.12)}
  .sidebar-user-info span{font-size:12px;opacity:.85;display:block;white-space:nowrap;transition:opacity .2s}
  #sidebar.collapsed .sidebar-user-info span{opacity:0;width:0;overflow:hidden}
  .points-badge{background:linear-gradient(135deg,#ffd700,#ffca28);color:#7a5800;font-size:11px;font-weight:700;padding:2px 7px;border-radius:8px}

  /* ── IDEA CARDS ── */
  .idea-card{background:#fff;border-radius:var(--r-lg);border:1px solid #eee;padding:16px;margin-bottom:12px;cursor:pointer;transition:transform .16s var(--ease),box-shadow .16s var(--ease),border-color .16s}
  .idea-card:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);border-color:#c5cae9}
  .idea-card-header{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
  .idea-card-id{font-size:10px;color:#9fa8da;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
  .idea-card-title{font-size:14px;font-weight:700;color:#1a237e;margin-top:3px}
  .idea-card-meta{font-size:11px;color:#999;margin-top:8px}
  .idea-card-footer{display:flex;align-items:center;justify-content:space-between;margin-top:12px}

  /* ── FILTER BAR ── */
  .filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
  .filter-bar input,.filter-bar select{padding:8px 12px;border:1.5px solid #e0e0e0;border-radius:var(--r);font-size:13px;outline:none;transition:border-color .15s,box-shadow .15s;font-family:inherit}
  .filter-bar input:focus,.filter-bar select:focus{border-color:#3f51b5;box-shadow:0 0 0 3px rgba(63,81,181,.1)}

  /* ── TABS ── */
  .tabs{display:flex;border-bottom:2px solid #f0f2f5;margin-bottom:18px;gap:2px}
  .tab{padding:9px 18px;cursor:pointer;font-size:13px;font-weight:600;color:#aaa;border-bottom:2px solid transparent;margin-bottom:-2px;transition:color .15s,border-color .15s;border-radius:var(--r) var(--r) 0 0}
  .tab:hover{color:#3f51b5;background:#f8f9fe}
  .tab.active{color:#3f51b5;border-bottom-color:#3f51b5;background:#f8f9fe}
  .tab-content{display:none}
  .tab-content.active{display:block}

  /* ── CHIPS ── */
  .chip-filter{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
  .chip{padding:5px 14px;border-radius:20px;font-size:12px;cursor:pointer;border:1.5px solid #e0e0e0;background:#fff;transition:all .15s var(--ease);font-weight:500}
  .chip:hover{border-color:#3f51b5;color:#3f51b5}
  .chip.active{background:#3f51b5;color:#fff;border-color:#3f51b5;box-shadow:0 2px 6px rgba(63,81,181,.3)}

  /* ── MINI STATS ── */
  .mini-stat{display:flex;flex-direction:column;align-items:center;padding:16px;background:linear-gradient(135deg,#f8f9fe,#e8eaf6);border-radius:var(--r-lg);flex:1;border:1px solid #e8eaf6}
  .mini-stat-val{font-size:22px;font-weight:800;color:#1a237e}
  .mini-stat-label{font-size:11px;color:#999;margin-top:4px;text-align:center;font-weight:500}

  /* ── SPINNER ── */
  .spinner{display:inline-block;width:20px;height:20px;border:2px solid #e8eaf6;border-top-color:#3f51b5;border-radius:50%;animation:spin .7s linear infinite;vertical-align:middle}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* ── EMPTY STATE ── */
  .empty-state{text-align:center;padding:48px 20px;color:#c5cae9;font-size:13px;font-weight:500}

  /* ── USER SEARCH ── */
  .user-search-results{border:1.5px solid #e0e0e0;border-radius:var(--r);background:#fff;max-height:160px;overflow-y:auto;display:none;position:absolute;z-index:10;width:100%;box-shadow:var(--shadow-md)}
  .user-search-results .uitem{padding:9px 14px;cursor:pointer;font-size:13px;transition:background .12s}
  .user-search-results .uitem:hover{background:#f0f2f5}
  .pos-rel{position:relative}

  /* ── TOP IDEAS ── */
  .top-idea-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f5f5f5}
  .top-idea-rank{width:24px;text-align:center;font-size:11px;font-weight:800;color:#c5cae9}

  /* ── LOGIN SPLIT LAYOUT ── */
  .login-wrap{min-height:100vh;display:flex;align-items:stretch;background:none}
  .login-left{flex:0 0 440px;background:linear-gradient(160deg,#1a237e 0%,#283593 55%,#3949ab 100%);color:#fff;display:flex;flex-direction:column;justify-content:center;padding:64px 52px;overflow:hidden;position:relative}
  .login-left::before{content:'';position:absolute;top:-100px;right:-100px;width:320px;height:320px;background:rgba(255,255,255,.05);border-radius:50%;pointer-events:none}
  .login-left::after{content:'';position:absolute;bottom:-80px;left:-80px;width:260px;height:260px;background:rgba(255,255,255,.04);border-radius:50%;pointer-events:none}
  .login-feature{display:flex;align-items:center;gap:16px;margin-bottom:20px}
  .login-feature-icon{width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;backdrop-filter:blur(4px)}
  .login-feature-title{font-size:14px;font-weight:700;margin-bottom:3px}
  .login-feature-sub{font-size:12px;opacity:.65}
  .login-right{flex:1;display:flex;align-items:center;justify-content:center;background:#f5f6fa;padding:40px}
  .login-card{background:#fff;border-radius:var(--r-xl);padding:40px;width:100%;max-width:420px;box-shadow:0 4px 30px rgba(0,0,0,.1)}
  .login-logo{text-align:left;margin-bottom:28px}
  .login-logo h2{font-size:22px;color:#1a237e;font-weight:800;margin-bottom:4px}
  .login-logo p{font-size:13px;color:#aaa}

  /* ── KPI ICON ── */
  .kpi-card{display:flex;align-items:center;gap:14px}
  .kpi-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .kpi-icon svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
  .kpi-body{flex:1}

  /* ── WIZARD STEP CIRCLES ── */
  .wizard-steps{display:flex;align-items:flex-start;gap:0;margin-bottom:28px;position:relative}
  .wizard-steps::before{content:'';position:absolute;top:16px;left:10%;right:10%;height:2px;background:#e8eaf6;z-index:0}
  .w-step{flex:1;text-align:center;padding:0 4px 10px;font-size:11px;border-bottom:none;color:#bbb;cursor:pointer;font-weight:500;transition:color .15s;position:relative;z-index:1}
  .w-step .w-num{display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e8eaf6;color:#aaa;font-size:12px;font-weight:700;margin:0 auto 6px;border:2px solid #e8eaf6;transition:all .2s var(--ease)}
  .w-step .w-lbl{font-size:11px;font-weight:500;white-space:nowrap}
  .w-step.active{color:#3f51b5}
  .w-step.active .w-num{background:#3f51b5;color:#fff;border-color:#3f51b5;box-shadow:0 2px 8px rgba(63,81,181,.35)}
  .w-step.done{color:#43a047}
  .w-step.done .w-num{background:#43a047;color:#fff;border-color:#43a047}

  /* ── SECTION HEADER ACCENT ── */
  .section-header .page-title{position:relative;padding-left:12px}
  .section-header .page-title::before{content:'';position:absolute;left:0;top:2px;bottom:2px;width:3px;background:linear-gradient(180deg,#3f51b5,#7986cb);border-radius:2px}

  /* ── SCROLLBAR ── */
  #content::-webkit-scrollbar{width:6px}
  #content::-webkit-scrollbar-track{background:transparent}
  #content::-webkit-scrollbar-thumb{background:#c5cae9;border-radius:3px}
  #content::-webkit-scrollbar-thumb:hover{background:#9fa8da}

  /* ── SIDEBAR TOOLTIP (collapsed) ── */
  #sidebar.collapsed .nav-item{position:relative}
  #sidebar.collapsed .nav-item:hover::after{content:attr(data-label);position:absolute;left:52px;top:50%;transform:translateY(-50%);background:#1a237e;color:#fff;font-size:12px;font-weight:600;padding:5px 10px;border-radius:6px;white-space:nowrap;pointer-events:none;box-shadow:0 2px 8px rgba(0,0,0,.25);z-index:100}

  /* ── STATUS STRIPE ON IDEA CARDS ── */
  .idea-card[data-status="Approved"]{border-left:3px solid #43a047}
  .idea-card[data-status="Rejected"]{border-left:3px solid #e53935}
  .idea-card[data-status="Implemented"]{border-left:3px solid #7b1fa2}
  .idea-card[data-status="Under Review"]{border-left:3px solid #fb8c00}
  .idea-card[data-status="Submitted"]{border-left:3px solid #3f51b5}
  .idea-card[data-status="Draft"]{border-left:3px solid #bdbdbd}
</style>
</head>
<body>

<div id="login-page" class="login-wrap" <?= $loggedIn ? 'style="display:none"' : '' ?>>

  <div class="login-left">
    <div style="font-size:36px;font-weight:900;letter-spacing:-2px;margin-bottom:6px">IF<span style="color:#7986cb">QM</span></div>
    <h1 style="font-size:26px;font-weight:800;line-height:1.25;margin-bottom:10px">Employee Ideation Tool</h1>
    <p style="font-size:14px;opacity:.75;line-height:1.7;margin-bottom:44px">Turn great ideas into real improvements.<br>Submit, track, and get rewarded for your innovations.</p>

    <div class="login-feature">
      <div class="login-feature-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg>
      </div>
      <div><div class="login-feature-title">Submit &amp; Track Ideas</div><div class="login-feature-sub">5-step wizard with AI quality scoring</div></div>
    </div>
    <div class="login-feature">
      <div class="login-feature-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <div><div class="login-feature-title">Earn Points &amp; Rewards</div><div class="login-feature-sub">+10 submit &nbsp;&middot;&nbsp; +25 approved &nbsp;&middot;&nbsp; +65 implemented</div></div>
    </div>
    <div class="login-feature">
      <div class="login-feature-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
      </div>
      <div><div class="login-feature-title">Analytics &amp; Leaderboard</div><div class="login-feature-sub">Real-time insights across departments</div></div>
    </div>
  </div>

  <div class="login-right">
    <div class="login-card">
      <div class="login-logo">
        <h2>Welcome back</h2>
        <p>Sign in to your IFQM account to continue</p>
      </div>
      <div id="login-error" class="alert alert-danger" style="display:none"></div>
      <div class="form-group">
        <label>Email Address</label>
        <input class="form-control" id="login-email" type="email" placeholder="your.name@company.com" value="yashas.r@company.com"/>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input class="form-control" id="login-pass" type="password" placeholder="••••••••" value="password"/>
      </div>
      <button class="btn btn-primary" id="login-btn" style="width:100%;justify-content:center;padding:11px;font-size:14px" onclick="doLogin()">Sign In</button>
      <div class="separator"></div>
      <p style="font-size:11px;color:#aaa;text-align:center">Session-based Auth &middot; Role-Based Access Control &middot; MySQL Backend</p>
      <div class="separator"></div>
      <div style="background:#f8f9fe;border-radius:8px;padding:12px 14px">
        <p style="font-size:11px;color:#3f51b5;margin-bottom:6px"><strong>Demo accounts</strong> &mdash; password: <code style="background:#e8eaf6;padding:1px 5px;border-radius:4px">password</code></p>
        <p style="font-size:11px;color:#666;line-height:1.8">yashas.r@company.com &middot; priya.sharma@company.com<br>bhuvan.kh@company.com &middot; adrish.c@company.com</p>
      </div>
    </div>
  </div>

</div>

<div id="main-app" style="<?= $loggedIn ? '' : 'display:none;' ?>height:100vh">
<div id="app">

  <div id="sidebar">
    <div class="sidebar-logo">
      <span style="font-size:14px;font-weight:900;letter-spacing:-.5px;flex-shrink:0;background:rgba(255,255,255,.18);padding:3px 8px;border-radius:6px">IF</span>
      <span style="font-weight:700;letter-spacing:-.3px">IdeaTool</span>
    </div>

    <div class="nav-section">Main</div>
    <div class="nav-item active" data-label="Dashboard" onclick="navigate('dashboard',this)"><span class="icon"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg></span><span class="label">Dashboard</span></div>
    <div class="nav-item" data-label="My Ideas" onclick="navigate('my-ideas',this)"><span class="icon"><svg viewBox="0 0 24 24"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg></span><span class="label">My Ideas</span></div>
    <div class="nav-item" data-label="Submit Idea" id="nav-submit" onclick="navigate('submit',this)"><span class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg></span><span class="label">Submit Idea</span></div>

    <div class="nav-section">Workflow</div>
    <div class="nav-item" data-label="Review Queue" id="nav-review" onclick="navigate('review',this)" style="display:none"><span class="icon"><svg viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/><polyline points="9 14 11 16 15 12"/></svg></span><span class="label">Review Queue</span></div>
    <div class="nav-item" data-label="All Ideas" id="nav-all" onclick="navigate('ideas-all',this)"><span class="icon"><svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3.5" cy="6" r="1" fill="currentColor" stroke="none"/><circle cx="3.5" cy="12" r="1" fill="currentColor" stroke="none"/><circle cx="3.5" cy="18" r="1" fill="currentColor" stroke="none"/></svg></span><span class="label">All Ideas</span></div>
    <div class="nav-item" data-label="Audit Trail" id="nav-audit" onclick="navigate('audit',this)"><span class="icon"><svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="label">Audit Trail</span></div>

    <div class="nav-section">Insights</div>
    <div class="nav-item" data-label="Leaderboard" onclick="navigate('leaderboard',this)"><span class="icon"><svg viewBox="0 0 24 24"><path d="M8 21h8M12 17v4M17 3h3l-1 5a4 4 0 01-4 3M7 3H4l1 5a4 4 0 004 3"/><path d="M7 11a5 5 0 0010 0V3H7v8z"/></svg></span><span class="label">Leaderboard</span></div>
    <div class="nav-item" data-label="Analytics" id="nav-analytics" onclick="navigate('analytics',this)"><span class="icon"><svg viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></span><span class="label">Analytics</span></div>

    <div class="nav-section" id="nav-section-admin" style="display:none">Admin</div>
    <div class="nav-item" data-label="Admin Panel" id="nav-admin" onclick="navigate('admin',this)" style="display:none"><span class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 115 19.07M12 2v2M12 20v2M2 12h2M20 12h2"/></svg></span><span class="label">Admin Panel</span></div>

    <div class="nav-item" data-label="My Profile" onclick="navigate('profile',this)" style="margin-top:auto"><span class="icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg></span><span class="label">My Profile</span></div>

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
        <button style="background:none;border:none;cursor:pointer;font-size:20px;color:#555;padding:4px 6px;border-radius:6px;transition:background .15s;line-height:1" onmouseover="this.style.background='#f0f2f5'" onmouseout="this.style.background='none'" onclick="toggleSidebar()">&#9776;</button>
        <span class="page-title" id="page-title">Dashboard</span>
      </div>
      <div class="topbar-right">
        <div class="notif-bell" onclick="toggleNotif()" title="Notifications" style="display:flex;align-items:center;gap:6px">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg>
          <span>Notifications</span>
          <div class="notif-badge" id="notif-count" style="display:none">0</div>
        </div>
        <div class="user-chip" onclick="navigate('profile',null)">
          <div class="avatar" id="top-avatar">??</div>
          <span id="top-name">Loading…</span>
          <span class="role-badge" id="top-role">-</span>
        </div>
        <button class="btn btn-outline btn-sm" onclick="doLogout()">Logout</button>
      </div>
      <div class="notification-panel" id="notif-panel">
        <div style="padding:10px 14px;border-bottom:1px solid #eee;display:flex;justify-content:space-between;align-items:center">
          <strong style="font-size:13px">Notifications</strong>
          <button class="btn btn-outline btn-sm" onclick="markAllRead()">Mark all read</button>
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
            <h3 style="font-size:14px;color:#1a237e;margin-bottom:12px">Step 1: Describe the Present Situation</h3>
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
              <div id="file-situation-name" style="font-size:12px;color:#43a047;margin-top:4px"></div>
            </div>
          </div>

          <div class="wizard-body" id="step-2" style="display:none">
            <h3 style="font-size:14px;color:#1a237e;margin-bottom:12px">Step 2: Proposed Idea / Solution</h3>
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
              <div id="file-solution-name" style="font-size:12px;color:#43a047;margin-top:4px"></div>
            </div>
          </div>

          <div class="wizard-body" id="step-3" style="display:none">
            <h3 style="font-size:14px;color:#1a237e;margin-bottom:12px">Step 3: Impact Areas &amp; Measurable Benefits</h3>
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
            <h3 style="font-size:14px;color:#1a237e;margin-bottom:12px">Step 4: Co-Suggesters (Optional, max 2)</h3>
            <div class="form-group">
              <label>Co-Suggester 1</label>
              <div class="pos-rel">
                <input class="form-control" id="co1-search" placeholder="Search by name or employee ID…" oninput="searchUsers(this,'co1-results','co1-id','co1-name-display')" autocomplete="off"/>
                <div class="user-search-results" id="co1-results"></div>
              </div>
              <div id="co1-name-display" style="font-size:12px;color:#3f51b5;margin-top:4px"></div>
              <input type="hidden" id="co1-id"/>
            </div>
            <div class="form-group">
              <label>Co-Suggester 2</label>
              <div class="pos-rel">
                <input class="form-control" id="co2-search" placeholder="Search by name or employee ID…" oninput="searchUsers(this,'co2-results','co2-id','co2-name-display')" autocomplete="off"/>
                <div class="user-search-results" id="co2-results"></div>
              </div>
              <div id="co2-name-display" style="font-size:12px;color:#3f51b5;margin-top:4px"></div>
              <input type="hidden" id="co2-id"/>
            </div>
          </div>

          <div class="wizard-body" id="step-5" style="display:none">
            <h3 style="font-size:14px;color:#1a237e;margin-bottom:12px">Step 5: Review &amp; Submit</h3>
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
            <thead><tr><th>Idea ID</th><th>Title</th><th>Submitted By</th><th>Department</th><th>Impact</th><th>AI Score</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>
            <tbody id="all-ideas-tbody"><tr><td colspan="9" class="text-center"><div class="spinner"></div></td></tr></tbody>
          </table>
        </div>
      </div>

      <div class="page" id="page-audit">
        <div class="section-header">
          <div class="page-title">System Audit Trail</div>
          <div class="text-muted">Immutable append-only log of all workflow actions</div>
        </div>
        <div class="card" style="padding:0;overflow:hidden">
          <div style="font-size:11px;color:#c62828;background:#ffebee;padding:8px 14px;border-left:4px solid #e53935;font-weight:600;letter-spacing:.2px">This log is append-only and tamper-proof. No record can be edited or deleted.</div>
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

      <div class="page" id="page-profile">
        <div class="grid-2">
          <div class="card">
            <div class="card-title">Employee Profile</div>
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
              <div class="avatar" id="profile-avatar" style="width:60px;height:60px;font-size:22px;border-radius:50%">??</div>
              <div>
                <div style="font-size:18px;font-weight:700;color:#1a237e" id="profile-name">Loading…</div>
                <div style="font-size:12px;color:#666" id="profile-empid"></div>
                <span class="badge badge-submitted" id="profile-role-badge"></span>
              </div>
            </div>
            <table style="font-size:13px" id="profile-table"></table>
            <div class="separator"></div>
            <div style="font-size:11px;color:#888">Auto-fetched from HR Database. Contact Admin to update.</div>
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
        <div style="font-size:12px;color:#888" id="modal-idea-title-sub"></div>
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
      <div style="width:64px;height:64px;background:linear-gradient(135deg,#e8f5e9,#c8e6c9);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px" id="success-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2e7d32" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
      <div style="font-size:18px;font-weight:700;color:#1a237e;margin:12px 0 6px" id="success-title"></div>
      <div style="font-size:13px;color:#555" id="success-msg"></div>
      <button class="btn btn-primary mt-8" style="margin-top:20px" onclick="closeModal('modal-success');navigate('my-ideas',null)">View My Ideas</button>
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
      document.getElementById('login-page').style.display  = 'none';
      document.getElementById('main-app').style.display    = '';
      initApp();
    } else {
      err.textContent = d.error || 'Login failed.';
      err.style.display = 'block';
    }
  } catch(e) {
    err.textContent = 'Server error. Is the web server running?';
    err.style.display = 'block';
  }
  btn.disabled = false; btn.textContent = 'Login to System';
}

async function doLogout() {
  await fetch('api/auth.php?action=logout', {method:'POST'});
  currentUser = null;
  document.getElementById('main-app').style.display = 'none';
  document.getElementById('login-page').style.display = 'flex';
}

function initApp() {
  if (!currentUser) return;
  const u = currentUser;

  document.getElementById('sb-avatar').textContent  = u.avatar_initials || u.name[0];
  document.getElementById('sb-name').textContent    = u.name;
  document.getElementById('sb-role').textContent    = u.role.charAt(0).toUpperCase() + u.role.slice(1);
  document.getElementById('sb-points').textContent  = u.points + ' pts';
  document.getElementById('top-avatar').textContent = u.avatar_initials || u.name[0];
  document.getElementById('top-name').textContent   = u.name;
  document.getElementById('top-role').textContent   = u.role.charAt(0).toUpperCase() + u.role.slice(1);

  document.getElementById('submit-user-banner').innerHTML =
    `Auto-fetched from HR Database: <strong>${u.name}</strong> &middot; ${u.employee_id} &middot; ${u.department || '–'} &middot; Reporting to: ${u.manager_name || '–'} &middot; ${u.business_unit || '–'}`;

  const isPriv = ['manager','admin','executive'].includes(u.role);
  document.getElementById('nav-review').style.display        = isPriv ? '' : 'none';
  document.getElementById('nav-analytics').style.display     = isPriv ? '' : 'none';
  document.getElementById('nav-audit').style.display         = isPriv ? '' : 'none';
  document.getElementById('nav-admin').style.display         = u.role === 'admin' ? '' : 'none';
  document.getElementById('nav-section-admin').style.display = u.role === 'admin' ? '' : 'none';

  loadNotifications();
  loadDashboard();
  loadMyIdeas();
}

const pageTitles = {
  dashboard:'Dashboard', 'my-ideas':'My Ideas', submit:'Submit New Idea',
  review:'Review Queue', 'ideas-all':'All Ideas', audit:'Audit Trail',
  leaderboard:'Leaderboard & Gamification', analytics:'Analytics Dashboard',
  admin:'Admin Panel', profile:'My Profile'
};

function navigate(page, navEl) {
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  const el = document.getElementById('page-' + page);
  if (el) el.classList.add('active');
  if (navEl) navEl.classList.add('active');
  document.getElementById('page-title').textContent = pageTitles[page] || page;
  document.getElementById('notif-panel').classList.remove('open');

  if (page === 'dashboard')   loadDashboard();
  if (page === 'my-ideas')    loadMyIdeas();
  if (page === 'review')      loadReviewQueue();
  if (page === 'ideas-all')   loadAllIdeas();
  if (page === 'audit')       loadAudit();
  if (page === 'leaderboard') loadLeaderboard();
  if (page === 'analytics')   loadAnalytics();
  if (page === 'admin')       loadAdminUsers();
  if (page === 'profile')     renderProfile();
  if (page === 'submit')      resetWizard();
}

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
    <div class="kpi-card" style="border-left-color:#3f51b5">
      <div class="kpi-icon" style="background:#e8eaf6;color:#3f51b5"><svg viewBox="0 0 24 24"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${total}</div><div class="kpi-label">Total Ideas</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#fb8c00">
      <div class="kpi-icon" style="background:#fff8e1;color:#fb8c00"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${counts['Under Review']||0}</div><div class="kpi-label">Under Review</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#43a047">
      <div class="kpi-icon" style="background:#e8f5e9;color:#43a047"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${counts['Approved']||0}</div><div class="kpi-label">Approved</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#7b1fa2">
      <div class="kpi-icon" style="background:#f3e5f5;color:#7b1fa2"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${counts['Implemented']||0}</div><div class="kpi-label">Implemented</div></div>
    </div>
  `;

  const statusColors = {'Submitted':'#3f51b5','Under Review':'#fb8c00','Approved':'#43a047','Rejected':'#e53935','Implemented':'#7b1fa2'};
  const maxCount = Math.max(...Object.values(counts), 1);
  document.getElementById('dash-status-chart').innerHTML =
    Object.entries(counts).map(([s,c]) => `
      <div class="bar-row">
        <span class="bar-label">${s}</span>
        <div class="bar-track"><div class="bar-fill" style="width:${Math.round(c/maxCount*100)}%;background:${statusColors[s]||'#ccc'}"></div></div>
        <span class="bar-val">${c}</span>
      </div>`).join('');

  document.getElementById('dash-activity').innerHTML = d.recent.length
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
        </div>
      </div>
      <div class="idea-card-meta">${i.impact_areas || '—'} · ${i.submitted_at ? fmtDate(i.submitted_at) : 'Draft'}</div>
      <div class="idea-card-footer">
        <span class="badge ${impactBadge(i.impact_level)}">${i.impact_level||'–'} Impact</span>
        <div style="display:flex;gap:8px;align-items:center">
          ${i.points_awarded ? `<span class="points-badge">+${i.points_awarded} pts</span>` : ''}
          <button class="btn btn-outline btn-sm" onclick="event.stopPropagation();openIdeaDetail(${i.id})">View</button>
        </div>
      </div>
    </div>`).join('');
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
  tbody.innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner"></div></td></tr>';
  let r, d;
  try {
    r = await fetch('api/ideas.php?' + p, {credentials:'same-origin'});
    d = await r.json();
  } catch(e) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center"><div class="alert alert-danger">Failed to load ideas.</div></td></tr>';
    return;
  }
  tbody.innerHTML = (d.ideas||[]).map(i => `
    <tr>
      <td><strong>${i.idea_code}</strong></td>
      <td>${escHtml(i.title).substring(0,60)}…</td>
      <td>${escHtml(i.submitter_name)}</td>
      <td>${i.department||'–'}</td>
      <td><span class="badge ${impactBadge(i.impact_level)}">${i.impact_level||'–'}</span></td>
      <td>${i.ai_score > 0 ? `<span class="${scoreBadgeClass(i.ai_score)}">${i.ai_score}/100</span>` : '<span class="score-none">—</span>'}</td>
      <td><span class="badge ${statusBadge(i.status)}">${i.status}</span></td>
      <td>${i.submitted_at ? fmtDate(i.submitted_at) : '–'}</td>
      <td><button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View</button></td>
    </tr>`).join('') || '<tr><td colspan="9" class="text-center">No ideas found.</td></tr>';
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
      <div style="font-size:13px;color:#444;line-height:1.5">${aiReason}</div>
    </div>
  `;

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
          <div style="padding:10px 0;border-bottom:1px solid #f0f0f0">
            <div style="display:flex;align-items:center;justify-content:space-between">
              <div>
                <span style="font-size:12px;color:#888;text-transform:uppercase;margin-right:6px">${a.section}</span>
                <a href="${url}" target="_blank" style="font-size:13px;color:#3f51b5">${escHtml(a.filename)}</a>
              </div>
              <a href="${url}" download="${escHtml(a.filename)}" class="btn btn-outline btn-sm">Download</a>
            </div>
            ${preview}
          </div>`;
      }).join('')
    : '<div class="empty-state">No attachments.</div>';

  const isPriv    = ['manager','admin','executive'].includes(currentUser?.role);
  const isSelf    = parseInt(idea.submitter_id) === parseInt(currentUser?.id);
  const canReview = isPriv && !isSelf && ['Submitted','Under Review'].includes(idea.status);
  const selfNote  = isPriv && isSelf && ['Submitted','Under Review'].includes(idea.status)
    ? `<span style="font-size:12px;color:#f57f17;margin-right:10px">You cannot review your own idea</span>` : '';
  document.getElementById('idea-detail-footer').innerHTML = `
    <button class="btn btn-outline" onclick="closeModal('modal-idea-detail')">Close</button>
    ${selfNote}
    ${canReview ? `<button class="btn btn-success" onclick="closeModal('modal-idea-detail');openReviewModal(${idea.id},'${idea.idea_code}')">Review / Decide</button>` : ''}
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
    const isSelf = parseInt(i.submitter_id) === parseInt(currentUser?.id);
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
      <div class="idea-card-footer">
        <span class="badge ${impactBadge(i.impact_level)}">${i.impact_level||'–'} Impact</span>
        <div style="display:flex;gap:8px;align-items:center">
          ${isSelf ? `<span style="font-size:11px;color:#f57f17">Your own idea</span>` : ''}
          <button class="btn btn-outline btn-sm" onclick="openIdeaDetail(${i.id})">View Details</button>
          ${!isSelf ? `<button class="btn btn-success btn-sm" onclick="openReviewModal(${i.id},'${i.idea_code}')">Review</button>` : ''}
        </div>
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
    loadReviewQueue();
    loadDashboard();
  } else {
    alert('Error: ' + (d.error || 'Unknown error'));
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
  goStep(1);
}

function goStep(n) {
  for (let i = 1; i <= totalSteps; i++) {
    const el = document.getElementById('step-' + i);
    if (el) el.style.display = 'none';
  }
  document.getElementById('step-' + n).style.display = 'block';
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

let searchTimer;
async function searchUsers(input, resultsId, hiddenId, displayId) {
  clearTimeout(searchTimer);
  const q = input.value.trim();
  const el = document.getElementById(resultsId);
  if (q.length < 2) { el.style.display = 'none'; return; }
  searchTimer = setTimeout(async () => {
    const r = await fetch('api/users.php?action=list&q=' + encodeURIComponent(q), {credentials:'same-origin'});
    const d = await r.json();
    if (!d.users?.length) { el.innerHTML = '<div class="uitem">No users found.</div>'; el.style.display = 'block'; return; }
    el.innerHTML = d.users.map(u => `
      <div class="uitem" onclick="selectUser('${u.id}','${escHtml(u.name)}','${u.employee_id}','${hiddenId}','${displayId}','${resultsId}','${input.id}')">
        ${escHtml(u.name)} · ${u.employee_id} · ${u.department||'–'}
      </div>`).join('');
    el.style.display = 'block';
  }, 300);
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
  document.getElementById('lb-individuals').innerHTML = (d.individuals||[]).map((u,i) => `
    <div class="lb-row">
      <div class="lb-rank ${i===0?'rank-1':i===1?'rank-2':i===2?'rank-3':'rank-n'}">${i+1}</div>
      <div class="avatar">${u.avatar_initials||u.name[0]}</div>
      <div style="flex:1">
        <div class="lb-name">${escHtml(u.name)} ${u.id==currentUser?.id?'<span style="font-size:11px;color:#fb8c00">(You)</span>':''}</div>
        <div class="lb-dept">${u.department||'–'}</div>
        <div class="progress-bar mt-8"><div class="progress-fill" style="width:${Math.round(u.points/maxPts*100)}%"></div></div>
      </div>
      <div style="text-align:right">
        <div class="lb-points">${u.points} pts</div>
        <div class="lb-ideas">${u.idea_count||0} ideas</div>
        ${u.avg_score > 0 ? `<span class="${scoreBadgeClass(u.avg_score)}" style="margin-top:2px;display:inline-block">Avg ${u.avg_score}</span>` : ''}
      </div>
    </div>`).join('') || '<div class="empty-state">No data yet.</div>';

  const maxDeptPts = Math.max(...(d.departments||[]).map(dep=>dep.dept_points), 1);
  document.getElementById('lb-departments').innerHTML = `
    <div class="bar-chart">${(d.departments||[]).map(dept => `
      <div class="bar-row">
        <span class="bar-label">${escHtml(dept.department||'–').substring(0,12)}</span>
        <div class="bar-track"><div class="bar-fill" style="width:${Math.round((dept.dept_points||0)/maxDeptPts*100)}%;background:#3f51b5"></div></div>
        <span class="bar-val">${dept.dept_points||0}</span>
      </div>`).join('')}</div>`;

  if (d.top_ideas && d.top_ideas.length) {
    document.getElementById('lb-top-ideas').innerHTML = d.top_ideas.map((idea, idx) => `
      <div class="top-idea-row">
        <div class="top-idea-rank">#${idx+1}</div>
        <div style="flex:1">
          <div style="font-size:13px;font-weight:600;color:#1a237e">${escHtml(idea.title)}</div>
          <div style="font-size:11px;color:#888">${idea.idea_code} · ${escHtml(idea.submitter_name)} · ${idea.department||'–'}</div>
        </div>
        <div style="text-align:right">
          <span class="${scoreBadgeClass(idea.ai_score)}">${idea.ai_score}/100</span>
          <div style="font-size:11px;color:#888;margin-top:2px"><span class="badge ${statusBadge(idea.status)}">${idea.status}</span></div>
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
    <div class="kpi-card" style="border-left-color:#3f51b5">
      <div class="kpi-icon" style="background:#e8eaf6;color:#3f51b5"><svg viewBox="0 0 24 24"><path d="M9 21h6M12 3a6 6 0 016 6c0 2.2-1.1 3.8-2.5 5L15 16H9l-.5-2C7 12.8 6 11.2 6 9a6 6 0 016-6z"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${total}</div><div class="kpi-label">Total Ideas</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#43a047">
      <div class="kpi-icon" style="background:#e8f5e9;color:#43a047"><svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${total ? Math.round(approved/total*100) : 0}%</div><div class="kpi-label">Approval Rate</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#f57c00">
      <div class="kpi-icon" style="background:#fff3e0;color:#f57c00"><svg viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${total ? Math.round(impl/total*100) : 0}%</div><div class="kpi-label">Implementation Rate</div></div>
    </div>
    <div class="kpi-card" style="border-left-color:#3f51b5">
      <div class="kpi-icon" style="background:#e8eaf6;color:#3f51b5"><svg viewBox="0 0 24 24"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6z"/></svg></div>
      <div class="kpi-body"><div class="kpi-val">${ss.overall_avg || 0}</div><div class="kpi-label">Avg AI Quality Score</div></div>
    </div>
  `;

  const impColors = ['#3f51b5','#5c6bc0','#7986cb','#9fa8da','#c5cae9','#e8eaf6'];
  const maxImp = Math.max(...Object.values(d.impact_distribution||{}), 1);
  document.getElementById('analytics-impact').innerHTML = Object.entries(d.impact_distribution||{}).map(([k,v],i) => `
    <div class="bar-row">
      <span class="bar-label">${k}</span>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.round(v/maxImp*100)}%;background:${impColors[i%impColors.length]}"></div></div>
      <span class="bar-val">${v}</span>
    </div>`).join('') || '<div class="empty-state">No data yet.</div>';

  const statusColors = {'Submitted':'#3f51b5','Under Review':'#fb8c00','Approved':'#43a047','Rejected':'#e53935','Implemented':'#7b1fa2','Draft':'#9e9e9e'};
  document.getElementById('analytics-status').innerHTML = `
    <div class="bar-chart">${(d.status_summary||[]).map(s => `
      <div class="bar-row">
        <span class="bar-label">${s.status}</span>
        <div class="bar-track"><div class="bar-fill" style="width:${Math.round(s.cnt/Math.max(total,1)*100)}%;background:${statusColors[s.status]||'#ccc'}"></div></div>
        <span class="bar-val">${s.cnt}</span>
      </div>`).join('')}</div>`;

  const maxTrend = Math.max(...(d.trend||[]).map(t=>t.total), 1);
  document.getElementById('analytics-trend').innerHTML = (d.trend||[]).reverse().map(t => `
    <div class="bar-row">
      <span class="bar-label">${t.month}</span>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.round(t.total/maxTrend*100)}%;background:#3f51b5"></div></div>
      <span class="bar-val">${t.total}</span>
    </div>`).join('') || '<div class="empty-state">No trend data yet.</div>';

  const hq = parseInt(ss.high_quality || 0);
  const mq = parseInt(ss.medium_quality || 0);
  const lq = parseInt(ss.low_quality || 0);
  const maxQ = Math.max(hq, mq, lq, 1);
  document.getElementById('analytics-score-dist').innerHTML = `
    <div class="bar-chart">
      <div class="bar-row"><span class="bar-label">High (75+)</span><div class="bar-track"><div class="bar-fill" style="width:${Math.round(hq/maxQ*100)}%;background:#43a047"></div></div><span class="bar-val">${hq}</span></div>
      <div class="bar-row"><span class="bar-label">Med (50-74)</span><div class="bar-track"><div class="bar-fill" style="width:${Math.round(mq/maxQ*100)}%;background:#fb8c00"></div></div><span class="bar-val">${mq}</span></div>
      <div class="bar-row"><span class="bar-label">Low (&lt;50)</span><div class="bar-track"><div class="bar-fill" style="width:${Math.round(lq/maxQ*100)}%;background:#e53935"></div></div><span class="bar-val">${lq}</span></div>
    </div>
    <div style="font-size:11px;color:#888;margin-top:8px">Overall average AI score: <strong>${ss.overall_avg || 0}/100</strong></div>`;
}

async function loadAdminUsers() {
  const r = await fetch('api/users.php?action=list&q=', {credentials:'same-origin'});
  const d = await r.json();
  document.getElementById('admin-users-tbody').innerHTML = (d.users||[]).map(u => `
    <tr>
      <td><div style="display:flex;align-items:center;gap:8px"><div class="avatar">${u.avatar_initials||u.name[0]}</div>${escHtml(u.name)}</div></td>
      <td>${u.department||'–'}</td>
      <td>${u.email}</td>
      <td><span class="badge badge-submitted">${u.role}</span></td>
      <td>–</td>
    </tr>`).join('') || '<tr><td colspan="5" class="text-center">No users found.</td></tr>';
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
  document.getElementById('profile-role-badge').textContent = u.role.charAt(0).toUpperCase() + u.role.slice(1);
  document.getElementById('profile-table').innerHTML = `
    <tr><td style="color:#888;padding:5px 0">Department</td><td>${u.department||'–'}</td></tr>
    <tr><td style="color:#888;padding:5px 0">Email</td><td>${u.email}</td></tr>
    <tr><td style="color:#888;padding:5px 0">Phone</td><td>${u.phone||'–'}</td></tr>
    <tr><td style="color:#888;padding:5px 0">Reporting To</td><td>${u.manager_name||'–'}</td></tr>
    <tr><td style="color:#888;padding:5px 0">Business Unit</td><td>${u.business_unit||'–'}</td></tr>
    <tr><td style="color:#888;padding:5px 0">Location</td><td>${u.location||'–'}</td></tr>
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
</body>
</html>
