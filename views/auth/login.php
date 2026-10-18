<?php
/**
 * MSEC SMART STOCK - Enterprise Institutional Landing & Multi-Section Authentication Portal
 * Mohamed Sathak Engineering College (Autonomous), Kilakarai
 * Approved by AICTE, Affiliated to Anna University, Accredited by NAAC 'A+' Grade
 * 
 * Enforces Institutional Security: Role-first & Department-first authentication workflow.
 */

$error = $_GET['error'] ?? null;
$msg = $_GET['msg'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    
    <!-- Apple iOS Safari Web App Optimizations -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MSEC Stock">
    <meta name="format-detection" content="telephone=no">
    <link rel="apple-touch-icon" href="images/msec_logo.png">

    <!-- Android Chrome & Material Design Web App Optimizations -->
    <meta name="theme-color" content="#071322">
    <meta name="mobile-web-app-capable" content="yes">

    <title>Sign In — <?= APP_NAME ?> | <?= APP_INSTITUTION_FULL ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-primary: #102a43;
            --brand-navy-dark: #071322;
            --brand-accent: #2563eb;
            --brand-accent-hover: #1d4ed8;
            --brand-emerald: #059669;
            --brand-purple: #7c3aed;
            --brand-amber: #d97706;
            --brand-crimson: #dc2626;
            --card-glass: rgba(255, 255, 255, 0.97);
            --card-border: rgba(255, 255, 255, 0.2);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: radial-gradient(circle at 12% 15%, #183b68 0%, #0d2138 45%, #050d17 100%);
            min-height: 100vh;
            min-height: -webkit-fill-available;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-top: max(20px, env(safe-area-inset-top));
            padding-bottom: max(24px, calc(env(safe-area-inset-bottom) + 84px));
            padding-left: max(14px, env(safe-area-inset-left));
            padding-right: max(14px, env(safe-area-inset-right));
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            -webkit-tap-highlight-color: transparent;
        }

        /* Ambient Background Highlights */
        body::before {
            content: "";
            position: absolute;
            top: -160px;
            left: 8%;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.22), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }
        body::after {
            content: "";
            position: absolute;
            bottom: -160px;
            right: 8%;
            width: 560px;
            height: 560px;
            background: radial-gradient(circle, rgba(217, 119, 6, 0.15), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .portal-container {
            width: 1260px;
            max-width: 100%;
            position: relative;
            z-index: 1;
        }

        /* Institutional Header Bar */
        .portal-header-bar {
            background: rgba(16, 42, 67, 0.88);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 20px;
            padding: 18px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            box-shadow: 0 16px 36px -10px rgba(0, 0, 0, 0.4);
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .college-emblem-img {
            width: 66px;
            height: 66px;
            background: #ffffff;
            border-radius: 50%;
            padding: 4px;
            object-fit: contain;
            box-shadow: 0 6px 14px rgba(0,0,0,0.35);
            border: 2px solid #fbbf24;
            transition: transform 0.3s ease;
        }

        .college-emblem-img:hover {
            transform: scale(1.05);
        }

        .header-title-box h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 23px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1.2;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title-box h1 .tag-autonomous {
            font-size: 11px;
            font-weight: 700;
            background: rgba(251, 191, 36, 0.22);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.45);
            padding: 3px 8px;
            border-radius: 6px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .header-inst-name {
            font-size: 13.5px;
            color: #93c5fd;
            font-weight: 600;
            margin-top: 3px;
            letter-spacing: 0.2px;
        }

        .header-inst-meta {
            font-size: 11px;
            color: #cbd5e1;
            margin-top: 2px;
        }

        .header-badges {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .badge-pill {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .badge-pill.gold {
            background: rgba(251, 191, 36, 0.16);
            border-color: rgba(251, 191, 36, 0.4);
            color: #fef08a;
        }

        /* Main Workspace Split Grid */
        .portal-grid {
            display: grid;
            grid-template-columns: 1.38fr 1fr;
            gap: 22px;
            align-items: stretch;
        }

        /* Left Card: Role & Department Directory */
        .section-picker-card {
            background: var(--card-glass);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.35);
            padding: 26px 26px;
            display: flex;
            flex-direction: column;
        }

        .picker-header {
            margin-bottom: 16px;
        }

        .picker-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 19px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .picker-header p {
            font-size: 12.5px;
            color: #64748b;
            margin-top: 4px;
        }

        /* Institutional Category Navigation Tabs */
        .category-nav-tabs {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            background: #f1f5f9;
            padding: 6px;
            border-radius: 12px;
            margin-bottom: 18px;
            border: 1px solid #e2e8f0;
        }

        .cat-tab-btn {
            background: none;
            border: none;
            padding: 10px 8px;
            border-radius: 8px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            text-align: center;
        }

        .cat-tab-btn:hover {
            color: #102a43;
            background: rgba(255, 255, 255, 0.8);
        }

        .cat-tab-btn.active {
            background: #ffffff;
            color: #102a43;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
        }

        .cat-tab-btn .tab-icon {
            font-size: 17px;
        }

        /* Department Selector Pills Bar */
        .dept-pills-bar {
            display: flex;
            gap: 6px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 1px solid #e2e8f0;
            scrollbar-width: thin;
        }

        .dept-pills-bar::-webkit-scrollbar {
            height: 4px;
        }
        .dept-pills-bar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .dept-pill-btn {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            white-space: nowrap;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .dept-pill-btn:hover {
            border-color: #2563eb;
            color: #2563eb;
            background: #eff6ff;
        }

        .dept-pill-btn.active {
            background: #102a43;
            color: #ffffff;
            border-color: #102a43;
            box-shadow: 0 3px 8px rgba(16, 42, 67, 0.2);
        }

        /* Personnel Role Cards Grid */
        .role-cards-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 11px;
            flex: 1;
            align-content: start;
            overflow-y: auto;
            max-height: 490px;
            padding-right: 4px;
            scrollbar-width: thin;
        }

        .role-cards-grid::-webkit-scrollbar {
            width: 5px;
        }
        .role-cards-grid::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .persona-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 13px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            text-align: left;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }

        .persona-card:hover {
            transform: translateY(-2px);
            border-color: #2563eb;
            background: #f8fafc;
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.12);
        }

        .persona-card.selected {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: 0 0 0 2.5px #2563eb, 0 6px 16px rgba(37, 99, 235, 0.15);
        }

        .persona-top {
            display: flex;
            align-items: flex-start;
            gap: 11px;
            margin-bottom: 6px;
        }

        .persona-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
            flex-shrink: 0;
            background: #e2e8f0;
            color: #1e293b;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);
        }

        .persona-card.selected .persona-avatar {
            background: #2563eb;
            color: #ffffff;
        }

        .persona-info {
            flex: 1;
            min-width: 0;
        }

        .persona-role-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .persona-name {
            font-size: 11.5px;
            color: #475569;
            margin-top: 2px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .persona-badge {
            font-size: 10px;
            font-weight: 700;
            padding: 2.5px 6.5px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 5px;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .badge-sanction { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-gateway  { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-lab      { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-admin    { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
        .badge-finance  { background: #fce7f3; color: #9d174d; border: 1px solid #fbcfe8; }
        .badge-purple   { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }

        .persona-email {
            font-size: 10.5px;
            color: #64748b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            margin-top: 5px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Right Card: Dynamic Context-Aware Login Box */
        .login-box-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.35);
            padding: 28px 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .active-role-banner {
            background: linear-gradient(135deg, #102a43 0%, #1e429f 100%);
            border-radius: 14px;
            padding: 16px 18px;
            color: #ffffff;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 18px rgba(16, 42, 67, 0.25);
            transition: all 0.3s ease;
        }

        .active-role-banner::after {
            content: "";
            position: absolute;
            right: -25px;
            bottom: -25px;
            width: 110px;
            height: 110px;
            background: radial-gradient(circle, rgba(255,255,255,0.18), transparent 70%);
            border-radius: 50%;
        }

        .active-banner-badge {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            padding: 3px 8px;
            border-radius: 999px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .active-banner-name {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 17.5px;
            font-weight: 700;
            color: #ffffff;
            line-height: 1.25;
        }

        .active-banner-scope {
            font-size: 11.5px;
            color: #bfdbfe;
            margin-top: 5px;
            line-height: 1.45;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 5px;
        }

        .input-wrap {
            position: relative;
            margin-bottom: 14px;
        }

        .form-control {
            width: 100%;
            padding: 11px 13px;
            border: 1.5px solid #cbd5e1;
            border-radius: 9px;
            font-size: 13.5px;
            font-family: inherit;
            color: #0f172a;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .toggle-pass-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
        }

        .btn-portal-submit {
            width: 100%;
            padding: 13px;
            background: #102a43;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(16, 42, 67, 0.2);
        }

        .btn-portal-submit:hover {
            background: #17365d;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(16, 42, 67, 0.3);
        }

        .security-advisory-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #2563eb;
            border-radius: 8px;
            padding: 11px 13px;
            font-size: 11px;
            color: #475569;
            line-height: 1.45;
            margin-top: 16px;
        }

        .security-advisory-box strong {
            color: #0f172a;
            display: block;
            margin-bottom: 2px;
        }

        .alert-box {
            padding: 11px 13px;
            border-radius: 8px;
            font-size: 12.5px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-box.error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .alert-box.success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        /* Touch Optimization for iOS & Android */
        button, a, input, select, .persona-card, .dept-pill-btn, .cat-tab-btn {
            touch-action: manipulation;
            -webkit-tap-highlight-color: transparent;
        }

        .persona-card:active, .cat-tab-btn:active, .dept-pill-btn:active, .btn-portal-submit:active {
            transform: scale(0.97);
        }

        /* Mobile Segmented Switcher (Default Hidden on Desktop) */
        .mobile-segmented-bar {
            display: none;
        }

        /* Floating Quick Sign In Pill (Default Hidden on Desktop) */
        .mobile-floating-pill {
            display: none;
        }

        /* Responsive Design: Tablets & Phones (iOS / Android) */
        @media (max-width: 960px) {
            .portal-container {
                width: 100%;
            }

            .portal-header-bar {
                padding: 16px 18px;
                border-radius: 16px;
                margin-bottom: 12px;
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            .college-emblem-img {
                width: 52px;
                height: 52px;
            }

            .header-brand {
                flex-direction: column;
                gap: 10px;
            }

            .header-title-box h1 {
                font-size: 20px;
                justify-content: center;
            }

            .header-inst-name {
                font-size: 12.5px;
            }

            .header-badges {
                justify-content: center;
                gap: 6px;
            }

            .badge-pill {
                padding: 4px 10px;
                font-size: 10.5px;
            }

            /* Mobile Native-Style Segmented Navigation */
            .mobile-segmented-bar {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 6px;
                background: rgba(16, 42, 67, 0.95);
                backdrop-filter: blur(14px);
                -webkit-backdrop-filter: blur(14px);
                padding: 5px;
                border-radius: 12px;
                margin-bottom: 14px;
                border: 1px solid rgba(255, 255, 255, 0.15);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
                position: sticky;
                top: max(10px, env(safe-area-inset-top));
                z-index: 99;
            }

            .seg-btn {
                background: transparent;
                border: none;
                padding: 10px 8px;
                border-radius: 8px;
                font-family: inherit;
                font-size: 12px;
                font-weight: 700;
                color: #cbd5e1;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                transition: all 0.2s ease;
            }

            .seg-btn.active {
                background: #2563eb;
                color: #ffffff;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
            }

            .seg-badge {
                font-size: 9.5px;
                background: rgba(255, 255, 255, 0.22);
                padding: 2px 7px;
                border-radius: 999px;
                font-weight: 700;
                max-width: 90px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .portal-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            /* Toggle between Directory and Login console on mobile */
            .portal-grid.view-directory .login-box-card {
                display: none;
            }
            .portal-grid.view-directory .section-picker-card {
                display: flex;
            }
            .portal-grid.view-login .section-picker-card {
                display: none;
            }
            .portal-grid.view-login .login-box-card {
                display: flex;
            }

            .section-picker-card, .login-box-card {
                padding: 18px 16px;
                border-radius: 16px;
            }

            /* Crucial iOS Safari fix: 16px minimum prevents viewport zoom on focus */
            .form-control {
                font-size: 16px !important;
                min-height: 48px;
                padding: 12px 14px;
            }

            .btn-portal-submit {
                min-height: 48px;
                font-size: 14.5px;
            }

            /* Floating Quick Sign In Pill for Mobile Thumb Reachability */
            .mobile-floating-pill {
                display: flex;
                align-items: center;
                justify-content: space-between;
                position: fixed;
                bottom: max(12px, env(safe-area-inset-bottom));
                left: 12px;
                right: 12px;
                background: rgba(16, 42, 67, 0.96);
                backdrop-filter: blur(18px);
                -webkit-backdrop-filter: blur(18px);
                border: 1px solid rgba(255, 255, 255, 0.22);
                border-radius: 16px;
                padding: 8px 12px;
                box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(37, 99, 235, 0.35);
                z-index: 100;
                animation: pillSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            @keyframes pillSlideUp {
                from { transform: translateY(100%); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }

            .pill-info-area {
                display: flex;
                align-items: center;
                gap: 10px;
                flex: 1;
                min-width: 0;
                cursor: pointer;
            }

            .pill-avatar {
                width: 36px;
                height: 36px;
                border-radius: 9px;
                background: #2563eb;
                color: #ffffff;
                font-weight: 800;
                font-size: 13px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .pill-meta {
                flex: 1;
                min-width: 0;
            }

            .pill-name {
                font-size: 12px;
                font-weight: 700;
                color: #ffffff;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .pill-role {
                font-size: 10.5px;
                color: #93c5fd;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .pill-action-btn {
                background: #2563eb;
                color: #ffffff;
                border: none;
                padding: 10px 14px;
                border-radius: 10px;
                font-family: inherit;
                font-size: 12px;
                font-weight: 700;
                cursor: pointer;
                white-space: nowrap;
                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
                transition: all 0.15s ease;
                flex-shrink: 0;
            }

            .pill-action-btn:active {
                transform: scale(0.96);
                background: #1d4ed8;
            }
        }

        @media (max-width: 640px) {
            .category-nav-tabs {
                grid-template-columns: repeat(2, 1fr);
                gap: 6px;
                padding: 4px;
            }

            .cat-tab-btn {
                min-height: 42px;
                padding: 8px 4px;
                font-size: 11px;
            }

            .dept-pills-bar {
                padding-bottom: 6px;
                margin-bottom: 10px;
                scroll-snap-type: x proximity;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
            }

            .dept-pills-bar::-webkit-scrollbar {
                display: none;
            }

            .dept-pill-btn {
                padding: 7px 12px;
                font-size: 12px;
                scroll-snap-align: start;
            }

            .role-cards-grid {
                grid-template-columns: 1fr;
                gap: 9px;
                max-height: 440px;
                -webkit-overflow-scrolling: touch;
            }

            .persona-card {
                padding: 12px 14px;
            }

            .persona-role-title {
                font-size: 13px;
            }

            .persona-name {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>

<div class="portal-container">

    <!-- Institutional Header Bar -->
    <header class="portal-header-bar">
        <div class="header-brand">
            <img src="images/msec_logo.png" alt="MSEC Crest" class="college-emblem-img">
            <div class="header-title-box">
                <h1>
                    <span><?= APP_NAME ?></span>
                    <span class="tag-autonomous">Autonomous</span>
                </h1>
                <div class="header-inst-name"><?= APP_INSTITUTION_FULL ?></div>
                <div class="header-inst-meta">
                    <?= APP_INSTITUTION_TAGLINE ?> &bull; <?= APP_INSTITUTION_LOCATION ?> &bull; Estd. 1984
                </div>
            </div>
        </div>

        <div class="header-badges">
            <div class="badge-pill gold">⭐ NAAC A+ Grade</div>
            <div class="badge-pill">🏛️ AICTE & NBA Approved</div>
            <div class="badge-pill">🔒 256-Bit SSL Secured</div>
        </div>
    </header>

    <!-- Mobile Segmented View Switcher (iOS & Android) -->
    <div class="mobile-segmented-bar">
        <button type="button" class="seg-btn active" id="segBtnDirectory" onclick="switchMobileView('directory')">
            <span class="seg-icon">🏛️</span>
            <span>1. Select Role</span>
        </button>
        <button type="button" class="seg-btn" id="segBtnLogin" onclick="switchMobileView('login')">
            <span class="seg-icon">🔐</span>
            <span>2. Sign In</span>
            <span class="seg-badge" id="mobileSegBadge">CSE • HOD</span>
        </button>
    </div>

    <!-- Main Workspace Split Grid -->
    <main class="portal-grid view-directory" id="portalGrid">

        <!-- Left: Categorized Institutional Section & Persona Directory -->
        <section class="section-picker-card">
            <div class="picker-header">
                <h2><span>🏛️</span> Institutional Section & Stakeholder Directory</h2>
                <p>Select your department or institutional section to configure your official login console:</p>
            </div>

            <!-- Category Navigation Tabs -->
            <div class="category-nav-tabs">
                <button type="button" class="cat-tab-btn active" onclick="switchCategory('academic')">
                    <span class="tab-icon">🎓</span>
                    <span>Academic & Labs</span>
                </button>
                <button type="button" class="cat-tab-btn" onclick="switchCategory('stores')">
                    <span class="tab-icon">🏬</span>
                    <span>Stores & Purchase</span>
                </button>
                <button type="button" class="cat-tab-btn" onclick="switchCategory('directorate')">
                    <span class="tab-icon">🏛️</span>
                    <span>Directorate</span>
                </button>
                <button type="button" class="cat-tab-btn" onclick="switchCategory('admin')">
                    <span class="tab-icon">🛡️</span>
                    <span>Admin & Audit</span>
                </button>
            </div>

            <!-- SECTION 1: ACADEMIC DEPARTMENTS & CENTRAL FACILITIES -->
            <div id="cat-academic" class="category-content-pane">
                
                <!-- Department Navigation Bar -->
                <div class="dept-pills-bar">
                    <button type="button" class="dept-pill-btn active" onclick="switchDept('CSE')">💻 CSE</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('IT')">🌐 IT</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('AIDS')">🤖 AI&DS</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('ECE')">📡 ECE</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('EEE')">⚡ EEE</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('MECH')">⚙️ MECH</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('CIVIL')">🏗️ CIVIL</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('LIB')">📚 Library</button>
                    <button type="button" class="dept-pill-btn" onclick="switchDept('INCUBATION')">🚀 Incubation</button>
                </div>

                <!-- 1. CSE Department Role Cards (100% Real Institutional Data) -->
                <div id="dept-pane-CSE" class="role-cards-grid dept-pane">
                    <!-- HOD -->
                    <div class="persona-card selected" onclick="selectPersona('csehod@msec.org.in', 'Mr. N. Balasubramanian', 'Head of Department (HOD)', 'Computer Science & Engineering', 'Scrutinizes and endorses departmental indents & lab requisitions; forwards through VP & Principal for Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Mr. N. Balasubramanian</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">csehod@msec.org.in</span>
                    </div>

                    <!-- Stock Coordinator -->
                    <div class="persona-card" onclick="selectPersona('seenipulavar2025@gmail.com', 'Mr. K. Seeni Pulavar Pitchai', 'Stock Dept Coordinator', 'Computer Science & Engineering', 'Performs technical scrutiny, verifies existing departmental balances, and initiates inter-dept transfers.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Mr. K. Seeni Pulavar Pitchai</div>
                                <span class="persona-badge badge-admin">Scrutiny & Stock Check</span>
                            </div>
                        </div>
                        <span class="persona-email">seenipulavar2025@gmail.com</span>
                    </div>

                    <!-- Lab Incharge 1: Linux & CASE Tools -->
                    <div class="persona-card" onclick="selectPersona('merlinrithika21@gmail.com', 'Ms. M.K. Merlin Rithika', 'Lab In-Charge (Linux & CASE)', 'Computer Science & Engineering', 'Custody of Linux Lab & CASE Tools Lab registers, damage reporting, consumable usage logs, and service indents.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Ms. M.K. Merlin Rithika</div>
                                <span class="persona-badge badge-lab">Linux & CASE Tools Labs</span>
                            </div>
                        </div>
                        <span class="persona-email">merlinrithika21@gmail.com</span>
                    </div>

                    <!-- Lab Incharge 2: Android -->
                    <div class="persona-card" onclick="selectPersona('Jothichandran1994@gmail.com', 'Ms. C. Mangala Jothi', 'Lab In-Charge (Android Lab)', 'Computer Science & Engineering', 'Custody of Android Lab registers, hardware terminals, damage reporting, and new product/service requests.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Ms. C. Mangala Jothi</div>
                                <span class="persona-badge badge-lab">Android Laboratory</span>
                            </div>
                        </div>
                        <span class="persona-email">Jothichandran1994@gmail.com</span>
                    </div>

                    <!-- Technician CSE -->
                    <div class="persona-card" onclick="selectPersona('tech.cse@msec.edu.in', 'T. Murugan', 'Senior Lab Technician', 'Computer Science & Engineering', 'Logs equipment preventative maintenance, diagnostic reports, and physical hardware repair tickets.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">TCH</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab Technician</div>
                                <div class="persona-name">T. Murugan</div>
                                <span class="persona-badge badge-admin">Hardware Servicing & Maint.</span>
                            </div>
                        </div>
                        <span class="persona-email">tech.cse@msec.edu.in</span>
                    </div>
                </div>

                <!-- 2. IT Department Role Cards -->
                <div id="dept-pane-IT" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('ithod@msec.org.in', 'Dr. K. Karthikeyan', 'Head of Department (HOD)', 'Information Technology', 'Scrutinizes and endorses departmental indents & cloud lab quotas; forwards through VP & Principal for Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Dr. K. Karthikeyan</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">ithod@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('itcoord@msec.org.in', 'Er. M. Naveen Kumar', 'Stock Dept Coordinator', 'Information Technology', 'Performs technical verification, audit checks on IT assets, server appliances, and consumable balance.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Er. M. Naveen Kumar</div>
                                <span class="persona-badge badge-admin">Scrutiny & Network Stock</span>
                            </div>
                        </div>
                        <span class="persona-email">itcoord@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('itlab@msec.org.in', 'Mrs. P. Priya', 'Lab In-Charge (Network Security)', 'Information Technology', 'Custody of Network Security & App Development Lab, routers, switches, and software licensing ledgers.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Mrs. P. Priya</div>
                                <span class="persona-badge badge-lab">Network Security Lab</span>
                            </div>
                        </div>
                        <span class="persona-email">itlab@msec.org.in</span>
                    </div>
                </div>

                <!-- 3. AIDS Department Role Cards -->
                <div id="dept-pane-AIDS" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('aidshod@msec.org.in', 'Dr. V. Senthil Murugan', 'Head of Department (HOD)', 'AI & Data Science', 'Scrutinizes and endorses AI compute indents; forwards through VP & Principal for Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Dr. V. Senthil Murugan</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">aidshod@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('aidscoord@msec.org.in', 'Er. R. Vignesh', 'Stock Dept Coordinator', 'AI & Data Science', 'GPU server asset tracking, high-end compute equipment audit, and consumable quota management.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Er. R. Vignesh</div>
                                <span class="persona-badge badge-admin">GPU Asset Scrutiny</span>
                            </div>
                        </div>
                        <span class="persona-email">aidscoord@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('aidslab@msec.org.in', 'Mr. S. Dinesh', 'Lab In-Charge (Deep Learning)', 'AI & Data Science', 'Physical custody of AI High-Performance Computing Research Lab and workstation inventory registers.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Mr. S. Dinesh</div>
                                <span class="persona-badge badge-lab">Deep Learning Lab</span>
                            </div>
                        </div>
                        <span class="persona-email">aidslab@msec.org.in</span>
                    </div>
                </div>

                <!-- 4. ECE Department Role Cards -->
                <div id="dept-pane-ECE" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('ecehod@msec.org.in', 'Dr. S. Syed Abuthahir', 'Head of Department (HOD)', 'Electronics & Communication Eng.', 'Endorses ECE laboratory indents and signal equipment requisitions for institutional review and Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Dr. S. Syed Abuthahir</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">ecehod@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('ececoord@msec.org.in', 'Er. K. Ramesh', 'Stock Dept Coordinator', 'Electronics & Communication Eng.', 'Scrutinizes electronic component stock, oscilloscopes, IC tester inventory, and sub-store ledger.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Er. K. Ramesh</div>
                                <span class="persona-badge badge-admin">ECE Stores & Components</span>
                            </div>
                        </div>
                        <span class="persona-email">ececoord@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('ecelab@msec.org.in', 'Mrs. M. Kavitha', 'Lab In-Charge (Comm Systems)', 'Electronics & Communication Eng.', 'Physical custody of Communication Systems Lab, spectrum analyzers, and RF test equipment.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Mrs. M. Kavitha</div>
                                <span class="persona-badge badge-lab">Comm Systems Lab</span>
                            </div>
                        </div>
                        <span class="persona-email">ecelab@msec.org.in</span>
                    </div>
                </div>

                <!-- 5. EEE Department Role Cards -->
                <div id="dept-pane-EEE" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('eeehod@msec.org.in', 'Dr. A. Raja', 'Head of Department (HOD)', 'Electrical & Electronics Eng.', 'Endorses electrical machinery and lab equipment requisitions for institutional review and Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Dr. A. Raja</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">eeehod@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('eeecoord@msec.org.in', 'Er. S. Manikandan', 'Stock Dept Coordinator', 'Electrical & Electronics Eng.', 'Performs electrical store audits, motor winding inventories, and consumable copper/fuses registry.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Er. S. Manikandan</div>
                                <span class="persona-badge badge-admin">Electrical Machinery Audit</span>
                            </div>
                        </div>
                        <span class="persona-email">eeecoord@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('eeelab@msec.org.in', 'Mr. B. Suresh', 'Lab In-Charge (Power Electronics)', 'Electrical & Electronics Eng.', 'Custody of Power Electronics & Drives Lab, transformers, inverters, and switchgear machinery.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Mr. B. Suresh</div>
                                <span class="persona-badge badge-lab">Power Electronics Lab</span>
                            </div>
                        </div>
                        <span class="persona-email">eeelab@msec.org.in</span>
                    </div>
                </div>

                <!-- 6. MECH Department Role Cards -->
                <div id="dept-pane-MECH" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('mechhod@msec.org.in', 'Dr. M. Ganesan', 'Head of Department (HOD)', 'Mechanical Engineering', 'Endorses machine shop tooling and workshop indents for institutional review and Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Dr. M. Ganesan</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">mechhod@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('mechcoord@msec.org.in', 'Er. P. Vijayakumar', 'Stock Dept Coordinator', 'Mechanical Engineering', 'Manages central mechanical tool crib, cutting inserts, lathe machinery, and raw metal bars.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Er. P. Vijayakumar</div>
                                <span class="persona-badge badge-admin">Tool Crib & Workshop Audit</span>
                            </div>
                        </div>
                        <span class="persona-email">mechcoord@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('mechlab@msec.org.in', 'Mr. R. Anand', 'Lab In-Charge (CAD/CAM Center)', 'Mechanical Engineering', 'Custody of CAD/CAM Automation Center, CNC machining centers, and 3D design workstations.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Mr. R. Anand</div>
                                <span class="persona-badge badge-lab">CAD/CAM Automation</span>
                            </div>
                        </div>
                        <span class="persona-email">mechlab@msec.org.in</span>
                    </div>
                </div>

                <!-- 7. CIVIL Department Role Cards -->
                <div id="dept-pane-CIVIL" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('civilhod@msec.org.in', 'Dr. R. Subramanian', 'Head of Department (HOD)', 'Civil Engineering', 'Endorses structural testing and surveying requisitions for institutional review and Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">HOD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Head of Department (HOD)</div>
                                <div class="persona-name">Dr. R. Subramanian</div>
                                <span class="persona-badge badge-sanction">Department Endorsement</span>
                            </div>
                        </div>
                        <span class="persona-email">civilhod@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('civilcoord@msec.org.in', 'Er. G. Selvaraj', 'Stock Dept Coordinator', 'Civil Engineering', 'Conducts material yard stock verifications, total station audits, and civil lab registers.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CRD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Stock Dept Coordinator</div>
                                <div class="persona-name">Er. G. Selvaraj</div>
                                <span class="persona-badge badge-admin">Materials & Total Stations</span>
                            </div>
                        </div>
                        <span class="persona-email">civilcoord@msec.org.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('civillab@msec.org.in', 'Mr. K. Rajesh', 'Lab In-Charge (Materials Lab)', 'Civil Engineering', 'Custody of Concrete & Highway Testing Lab, Universal Testing Machine (UTM), and compressive gauges.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LAB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Lab In-Charge</div>
                                <div class="persona-name">Mr. K. Rajesh</div>
                                <span class="persona-badge badge-lab">Concrete & Testing Lab</span>
                            </div>
                        </div>
                        <span class="persona-email">civillab@msec.org.in</span>
                    </div>
                </div>

                <!-- 8. Central Library Role Cards -->
                <div id="dept-pane-LIB" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('librarian@msec.edu.in', 'Dr. N. Mohamed Ismail', 'Chief Librarian (Section Head)', 'Central Dr. Zakir Husain Library', 'Submits book acquisition indents and journal renewals for institutional review and Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">LIB</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Chief Librarian</div>
                                <div class="persona-name">Dr. N. Mohamed Ismail</div>
                                <span class="persona-badge badge-sanction">Library Indents & Stacks</span>
                            </div>
                        </div>
                        <span class="persona-email">librarian@msec.edu.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('library@msec.edu.in', 'Mrs. J. Fathima', 'Circulation & Issues In-Charge', 'Central Dr. Zakir Husain Library', 'Maintains book stack registers, student barcode scanning, physical book bank, and loss logging.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">CIR</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Circulation In-Charge</div>
                                <div class="persona-name">Mrs. J. Fathima</div>
                                <span class="persona-badge badge-lab">Book Stacks & Barcodes</span>
                            </div>
                        </div>
                        <span class="persona-email">library@msec.edu.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('digitallib@msec.edu.in', 'Mr. A. Sikkandar', 'Digital Resource In-Charge', 'Central Dr. Zakir Husain Library', 'Custody of 120 e-library computer terminals, OPAC servers, and online journal connectivity.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">DIG</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Digital Library In-Charge</div>
                                <div class="persona-name">Mr. A. Sikkandar</div>
                                <span class="persona-badge badge-lab">120 Digital Terminals</span>
                            </div>
                        </div>
                        <span class="persona-email">digitallib@msec.edu.in</span>
                    </div>
                </div>

                <!-- 9. MSEC Incubation Center Role Cards -->
                <div id="dept-pane-INCUBATION" class="role-cards-grid dept-pane" style="display: none;">
                    <div class="persona-card" onclick="selectPersona('incubation@msec.edu.in', 'Dr. T. Sheikh Fakrudeen', 'Director (Incubation & EDII Hub)', 'MSEC Incubation & Innovation Center', 'Submits student startup seed prototyping indents and patent filings for institutional review and Management sanction.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">INC</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Incubation Director</div>
                                <div class="persona-name">Dr. T. Sheikh Fakrudeen</div>
                                <span class="persona-badge badge-sanction">Startup Grants & Indents</span>
                            </div>
                        </div>
                        <span class="persona-email">incubation@msec.edu.in</span>
                    </div>

                    <div class="persona-card" onclick="selectPersona('maker@msec.edu.in', 'Er. K. Mohamed Rifath', 'IoT & Makerspace Lab In-Charge', 'MSEC Incubation & Innovation Center', 'Custody of 3D printers, laser engravers, development boards, and rapid hardware prototyping bays.', 'indigo', this)">
                        <div class="persona-top">
                            <div class="persona-avatar">MAK</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Makerspace In-Charge</div>
                                <div class="persona-name">Er. K. Mohamed Rifath</div>
                                <span class="persona-badge badge-lab">3D Printers & IoT Hardware</span>
                            </div>
                        </div>
                        <span class="persona-email">maker@msec.edu.in</span>
                    </div>
                </div>

            </div>

            <!-- SECTION 2: STORES & PROCUREMENT -->
            <div id="cat-stores" class="category-content-pane" style="display: none;">
                <div class="role-cards-grid">
                    <!-- Central Store Keeper -->
                    <div class="persona-card" onclick="selectPersona('store@msec.edu.in', 'K. Ramasamy', 'Central Store Keeper', 'Central Warehouse Operations', 'The Operational Gateway: Catalog inventory, stock adjustments, GRN receipt, and printable Delivery Challan / Issue Vouchers.', 'emerald', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #dcfce7; color: #166534;">STR</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Central Store Keeper</div>
                                <div class="persona-name">K. Ramasamy</div>
                                <span class="persona-badge badge-gateway">The Mandatory Operational Gateway</span>
                            </div>
                        </div>
                        <span class="persona-email">store@msec.edu.in</span>
                    </div>

                    <!-- Purchase Officer -->
                    <div class="persona-card" onclick="selectPersona('purchase@msec.edu.in', 'P. Natarajan', 'Purchase & Procurement Officer', 'Procurement Division', 'Vendor onboarding, GST verification, 3-vendor quotation comparisons, L1 analysis, and official Purchase Orders (PO).', 'amber', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #fef3c7; color: #92400e;">PUR</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Purchase Officer</div>
                                <div class="persona-name">P. Natarajan</div>
                                <span class="persona-badge badge-sanction">Vendors, L1 Quotes & POs</span>
                            </div>
                        </div>
                        <span class="persona-email">purchase@msec.edu.in</span>
                    </div>

                    <!-- Accounts Officer -->
                    <div class="persona-card" onclick="selectPersona('accounts@msec.edu.in', 'A. Mohamed Hameed', 'Chief Accounts Officer', 'Finance & Accounts Office', 'Budget head allocations, commitment tracking, invoice audit, and statutory financial ledgers.', 'finance', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #fce7f3; color: #9d174d;">ACC</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Accounts Officer</div>
                                <div class="persona-name">A. Mohamed Hameed</div>
                                <span class="persona-badge badge-finance">Budget Heads & Expenditure</span>
                            </div>
                        </div>
                        <span class="persona-email">accounts@msec.edu.in</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: DIRECTORATE & EXECUTIVE SANCTIONS -->
            <div id="cat-directorate" class="category-content-pane" style="display: none;">
                <div class="role-cards-grid">
                    <!-- Director -->
                    <div class="persona-card" onclick="selectPersona('director@msec.edu.in', 'Dr. K. S. Babai', 'Director (Executive Governance)', 'Directorate & Trust Office', 'Ultimate institutional financial sanctioning authority, trust governance, and campus-wide procurement approvals.', 'purple', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #ede9fe; color: #5b21b6;">DIR</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Director</div>
                                <div class="persona-name">Dr. K. S. Babai</div>
                                <span class="persona-badge badge-purple">Final Management Sanction</span>
                            </div>
                        </div>
                        <span class="persona-email">director@msec.edu.in</span>
                    </div>

                    <!-- Principal -->
                    <div class="persona-card" onclick="selectPersona('principal@msec.edu.in', 'Dr. C. Umarani', 'Principal (Head of Institution)', 'Office of the Principal', 'College-wide academic review, institutional budget checks, and recommendation for final Management sanction.', 'purple', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #fef3c7; color: #92400e;">PRN</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Principal</div>
                                <div class="persona-name">Dr. C. Umarani</div>
                                <span class="persona-badge badge-sanction">Institutional Concurrence</span>
                            </div>
                        </div>
                        <span class="persona-email">principal@msec.edu.in</span>
                    </div>

                    <!-- Vice Principal -->
                    <div class="persona-card" onclick="selectPersona('vp@msec.edu.in', 'Dr. S. Rajendran', 'Vice Principal (Administration)', 'Office of the Vice Principal', 'Administrative scrutiny, multi-department quotas, and concurrence before forwarding to Principal & Management.', 'purple', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #fef3c7; color: #92400e;">VP</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Vice Principal</div>
                                <div class="persona-name">Dr. S. Rajendran</div>
                                <span class="persona-badge badge-sanction">Administrative Review</span>
                            </div>
                        </div>
                        <span class="persona-email">vp@msec.edu.in</span>
                    </div>

                    <!-- Management Viewer -->
                    <div class="persona-card" onclick="selectPersona('viewer@msec.edu.in', 'Janab Alhaj S.M. Yousuf', 'Management Viewer', 'Governing Body', 'Read-only institutional analytics, governance audits, high-level summaries, and statutory compliance monitoring.', 'purple', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #f1f5f9; color: #334155;">TRU</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Management Viewer</div>
                                <div class="persona-name">Janab Alhaj S.M. Yousuf</div>
                                <span class="persona-badge badge-admin">Governing Body Observer</span>
                            </div>
                        </div>
                        <span class="persona-email">viewer@msec.edu.in</span>
                    </div>
                </div>
            </div>

            <!-- SECTION 4: ADMIN & AUDIT -->
            <div id="cat-admin" class="category-content-pane" style="display: none;">
                <div class="role-cards-grid">
                    <!-- Super Admin -->
                    <div class="persona-card" onclick="selectPersona('admin@msec.edu.in', 'Engr. M. Shakil', 'Super Administrator', 'Central IT Office', 'Security console, user provisioning, role assignments, system locks, password resets, and master immutable audit trail.', 'crimson', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #fee2e2; color: #991b1b;">ADM</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Super Administrator</div>
                                <div class="persona-name">Engr. M. Shakil</div>
                                <span class="persona-badge badge-admin">Security Console & User Master</span>
                            </div>
                        </div>
                        <span class="persona-email">admin@msec.edu.in</span>
                    </div>

                    <!-- Internal Auditor -->
                    <div class="persona-card" onclick="selectPersona('audit@msec.edu.in', 'CMA S. Narayanan', 'Internal & NAAC Auditor', 'Statutory Audit Cell', 'Read-only compliance portal, physical audit count verifications, NAAC Criteria 4 reports, and dead stock inspection.', 'navy', this)">
                        <div class="persona-top">
                            <div class="persona-avatar" style="background: #f1f5f9; color: #334155;">AUD</div>
                            <div class="persona-info">
                                <div class="persona-role-title">Internal / NAAC Auditor</div>
                                <div class="persona-name">CMA S. Narayanan</div>
                                <span class="persona-badge badge-admin">NAAC Criteria 4 & Physical Audits</span>
                            </div>
                        </div>
                        <span class="persona-email">audit@msec.edu.in</span>
                    </div>
                </div>
            </div>

        </section>

        <!-- Right: Dynamic Context-Aware Login Box -->
        <section class="login-box-card">
            <div>
                <!-- Dynamic Persona Display Banner -->
                <div class="active-role-banner" id="activeRoleBanner">
                    <span class="active-banner-badge" id="activeBadgeText">Computer Science & Engineering &bull; Head of Department (HOD)</span>
                    <div class="active-banner-name" id="activeNameText">Mr. N. Balasubramanian</div>
                    <div class="active-banner-scope" id="activeScopeText">
                        Scrutinizes and endorses departmental indents & lab requisitions; forwards through VP & Principal for Management sanction.
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert-box error">
                        <span>⚠️</span>
                        <div><?= htmlspecialchars($error) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($msg): ?>
                    <div class="alert-box success">
                        <span>✓</span>
                        <div><?= htmlspecialchars($msg) ?></div>
                    </div>
                <?php endif; ?>

                <!-- Authentication Form -->
                <form method="POST" action="index.php?route=do_login" id="authLoginForm">
                    <div class="form-group">
                        <label class="form-label" for="loginUsername">Official Email / System Username</label>
                        <div class="input-wrap">
                            <input type="text" id="loginUsername" name="username" class="form-control" placeholder="Enter username or email" required autofocus value="csehod@msec.org.in">
                        </div>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label class="form-label" for="loginPassword" style="margin-bottom: 0;">Account Password</label>
                            <span style="font-size: 11px; color: #2563eb; font-weight: 600;">BCRYPT Secured</span>
                        </div>
                        <div class="input-wrap">
                            <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Enter password" required value="password123">
                            <button type="button" class="toggle-pass-btn" onclick="togglePassVisibility()" id="togglePassBtn">Show</button>
                        </div>
                    </div>

                    <button type="submit" class="btn-portal-submit" id="submitLoginBtn">
                        <span id="btnSubmitText">Sign In to HOD Dashboard</span>
                        <span>&rarr;</span>
                    </button>
                </form>

                <div style="margin-top: 14px; text-align: center;">
                    <a href="javascript:void(0)" onclick="clearForManualInput()" style="font-size: 12px; color: #64748b; text-decoration: none; font-weight: 600;">
                        ✏️ Or type custom credentials directly
                    </a>
                </div>
            </div>

            <!-- Mandatory Security Notice -->
            <div class="security-advisory-box">
                <strong>🔒 Institutional Zero Self-Service Security Policy</strong>
                Public self-registration and automated self-resets are strictly disabled at MSEC. If you require password resets or new staff onboarding, contact the <strong>Central IT Administrator</strong> for identity-verified provisioning.
            </div>
        </section>

    </main>

    <!-- Mobile Floating Quick Sign In Pill (iOS & Android) -->
    <aside class="mobile-floating-pill" id="mobileAuthPill">
        <div class="pill-info-area" onclick="switchMobileView('login')">
            <div class="pill-avatar" id="pillAvatar">HOD</div>
            <div class="pill-meta">
                <div class="pill-name" id="pillName">Mr. N. Balasubramanian</div>
                <div class="pill-role" id="pillRole">Computer Science &bull; HOD</div>
            </div>
        </div>
        <button type="button" class="pill-action-btn" onclick="submitMobileAuth()" id="pillActionBtn">
            <span>Sign In ⚡</span>
        </button>
    </aside>

</div>

<script>
// Banner Theme Gradients
const bannerThemes = {
    'navy': 'linear-gradient(135deg, #102a43 0%, #1e429f 100%)',
    'indigo': 'linear-gradient(135deg, #1e1b4b 0%, #4338ca 100%)',
    'emerald': 'linear-gradient(135deg, #064e3b 0%, #059669 100%)',
    'purple': 'linear-gradient(135deg, #4c1d95 0%, #7c3aed 100%)',
    'amber': 'linear-gradient(135deg, #78350f 0%, #d97706 100%)',
    'crimson': 'linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%)',
    'finance': 'linear-gradient(135deg, #701a75 0%, #db2777 100%)'
};

// Mobile View Switcher (iOS & Android)
function switchMobileView(view) {
    const grid = document.getElementById('portalGrid');
    const btnDir = document.getElementById('segBtnDirectory');
    const btnLogin = document.getElementById('segBtnLogin');

    if (view === 'login') {
        grid.classList.remove('view-directory');
        grid.classList.add('view-login');
        if (btnDir) btnDir.classList.remove('active');
        if (btnLogin) btnLogin.classList.add('active');
        // Scroll to top of login card
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        grid.classList.remove('view-login');
        grid.classList.add('view-directory');
        if (btnLogin) btnLogin.classList.remove('active');
        if (btnDir) btnDir.classList.add('active');
    }
}

// Mobile Quick Authentication Trigger
function submitMobileAuth() {
    const form = document.getElementById('authLoginForm');
    if (form) form.submit();
}

// Switch Institutional Category Tabs
function switchCategory(catId) {
    document.querySelectorAll('.cat-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.category-content-pane').forEach(pane => pane.style.display = 'none');

    event.currentTarget.classList.add('active');
    const targetPane = document.getElementById('cat-' + catId);
    if (targetPane) targetPane.style.display = 'block';

    if (catId === 'academic') {
        // Default to CSE
        const activeDeptBtn = document.querySelector('.dept-pill-btn.active') || document.querySelector('.dept-pill-btn');
        if (activeDeptBtn) activeDeptBtn.click();
    } else {
        const firstCard = targetPane.querySelector('.persona-card');
        if (firstCard) firstCard.click();
    }
}

// Switch Academic & Facilities Sub-Departments
function switchDept(deptCode) {
    document.querySelectorAll('.dept-pill-btn').forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');

    // Hide all dept panes
    document.querySelectorAll('.dept-pane').forEach(p => p.style.display = 'none');

    // Show target pane
    const target = document.getElementById('dept-pane-' + deptCode);
    if (target) {
        target.style.display = 'grid';
        // Auto-select first card in this department
        const firstCard = target.querySelector('.persona-card');
        if (firstCard) firstCard.click();
    }
}

// Select Persona Card & Populate Contextual Login Form
function selectPersona(emailOrUser, fullName, roleTitle, deptName, scopeText, themeKey, cardElement) {
    document.querySelectorAll('.persona-card').forEach(c => c.classList.remove('selected'));
    if (cardElement) cardElement.classList.add('selected');

    // Update Banner
    const banner = document.getElementById('activeRoleBanner');
    if (bannerThemes[themeKey]) {
        banner.style.background = bannerThemes[themeKey];
    } else {
        banner.style.background = bannerThemes['navy'];
    }

    document.getElementById('activeBadgeText').textContent = deptName + ' • ' + roleTitle;
    document.getElementById('activeNameText').textContent = fullName;
    document.getElementById('activeScopeText').textContent = scopeText;

    // Update Form Inputs
    document.getElementById('loginUsername').value = emailOrUser;
    document.getElementById('loginPassword').value = 'password123';
    
    // Update Button Text
    const shortName = fullName.split(' ')[0] + ' ' + (fullName.split(' ')[1] || '');
    const shortRole = roleTitle.split('(')[0].trim();
    document.getElementById('btnSubmitText').textContent = 'Sign In as ' + shortName + ' (' + shortRole + ')';

    // Update Floating Mobile Pill
    const pillName = document.getElementById('pillName');
    const pillRole = document.getElementById('pillRole');
    const pillAvatar = document.getElementById('pillAvatar');
    const mobileSegBadge = document.getElementById('mobileSegBadge');

    const cleanWords = fullName.replace(/^(Mr\.|Ms\.|Mrs\.|Dr\.|Er\.|CMA)\s+/i, '').split(' ');
    const initials = cleanWords.map(w => w[0]).filter(Boolean).slice(0, 2).join('').toUpperCase() || 'ID';

    if (pillAvatar) pillAvatar.textContent = initials;
    if (pillName) pillName.textContent = fullName;
    if (pillRole) pillRole.textContent = (deptName.includes('&') ? deptName.split('&')[0].trim() : deptName.split(' ')[0]) + ' • ' + shortRole;
    if (mobileSegBadge) mobileSegBadge.textContent = shortRole;

    // Mobile scroll assist if already on login view
    if (window.innerWidth <= 960 && cardElement && event && event.isTrusted) {
        // Subtle haptic visual feedback
        if ('vibrate' in navigator) {
            try { navigator.vibrate(25); } catch(e) {}
        }
    }
}

function clearForManualInput() {
    document.getElementById('loginUsername').value = '';
    document.getElementById('loginPassword').value = '';
    document.getElementById('activeRoleBanner').style.background = bannerThemes['navy'];
    document.getElementById('activeBadgeText').textContent = 'Institutional Authentication Console';
    document.getElementById('activeNameText').textContent = 'Custom Institutional Login';
    document.getElementById('activeScopeText').textContent = 'Enter your registered MSEC employee ID or official college email address to sign in.';
    document.getElementById('btnSubmitText').textContent = 'Authenticate & Sign In';
    document.getElementById('loginUsername').focus();

    // Update Mobile Segmented indicator
    const mobileSegBadge = document.getElementById('mobileSegBadge');
    if (mobileSegBadge) mobileSegBadge.textContent = 'Manual';
}

function togglePassVisibility() {
    const input = document.getElementById('loginPassword');
    const btn = document.getElementById('togglePassBtn');
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Hide';
    } else {
        input.type = 'password';
        btn.textContent = 'Show';
    }
}
</script>

</body>
</html>
