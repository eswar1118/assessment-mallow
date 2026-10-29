<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Store POS - Retail Counter Order Creation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #059669;
            --primary-hover: #047857;
            --primary-light: #ecfdf5;
            --danger: #dc2626;
            --danger-light: #fef2f2;
            --warning: #d97706;
            --warning-light: #fffbeb;
            --info: #2563eb;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 16px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: var(--slate-800);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Bar */
        .navbar {
            background: linear-gradient(135deg, var(--slate-900) 0%, var(--slate-800) 100%);
            color: white;
            padding: 0.85rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--shadow-md);
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary) 0%, #10b981 100%);
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 0.75rem;
            color: var(--slate-400);
        }

        .header-meta {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            font-size: 0.85rem;
        }

        .live-clock {
            color: var(--slate-300);
            background: rgba(255, 255, 255, 0.08);
            padding: 0.35rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Main Grid Layout */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 1.5rem;
            padding: 1.5rem 1.75rem;
            max-width: 1560px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }

        @media (max-width: 1080px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        /* Card Elements */
        .card {
            background: white;
            border-radius: var(--radius-lg);
            border: 1px solid var(--slate-200);
            box-shadow: var(--shadow-sm);
            overflow: visible;
            margin-bottom: 1.25rem;
            position: relative;
        }

        .card-header {
            padding: 1rem 1.25rem;
            background-color: var(--slate-50);
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top-left-radius: var(--radius-lg);
            border-top-right-radius: var(--radius-lg);
        }

        .order-lines-card {
            z-index: 50;
            position: relative;
            overflow: visible !important;
        }

        .card-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--slate-800);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.25rem;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--slate-600);
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .form-control {
            height: 42px;
            padding: 0.5rem 0.75rem;
            border: 1.5px solid var(--slate-300);
            border-radius: var(--radius-sm);
            font-size: 0.9rem;
            color: var(--slate-800);
            background-color: white;
            transition: all 0.15s ease;
            width: 100%;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            background-color: var(--danger-light);
        }

        .form-hint {
            font-size: 0.75rem;
            color: var(--slate-500);
        }

        /* Order Items Table */
        .table-responsive {
            overflow: visible !important;
            border-radius: var(--radius-sm);
            border: none;
            position: relative;
        }

        @media (max-width: 991px) {
            .table-responsive {
                overflow: visible !important;
            }
        }

        /* Searchable Product Combobox */
        .combobox-container {
            position: relative;
            width: 100%;
        }

        .combobox-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .combobox-input {
            width: 100%;
            padding-right: 2.2rem;
            cursor: pointer;
            font-size: 0.875rem;
        }

        .combobox-input:focus {
            cursor: text;
        }

        .combobox-action-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--slate-400);
            cursor: pointer;
            padding: 2px 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 1.15rem;
            line-height: 1;
        }

        .combobox-action-btn:hover {
            color: var(--slate-700);
            background-color: var(--slate-100);
        }

        .combobox-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            width: 100%;
            min-width: 380px;
            max-height: 280px;
            overflow-y: auto;
            background: #ffffff;
            border: 2px solid var(--primary);
            border-radius: var(--radius-sm);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.25), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
            z-index: 99999;
            padding: 0.25rem 0;
        }

        .combobox-header {
            padding: 0.4rem 0.75rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--slate-500);
            background-color: var(--slate-50);
            border-bottom: 1px solid var(--slate-100);
            display: flex;
            justify-content: space-between;
        }

        .combobox-item {
            padding: 0.6rem 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid var(--slate-100);
            transition: background-color 0.1s ease;
        }

        .combobox-item:last-child {
            border-bottom: none;
        }

        .combobox-item:hover,
        .combobox-item.active {
            background-color: #f0fdf4;
        }

        .combobox-item.is-selected {
            background-color: #ecfdf5;
            border-left: 3px solid var(--primary);
        }

        .combobox-item.out-of-stock {
            opacity: 0.65;
            background-color: #fffafb;
        }

        .combobox-item-main {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            flex: 1;
            padding-right: 0.75rem;
        }

        .combobox-item-name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--slate-800);
        }

        .combobox-item-name mark {
            background-color: #fef08a;
            color: #854d0e;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: 700;
        }

        .combobox-item-code {
            font-size: 0.75rem;
            color: var(--slate-500);
            font-family: monospace;
        }

        .combobox-item-code mark {
            background-color: #fef08a;
            color: #854d0e;
            padding: 0 2px;
            border-radius: 2px;
            font-weight: 700;
        }

        .combobox-item-meta {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.2rem;
            white-space: nowrap;
        }

        .combobox-item-price {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--slate-900);
        }

        .combobox-item-tax {
            font-size: 0.72rem;
            color: var(--slate-500);
        }

        .combobox-item-stock {
            font-size: 0.72rem;
            padding: 0.1rem 0.45rem;
            border-radius: 4px;
            font-weight: 600;
        }

        .stock-available {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .stock-low {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .stock-zero {
            background-color: var(--danger-light);
            color: var(--danger);
        }

        .combobox-empty {
            padding: 1.25rem 1rem;
            text-align: center;
            font-size: 0.85rem;
            color: var(--slate-500);
        }

        .order-table tr.active-combobox-row {
            position: relative;
            z-index: 9999;
        }

        .order-table tr.active-combobox-row td {
            position: relative;
            z-index: 9999;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .order-table th {
            background-color: var(--slate-100);
            color: var(--slate-700);
            font-weight: 600;
            text-align: left;
            padding: 0.75rem 0.85rem;
            border-bottom: 1.5px solid var(--slate-200);
            white-space: nowrap;
        }

        .order-table td {
            padding: 0.75rem 0.85rem;
            border-bottom: 1px solid var(--slate-200);
            vertical-align: middle;
        }

        .order-table tbody tr:hover {
            background-color: #f8fafc;
        }

        .qty-input {
            width: 80px;
            text-align: center;
            font-weight: 600;
        }

        /* Action Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            height: 42px;
            padding: 0.5rem 1.15rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.15s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover:not(:disabled) {
            background-color: var(--primary-hover);
            box-shadow: 0 4px 8px rgba(5, 150, 105, 0.25);
        }

        .btn-secondary {
            background-color: var(--slate-100);
            color: var(--slate-700);
            border: 1px solid var(--slate-300);
        }

        .btn-secondary:hover:not(:disabled) {
            background-color: var(--slate-200);
        }

        .btn-danger-outline {
            background-color: transparent;
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.3);
            height: 34px;
            padding: 0 0.65rem;
        }

        .btn-danger-outline:hover:not(:disabled) {
            background-color: var(--danger-light);
            border-color: var(--danger);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-lg {
            height: 52px;
            font-size: 1.05rem;
            font-weight: 700;
            border-radius: var(--radius-md);
            width: 100%;
        }

        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-danger {
            background-color: var(--danger-light);
            color: var(--danger);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .badge-warning {
            background-color: var(--warning-light);
            color: var(--warning);
            border: 1px solid rgba(217, 119, 6, 0.2);
        }

        .badge-success {
            background-color: var(--primary-light);
            color: var(--primary);
            border: 1px solid rgba(5, 150, 105, 0.2);
        }

        /* Payment Wireframe Box (matching spec) */
        .payment-box {
            background-color: #f8fafc;
            border: 1.5px solid #94a3b8;
            border-radius: 8px;
            padding: 1.25rem 1.5rem;
        }

        .payment-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.35rem 0;
            font-size: 0.95rem;
            color: #475569;
        }

        .payment-row-label {
            font-weight: 500;
        }

        .payment-row-val {
            font-weight: 700;
            color: #1e293b;
        }

        .payment-row.grand-total-row {
            font-weight: 700;
            color: #0f172a;
            padding-top: 0.45rem;
        }

        .payment-row.grand-total-row .payment-row-val {
            font-weight: 800;
            font-size: 1.1rem;
            color: #0f172a;
        }

        .payment-divider {
            border-top: 1.5px dashed #94a3b8;
            margin: 0.9rem 0;
        }

        .payment-input-group {
            margin-bottom: 0.85rem;
        }

        .payment-input-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }

        .payment-tender-input {
            width: 100%;
            background-color: #ffffff;
            border: 1.5px solid #94a3b8;
            border-radius: 6px;
            padding: 0.65rem 0.85rem;
            font-size: 0.95rem;
            font-weight: 600;
            color: #0f172a;
        }

        .payment-tender-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }

        .payment-balance-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.4rem;
            font-size: 0.95rem;
        }

        .payment-balance-label {
            font-weight: 700;
            color: #1e293b;
        }

        .payment-balance-val {
            font-weight: 600;
            color: #334155;
            font-size: 0.95rem;
            text-align: right;
            font-family: inherit;
        }

        /* Low Stock Panel (Right Side) */
        .low-stock-panel {
            position: sticky;
            top: 1.5rem;
        }

        .low-stock-list {
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            max-height: 480px;
            overflow-y: auto;
            padding-right: 0.25rem;
        }

        .low-stock-item {
            background: white;
            border: 1px solid var(--slate-200);
            border-left: 4px solid var(--warning);
            border-radius: var(--radius-sm);
            padding: 0.65rem 0.85rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.1s ease;
        }

        .low-stock-item.out-of-stock {
            border-left-color: var(--danger);
            background-color: #fffafb;
        }

        .low-stock-item:hover {
            transform: translateX(2px);
        }

        .low-stock-info {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
        }

        .low-stock-name {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--slate-800);
        }

        .low-stock-code {
            font-size: 0.725rem;
            color: var(--slate-400);
        }

        /* Alerts & Toast */
        .alert {
            padding: 0.85rem 1.15rem;
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            animation: fadeIn 0.2s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert-danger {
            background-color: var(--danger-light);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #991b1b;
        }

        .alert-success {
            background-color: var(--primary-light);
            border: 1px solid rgba(5, 150, 105, 0.3);
            color: #065f46;
        }

        /* Receipt Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 999;
            padding: 1.5rem;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .receipt-card {
            background: white;
            border-radius: var(--radius-lg);
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-xl);
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            transform: scale(0.95);
            transition: transform 0.2s ease;
        }

        .modal-overlay.active .receipt-card {
            transform: scale(1);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed var(--slate-200);
            padding-bottom: 1rem;
        }

        .receipt-body {
            font-size: 0.85rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <header class="navbar">
        <div class="brand-container">
            <div class="brand-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
            </div>
            <div>
                <h1 class="brand-title">Store POS & Retail Counter</h1>
                <p class="brand-subtitle">Fast Order Creation & Real-Time Inventory</p>
            </div>
        </div>
        <div class="header-meta">
            <span class="live-clock" id="liveClock">Loading time...</span>
            <span class="badge badge-success">Online & Ready</span>
        </div>
    </header>

    <!-- Main Workspace -->
    <main class="main-layout">

        <!-- Left / Center: Order Creation Workspace -->
        <section aria-labelledby="orderCreationTitle">
            
            <!-- Global Message Banner -->
            <div id="alertBanner" style="display: none;"></div>

            <!-- Customer Details Card -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title" id="orderCreationTitle">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        1. Customer Information
                    </h2>
                    <span class="badge badge-warning" id="customerTypeBadge">New / Existing</span>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customerEmail" class="form-label">Customer Email *</label>
                            <input 
                                type="email" 
                                id="customerEmail" 
                                class="form-control" 
                                placeholder="e.g. rahul@example.com" 
                                required
                                autocomplete="email"
                            >
                            <span class="form-hint">Existing customer is automatically reused by email.</span>
                        </div>
                        <div class="form-group">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input 
                                type="text" 
                                id="customerName" 
                                class="form-control" 
                                placeholder="Required only if new customer"
                            >
                            <span class="form-hint">Name is required when creating a new customer account.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Lines Card -->
            <div class="card order-lines-card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                            <polyline points="2 17 12 22 22 17"></polyline>
                            <polyline points="2 12 12 17 22 12"></polyline>
                        </svg>
                        2. Order Line Items
                    </h2>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="order-table" id="orderTable" aria-label="Order line items table">
                            <thead>
                                <tr>
                                    <th style="min-width: 320px;">Product Selection (Search by Name or Code)</th>
                                    <th style="width: 100px;">Quantity</th>
                                    <th style="width: 120px;">Unit Price</th>
                                    <th style="width: 90px;">GST Rate</th>
                                    <th style="width: 130px;">Line Total</th>
                                    <th style="width: 70px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="orderTableBody">
                                <!-- Order lines rendered dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Add Product Button Below the Item Rows (matching wireframe) -->
                    <div style="padding: 0.75rem 1rem; display: flex; justify-content: flex-end; background-color: #fafbfc; border-top: 1px solid var(--slate-200);">
                        <button type="button" class="btn btn-primary" id="btnAddLine">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Product
                        </button>
                    </div>
                </div>
            </div>

            <!-- Payment Card (matching wireframe) -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        Payment
                    </h2>
                </div>
                <div class="card-body">
                    <!-- Wireframe Payment Box -->
                    <div class="payment-box">
                        <div class="payment-row">
                            <span class="payment-row-label">Subtotal</span>
                            <span class="payment-row-val" id="dispSubtotal">₹0.00</span>
                        </div>
                        <div class="payment-row">
                            <span class="payment-row-label">Tax</span>
                            <span class="payment-row-val" id="dispTax">₹0.00</span>
                        </div>
                        <div class="payment-row grand-total-row">
                            <span class="payment-row-label">Grand Total</span>
                            <span class="payment-row-val" id="dispGrandTotal">₹0.00</span>
                        </div>

                        <div class="payment-divider"></div>

                        <div class="payment-input-group">
                            <label for="tenderAmount" class="payment-input-label">Amount Given by Customer</label>
                            <input 
                                type="number" 
                                id="tenderAmount" 
                                class="payment-tender-input" 
                                placeholder="₹250" 
                                step="0.01" 
                                min="0"
                            >
                        </div>

                        <div class="payment-balance-row">
                            <span class="payment-balance-label" id="balanceLabel">Balance to Return:</span>
                            <span class="payment-balance-val" id="balanceText">₹0.00</span>
                        </div>
                    </div>

                    <!-- Generate Bill Action Button -->
                    <div style="margin-top: 1.25rem;">
                        <button type="button" class="btn btn-primary btn-lg" id="btnGenerateBill">
                            <span id="btnText">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display: inline-block; vertical-align: middle; margin-right: 6px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                Generate Bill / Confirm Order
                            </span>
                            <span id="btnSpinner" class="spinner" style="display: none;"></span>
                        </button>
                    </div>

                </div>
            </div>

        </section>

        <!-- Right Side: Low Stock Alerts Panel -->
        <aside class="low-stock-panel" aria-labelledby="lowStockPanelTitle">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title" id="lowStockPanelTitle">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--warning);">
                            <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        Low-Stock Alert
                    </h2>
                    <button type="button" id="btnRefreshAlerts" title="Refresh inventory alerts" style="background: none; border: none; cursor: pointer; color: var(--slate-500);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="23 4 23 10 17 10"></polyline>
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                        </svg>
                    </button>
                </div>
                <div class="card-body">
                    <div style="font-size: 0.775rem; color: var(--slate-500); margin-bottom: 0.75rem;">
                        Items with inventory strictly below threshold (<span id="thresholdVal">5</span> units):
                    </div>
                    <div class="low-stock-list" id="lowStockList">
                        <div style="text-align: center; color: var(--slate-400); padding: 1.5rem 0;">
                            Loading stock alerts...
                        </div>
                    </div>
                </div>
            </div>
        </aside>

    </main>

    <!-- Success Receipt Modal -->
    <div class="modal-overlay" id="receiptModal" role="dialog" aria-modal="true" aria-labelledby="receiptModalTitle">
        <div class="receipt-card">
            <div class="receipt-header">
                <div style="width: 44px; height: 44px; background: var(--primary-light); color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <h3 id="receiptModalTitle" style="font-size: 1.25rem; font-weight: 800; color: var(--slate-900);">Order Placed Successfully</h3>
                <p style="font-size: 0.8rem; color: var(--slate-500); margin-top: 0.25rem;" id="receiptOrderNumber">Order #--</p>
            </div>
            
            <div class="receipt-body">
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid var(--slate-200);">
                    <span style="color: var(--slate-500);">Customer:</span>
                    <strong id="receiptCustomerName">--</strong>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 0.5rem; border-bottom: 1px solid var(--slate-200);">
                    <span style="color: var(--slate-500);">Email:</span>
                    <strong id="receiptCustomerEmail">--</strong>
                </div>
                
                <div style="margin-top: 0.5rem; font-weight: 600; color: var(--slate-700);">Items Billed:</div>
                <div id="receiptItemsList" style="max-height: 180px; overflow-y: auto; padding: 0.25rem 0;"></div>

                <div style="border-top: 2px dashed var(--slate-200); padding-top: 0.75rem; margin-top: 0.5rem;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--slate-600); margin-bottom: 0.35rem;">
                        <span>Subtotal:</span>
                        <strong id="receiptSubtotal">₹0.00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--slate-600); margin-bottom: 0.35rem;">
                        <span>GST / Tax Total:</span>
                        <strong id="receiptTax">₹0.00</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--primary);">
                        <span>Grand Total:</span>
                        <span id="receiptGrandTotal">₹0.00</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 0.5rem;">
                <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="window.print()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Bill
                </button>
                <button type="button" class="btn btn-primary" style="flex: 1;" id="btnNewOrder">
                    New Order
                </button>
            </div>
        </div>
    </div>

    <!-- Frontend Application Logic -->
    <script>
        // Catalog loaded from backend server
        let catalogProducts = @json($products ?? []);

        // Dynamic fallback loader to ensure products are always available even if loaded before seeding
        async function fetchCatalog() {
            try {
                const res = await fetch('/api/products', { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data && Array.isArray(data.products) && data.products.length > 0) {
                    catalogProducts = data.products;
                    return catalogProducts;
                }
            } catch (e) {
                console.error('Error fetching product catalog', e);
            }
            return [];
        }

        // State
        let orderRows = [];
        let isSubmitting = false;

        // Initialize clock
        function updateClock() {
            const now = new Date();
            document.getElementById('liveClock').textContent = now.toLocaleDateString('en-IN', {
                weekday: 'short',
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // Autocomplete helper: highlight matched substring safely
        function highlightMatch(text, query) {
            const raw = String(text ?? '');
            if (!query || !query.trim()) return escapeHtml(raw);
            const trimmed = query.trim();
            const safeQuery = trimmed.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const parts = raw.split(new RegExp(`(${safeQuery})`, 'gi'));
            return parts.map(part => {
                if (part.toLowerCase() === trimmed.toLowerCase()) {
                    return `<mark>${escapeHtml(part)}</mark>`;
                }
                return escapeHtml(part);
            }).join('');
        }

        // Filter products by name or code
        function filterProducts(query) {
            if (!query || !query.trim()) return catalogProducts;
            const q = query.trim().toLowerCase();
            return catalogProducts.filter(p => 
                p.name.toLowerCase().includes(q) || 
                p.code.toLowerCase().includes(q)
            );
        }

        // Add a new order line
        function addOrderLine(selectedProductId = null, quantity = 1, autoFocusSearch = false) {
            const rowId = 'row_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);
            orderRows.push({
                rowId: rowId,
                productId: selectedProductId ? parseInt(selectedProductId) : null,
                quantity: quantity
            });
            renderOrderRows();

            if (autoFocusSearch) {
                setTimeout(() => {
                    const input = document.querySelector(`.combobox-input[data-row-id="${rowId}"]`);
                    if (input) {
                        input.focus();
                        openComboboxDropdown(rowId, '');
                    }
                }, 50);
            }
        }

        // Remove an order line
        function removeOrderLine(rowId) {
            orderRows = orderRows.filter(r => r.rowId !== rowId);
            if (orderRows.length === 0) {
                addOrderLine(); // keep at least 1 row
            } else {
                renderOrderRows();
            }
        }

        // Render table rows
        function renderOrderRows() {
            const tbody = document.getElementById('orderTableBody');
            tbody.innerHTML = '';

            orderRows.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.id = row.rowId;

                const product = catalogProducts.find(p => p.id === parseInt(row.productId));
                const unitPrice = product ? parseFloat(product.price) : 0;
                const taxPercent = product ? parseFloat(product.tax_percent) : 0;
                const qty = parseInt(row.quantity) || 1;

                const lineSubtotal = Math.round((qty * unitPrice) * 100) / 100;
                const lineTax = Math.round((lineSubtotal * (taxPercent / 100)) * 100) / 100;
                const lineTotal = Math.round((lineSubtotal + lineTax) * 100) / 100;

                const displayLabel = product ? `${product.name} (${product.code})` : '';

                tr.innerHTML = `
                    <td>
                        <div class="combobox-container" data-row-id="${row.rowId}">
                            <div class="combobox-input-wrap">
                                <input 
                                    type="text" 
                                    class="form-control combobox-input" 
                                    data-row-id="${row.rowId}" 
                                    placeholder="Type product name or code to search..." 
                                    value="${escapeHtml(displayLabel)}" 
                                    autocomplete="off" 
                                    spellcheck="false"
                                    aria-label="Search product for row ${index + 1}"
                                >
                                ${row.productId ? `
                                    <button type="button" class="combobox-action-btn combobox-clear-btn" data-row-id="${row.rowId}" title="Clear product selection">
                                        &times;
                                    </button>
                                ` : `
                                    <span class="combobox-action-btn" style="pointer-events: none; color: var(--slate-400);">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </span>
                                `}
                            </div>
                            <div class="combobox-dropdown" id="dropdown_${row.rowId}" style="display: none;"></div>
                        </div>
                    </td>
                    <td>
                        <input 
                            type="number" 
                            class="form-control qty-input" 
                            data-row-id="${row.rowId}" 
                            value="${qty}" 
                            min="1" 
                            max="${product ? product.stock : 9999}"
                            aria-label="Quantity for row ${index + 1}"
                        >
                    </td>
                    <td style="font-weight: 600; color: var(--slate-700);">
                        ₹${unitPrice.toFixed(2)}
                    </td>
                    <td>
                        <span class="badge ${taxPercent === 0 ? 'badge-success' : 'badge-warning'}">
                            ${taxPercent.toFixed(1)}%
                        </span>
                    </td>
                    <td style="font-weight: 700; color: var(--slate-900);">
                        ₹${lineTotal.toFixed(2)}
                    </td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-danger-outline" data-remove-id="${row.rowId}" title="Remove item">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </td>
                `;

                tbody.appendChild(tr);
            });

            bindRowEvents();
            calculateTotals();
        }

        // Open searchable dropdown for a specific row
        async function openComboboxDropdown(rowId, filterQuery = '') {
            closeAllComboboxes();

            const rowEl = document.getElementById(rowId);
            const dropdown = document.getElementById(`dropdown_${rowId}`);
            if (!dropdown || !rowEl) return;

            if (!catalogProducts || catalogProducts.length === 0) {
                await fetchCatalog();
            }

            rowEl.classList.add('active-combobox-row');

            // Smart positioning: flip upwards if near the bottom of viewport
            const input = rowEl.querySelector('.combobox-input');
            if (input) {
                const rect = input.getBoundingClientRect();
                const spaceBelow = window.innerHeight - rect.bottom;
                if (spaceBelow < 280 && rect.top > 280) {
                    dropdown.style.top = 'auto';
                    dropdown.style.bottom = 'calc(100% + 4px)';
                } else {
                    dropdown.style.top = 'calc(100% + 4px)';
                    dropdown.style.bottom = 'auto';
                }
            }

            const filtered = filterProducts(filterQuery);
            const row = orderRows.find(r => r.rowId === rowId);
            const currentProductId = row ? row.productId : null;

            if (filtered.length === 0) {
                dropdown.innerHTML = `
                    <div class="combobox-empty">
                        No products found matching "<strong>${escapeHtml(filterQuery)}</strong>"
                    </div>
                `;
            } else {
                dropdown.innerHTML = `
                    <div class="combobox-header">
                        <span>Matching Products (${filtered.length})</span>
                        <span>Price (GST)</span>
                    </div>
                    ${filtered.map((p, idx) => {
                        const isSelected = currentProductId === p.id;
                        let stockBadge = '';
                        if (p.stock === 0) {
                            stockBadge = `<span class="combobox-item-stock stock-zero">Out of Stock</span>`;
                        } else if (p.stock <= 5) {
                            stockBadge = `<span class="combobox-item-stock stock-low">Low: ${p.stock} left</span>`;
                        } else {
                            stockBadge = `<span class="combobox-item-stock stock-available">Stock: ${p.stock}</span>`;
                        }

                        return `
                            <div class="combobox-item ${isSelected ? 'is-selected' : ''} ${p.stock === 0 ? 'out-of-stock' : ''} ${idx === 0 ? 'active' : ''}" 
                                 data-row-id="${rowId}" 
                                 data-product-id="${p.id}"
                                 data-index="${idx}">
                                <div class="combobox-item-main">
                                    <span class="combobox-item-name">${highlightMatch(p.name, filterQuery)}</span>
                                    <span class="combobox-item-code">SKU: ${highlightMatch(p.code, filterQuery)}</span>
                                </div>
                                <div class="combobox-item-meta">
                                    <span class="combobox-item-price">₹${parseFloat(p.price).toFixed(2)}</span>
                                    <span class="combobox-item-tax">+${parseFloat(p.tax_percent)}% GST</span>
                                    ${stockBadge}
                                </div>
                            </div>
                        `;
                    }).join('')}
                `;
            }

            dropdown.style.display = 'block';

            // Click event on dropdown items
            dropdown.querySelectorAll('.combobox-item').forEach(item => {
                item.addEventListener('mousedown', function(e) {
                    e.preventDefault(); // prevent input blur before selection
                    const pId = parseInt(this.dataset.productId);
                    selectProductForRow(rowId, pId);
                });
            });
        }

        // Close all open combobox dropdowns
        function closeAllComboboxes() {
            document.querySelectorAll('.combobox-dropdown').forEach(d => d.style.display = 'none');
            document.querySelectorAll('.order-table tr').forEach(tr => tr.classList.remove('active-combobox-row'));
        }

        // Select a product for a specific row
        function selectProductForRow(rowId, productId) {
            const row = orderRows.find(r => r.rowId === rowId);
            if (!row) return;

            row.productId = productId;
            renderOrderRows();

            // Auto-focus quantity input for ultra-fast retail checkout
            setTimeout(() => {
                const qtyInput = document.querySelector(`.qty-input[data-row-id="${rowId}"]`);
                if (qtyInput) {
                    qtyInput.focus();
                    qtyInput.select();
                }
            }, 40);
        }

        // Clear product selection for a row
        function clearProductForRow(rowId) {
            const row = orderRows.find(r => r.rowId === rowId);
            if (row) {
                row.productId = null;
                renderOrderRows();
                setTimeout(() => {
                    const input = document.querySelector(`.combobox-input[data-row-id="${rowId}"]`);
                    if (input) {
                        input.focus();
                        openComboboxDropdown(rowId, '');
                    }
                }, 40);
            }
        }

        // Quick add product from low stock alerts or shortcut
        function quickAddProduct(productId) {
            const emptyRow = orderRows.find(r => !r.productId);
            if (emptyRow) {
                selectProductForRow(emptyRow.rowId, productId);
            } else {
                addOrderLine(productId, 1, false);
            }
        }

        // Global click listener to close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.combobox-container')) {
                closeAllComboboxes();
            }
        });

        // Bind interactive events for table inputs
        function bindRowEvents() {
            // Quantity changes
            document.querySelectorAll('.qty-input').forEach(input => {
                input.oninput = function() {
                    const rowId = this.dataset.rowId;
                    const row = orderRows.find(r => r.rowId === rowId);
                    if (row) {
                        const val = parseInt(this.value);
                        row.quantity = isNaN(val) || val < 1 ? 1 : val;
                        calculateTotals();
                    }
                };
            });

            // Remove row button
            document.querySelectorAll('[data-remove-id]').forEach(btn => {
                btn.onclick = function() {
                    removeOrderLine(this.dataset.removeId);
                };
            });

            // Combobox clear button
            document.querySelectorAll('.combobox-clear-btn').forEach(btn => {
                btn.onclick = function(e) {
                    e.stopPropagation();
                    clearProductForRow(this.dataset.rowId);
                };
            });

            // Combobox input events
            document.querySelectorAll('.combobox-input').forEach(input => {
                const rowId = input.dataset.rowId;

                input.onfocus = function() {
                    const row = orderRows.find(r => r.rowId === rowId);
                    const query = row && row.productId ? '' : this.value;
                    openComboboxDropdown(rowId, query);
                    this.select();
                };

                input.onclick = function() {
                    const row = orderRows.find(r => r.rowId === rowId);
                    const query = row && row.productId ? '' : this.value;
                    openComboboxDropdown(rowId, query);
                };

                input.oninput = function() {
                    const query = this.value;
                    const row = orderRows.find(r => r.rowId === rowId);
                    if (!query.trim() && row && row.productId) {
                        row.productId = null;
                        calculateTotals();
                    }
                    openComboboxDropdown(rowId, query);
                };

                input.onkeydown = function(e) {
                    const dropdown = document.getElementById(`dropdown_${rowId}`);
                    if (!dropdown || dropdown.style.display === 'none') {
                        if (e.key === 'ArrowDown' || e.key === 'Enter') {
                            e.preventDefault();
                            openComboboxDropdown(rowId, this.value);
                        }
                        return;
                    }

                    const items = Array.from(dropdown.querySelectorAll('.combobox-item'));
                    if (items.length === 0) return;

                    let activeIndex = items.findIndex(item => item.classList.contains('active'));

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (activeIndex === -1 || activeIndex >= items.length - 1) {
                            activeIndex = 0;
                        } else {
                            activeIndex++;
                        }
                        items.forEach((it, idx) => it.classList.toggle('active', idx === activeIndex));
                        items[activeIndex].scrollIntoView({ block: 'nearest' });
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (activeIndex <= 0) {
                            activeIndex = items.length - 1;
                        } else {
                            activeIndex--;
                        }
                        items.forEach((it, idx) => it.classList.toggle('active', idx === activeIndex));
                        items[activeIndex].scrollIntoView({ block: 'nearest' });
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const targetItem = activeIndex >= 0 ? items[activeIndex] : items[0];
                        if (targetItem) {
                            const pId = parseInt(targetItem.dataset.productId);
                            selectProductForRow(rowId, pId);
                        }
                    } else if (e.key === 'Escape') {
                        closeAllComboboxes();
                    }
                };
            });
        }

        // Currency change denomination breakdown matching wireframe (e.g. 1x20 + 1x2 + 1x1)
        function getChangeBreakdown(amount) {
            if (amount <= 0) return '';
            let remaining = Math.round(amount); // Cash round to nearest rupee
            const denominations = [500, 200, 100, 50, 20, 10, 5, 2, 1];
            const parts = [];

            for (const d of denominations) {
                if (remaining >= d) {
                    const count = Math.floor(remaining / d);
                    parts.push(`${count}x${d}`);
                    remaining = remaining % d;
                }
            }

            return parts.length > 0 ? parts.join(' + ') : '';
        }

        // Calculate preview subtotal, tax, grand total, and balance
        function calculateTotals() {
            let subtotal = 0;
            let taxTotal = 0;

            orderRows.forEach(row => {
                const product = catalogProducts.find(p => p.id === parseInt(row.productId));
                if (product) {
                    const price = parseFloat(product.price);
                    const taxPercent = parseFloat(product.tax_percent);
                    const qty = parseInt(row.quantity) || 1;

                    const lineSub = Math.round((qty * price) * 100) / 100;
                    const lineTax = Math.round((lineSub * (taxPercent / 100)) * 100) / 100;

                    subtotal += lineSub;
                    taxTotal += lineTax;
                }
            });

            subtotal = Math.round(subtotal * 100) / 100;
            taxTotal = Math.round(taxTotal * 100) / 100;
            const grandTotal = Math.round((subtotal + taxTotal) * 100) / 100;

            document.getElementById('dispSubtotal').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('dispTax').textContent = '₹' + taxTotal.toFixed(2);
            document.getElementById('dispGrandTotal').textContent = '₹' + grandTotal.toFixed(2);

            // Compute change / balance if payment amount is given
            const tenderInput = document.getElementById('tenderAmount');
            const tenderVal = parseFloat(tenderInput.value);
            const balanceLabel = document.getElementById('balanceLabel');
            const balanceText = document.getElementById('balanceText');

            if (!isNaN(tenderVal) && tenderVal > 0) {
                const diff = Math.round((tenderVal - grandTotal) * 100) / 100;

                if (diff >= 0) {
                    balanceLabel.textContent = 'Balance to Return:';
                    const breakdown = getChangeBreakdown(diff);
                    balanceText.textContent = breakdown 
                        ? `₹${diff.toFixed(2)} → ${breakdown}` 
                        : `₹${diff.toFixed(2)}`;
                    balanceText.style.color = '#1e293b';
                } else {
                    balanceLabel.textContent = 'Remaining Due:';
                    balanceText.textContent = '₹' + Math.abs(diff).toFixed(2);
                    balanceText.style.color = 'var(--danger)';
                }
            } else {
                balanceLabel.textContent = 'Balance to Return:';
                balanceText.textContent = '₹0.00';
                balanceText.style.color = '#64748b';
            }
        }

        document.getElementById('tenderAmount').addEventListener('input', calculateTotals);
        document.getElementById('btnAddLine').addEventListener('click', () => addOrderLine(null, 1, true));

        // Fetch low stock items from API
        async function fetchLowStock() {
            const listEl = document.getElementById('lowStockList');
            try {
                const response = await fetch('/api/products/low-stock?threshold=5', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();

                document.getElementById('thresholdVal').textContent = data.effective_threshold;

                if (!data.products || data.products.length === 0) {
                    listEl.innerHTML = `
                        <div style="text-align: center; color: var(--primary); padding: 1.25rem 0; font-size: 0.85rem; font-weight: 600;">
                            ✓ All products adequately stocked!
                        </div>
                    `;
                    return;
                }

                listEl.innerHTML = data.products.map(p => `
                    <div class="low-stock-item ${p.current_stock === 0 ? 'out-of-stock' : ''}" style="cursor: pointer;" title="Click to add to bill" onclick="quickAddProduct(${p.id})">
                        <div class="low-stock-info">
                            <span class="low-stock-name">${escapeHtml(p.name)}</span>
                            <span class="low-stock-code">Code: ${p.code} • ₹${parseFloat(p.price).toFixed(2)}</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.4rem;">
                            <span class="badge ${p.current_stock === 0 ? 'badge-danger' : 'badge-warning'}">
                                ${p.current_stock === 0 ? 'Out of Stock' : p.current_stock + ' left'}
                            </span>
                            <span style="font-size: 0.72rem; color: var(--primary); font-weight: 700; background: var(--primary-light); padding: 0.15rem 0.4rem; border-radius: 4px;">+ Add</span>
                        </div>
                    </div>
                `).join('');
            } catch (err) {
                listEl.innerHTML = `
                    <div style="color: var(--danger); font-size: 0.8rem; padding: 0.5rem 0;">
                        Unable to load stock alerts.
                    </div>
                `;
            }
        }

        document.getElementById('btnRefreshAlerts').addEventListener('click', fetchLowStock);

        // Submit Order to backend (POST /api/orders)
        document.getElementById('btnGenerateBill').addEventListener('click', async function() {
            if (isSubmitting) return;

            hideAlert();

            const email = document.getElementById('customerEmail').value.trim();
            const name = document.getElementById('customerName').value.trim();

            if (!email) {
                showAlert('Please provide a valid customer email address.', 'danger');
                document.getElementById('customerEmail').focus();
                return;
            }

            // Filter valid product lines
            const productsPayload = [];
            for (const r of orderRows) {
                if (!r.productId) {
                    showAlert('Please select a valid product for every item line.', 'danger');
                    return;
                }
                const qty = parseInt(r.quantity);
                if (isNaN(qty) || qty < 1) {
                    showAlert('Quantity must be an integer greater than zero.', 'danger');
                    return;
                }
                productsPayload.push({
                    product_id: parseInt(r.productId),
                    quantity: qty
                });
            }

            if (productsPayload.length === 0) {
                showAlert('Please add at least one product to the order.', 'danger');
                return;
            }

            // Check duplicate product selections on frontend
            const seenIds = new Set();
            for (const p of productsPayload) {
                if (seenIds.has(p.product_id)) {
                    showAlert('Duplicate product detected in order lines. Please merge quantities into a single line.', 'danger');
                    return;
                }
                seenIds.add(p.product_id);
            }

            // Set Loading state
            setSubmitting(true);

            try {
                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        customer_email: email,
                        customer_name: name,
                        products: productsPayload
                    })
                });

                const data = await response.json();

                if (response.status === 201) {
                    // Success! Display official receipt
                    showReceiptModal(data.order);
                    fetchLowStock(); // Refresh stock panel
                    
                    // Update in-memory stock numbers
                    if (data.order && data.order.items) {
                        data.order.items.forEach(item => {
                            const p = catalogProducts.find(prod => prod.id === item.product_id);
                            if (p) {
                                p.stock = Math.max(0, p.stock - item.quantity);
                            }
                        });
                    }
                } else if (response.status === 409) {
                    // Insufficient stock error
                    showAlert(`⚠️ Stock Error: ${data.message}`, 'danger');
                } else if (response.status === 422) {
                    // Form validation errors
                    const errMsgs = Object.values(data.errors || {}).flat().join(' ');
                    showAlert(`Validation Error: ${errMsgs || data.message}`, 'danger');
                } else {
                    showAlert(`Error: ${data.message || 'Failed to place order.'}`, 'danger');
                }
            } catch (err) {
                showAlert('Network error: Unable to reach the store server.', 'danger');
            } finally {
                setSubmitting(false);
            }
        });

        function setSubmitting(loading) {
            isSubmitting = loading;
            const btn = document.getElementById('btnGenerateBill');
            const spinner = document.getElementById('btnSpinner');
            const btnText = document.getElementById('btnText');

            if (loading) {
                btn.disabled = true;
                spinner.style.display = 'inline-block';
                btnText.style.opacity = '0.7';
            } else {
                btn.disabled = false;
                spinner.style.display = 'none';
                btnText.style.opacity = '1';
            }
        }

        function showAlert(message, type) {
            const banner = document.getElementById('alertBanner');
            banner.className = `alert alert-${type}`;
            banner.innerHTML = `
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>${escapeHtml(message)}</span>
            `;
            banner.style.display = 'flex';
            banner.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function hideAlert() {
            const banner = document.getElementById('alertBanner');
            banner.style.display = 'none';
        }

        function showReceiptModal(order) {
            document.getElementById('receiptOrderNumber').textContent = `Order #${order.id} • ${new Date(order.created_at).toLocaleString('en-IN')}`;
            document.getElementById('receiptCustomerName').textContent = order.customer.name;
            document.getElementById('receiptCustomerEmail').textContent = order.customer.email;
            document.getElementById('receiptSubtotal').textContent = '₹' + parseFloat(order.subtotal).toFixed(2);
            document.getElementById('receiptTax').textContent = '₹' + parseFloat(order.tax_total).toFixed(2);
            document.getElementById('receiptGrandTotal').textContent = '₹' + parseFloat(order.grand_total).toFixed(2);

            const itemsList = document.getElementById('receiptItemsList');
            itemsList.innerHTML = order.items.map(item => `
                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; padding: 0.25rem 0; border-bottom: 1px dotted var(--slate-200);">
                    <span>${escapeHtml(item.product_name)} × ${item.quantity}</span>
                    <span style="font-weight: 600;">₹${parseFloat(item.line_total).toFixed(2)}</span>
                </div>
            `).join('');

            document.getElementById('receiptModal').classList.add('active');
        }

        document.getElementById('btnNewOrder').addEventListener('click', function() {
            document.getElementById('receiptModal').classList.remove('active');
            document.getElementById('customerEmail').value = '';
            document.getElementById('customerName').value = '';
            document.getElementById('tenderAmount').value = '';
            const balanceLabel = document.getElementById('balanceLabel');
            const balanceText = document.getElementById('balanceText');
            if (balanceLabel) balanceLabel.textContent = 'Balance to Return:';
            if (balanceText) {
                balanceText.textContent = '₹0.00';
                balanceText.style.color = '#64748b';
            }
            orderRows = [];
            addOrderLine();
            hideAlert();
        });

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        // Initialize with 1 empty line and fetch initial low stock alerts
        if (!catalogProducts || catalogProducts.length === 0) {
            fetchCatalog().then(() => {
                addOrderLine();
                fetchLowStock();
            });
        } else {
            addOrderLine();
            fetchLowStock();
        }
    </script>
</body>
</html>
