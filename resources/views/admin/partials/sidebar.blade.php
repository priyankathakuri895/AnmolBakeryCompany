<aside class="admin-sidebar">

    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="brand-main">ANMOL</span>
        <span class="brand-sub">ADMIN PANEL</span>
    </a>

    <nav class="sidebar-nav">

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <span class="icon">▣</span> Dashboard
        </a>

        <p class="sidebar-heading">Purchasing</p>

        <a href="{{ route('admin.suppliers.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.suppliers.*') ? 'active' : '' }}">
            <span class="icon">◎</span> Suppliers
        </a>

        <a href="{{ route('admin.vehicles.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
            <span class="icon">▤</span> Supplier Vehicles
        </a>

        <p class="sidebar-heading">Raw Material</p>

        <a href="{{ route('admin.materials.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.materials.*') ? 'active' : '' }}">
            <span class="icon">◈</span> Raw Materials
        </a>

        <a href="{{ route('admin.receipts.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.receipts.*') && request('status') !== 'pending' ? 'active' : '' }}">
            <span class="icon">↓</span> Receiving
        </a>

        <a href="{{ route('admin.receipts.index', ['status' => 'pending']) }}"
           class="sidebar-link {{ request()->routeIs('admin.receipts.index') && request('status') === 'pending' ? 'active' : '' }}">
            <span class="icon">◔</span> Pending Receipts
        </a>

        <span class="sidebar-link disabled">
            <span class="icon">▥</span> Stock Ledger <span class="soon">soon</span>
        </span>

        <span class="sidebar-link disabled">
            <span class="icon">✓</span> Stock Check <span class="soon">soon</span>
        </span>

        <p class="sidebar-heading">Sales Distribution</p>

        <a href="{{ route('admin.products.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
            <span class="icon">◆</span> Products
        </a>

        <a href="{{ route('admin.salesmen.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.salesmen.*') ? 'active' : '' }}">
            <span class="icon">☺</span> Salesmen
        </a>

        <a href="{{ route('admin.vans.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.vans.*') ? 'active' : '' }}">
            <span class="icon">▤</span> Vans
        </a>

        <a href="{{ route('admin.production.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.production.*') ? 'active' : '' }}">
            <span class="icon">↓</span> Production
        </a>

        <a href="{{ route('admin.product-stock-checks.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.product-stock-checks.*') ? 'active' : '' }}">
            <span class="icon">✓</span> Stock Check
        </a>

        <a href="{{ route('admin.van-loads.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.van-loads.*') ? 'active' : '' }}">
            <span class="icon">↓</span> Van Loading
        </a>

        <a href="{{ route('admin.van-settlements.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.van-settlements.*') ? 'active' : '' }}">
            <span class="icon">✓</span> Daily Settlement
        </a>

        <a href="{{ route('admin.van-debit.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.van-debit.*') ? 'active' : '' }}">
            <span class="icon">▥</span> Van Debit Ledger
        </a>

        <a href="{{ route('admin.expenses.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
            <span class="icon">◔</span> Expenses
        </a>

        <a href="{{ route('admin.reports.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <span class="icon">▣</span> Reports
        </a>

        <p class="sidebar-heading">Site</p>

        <a href="{{ route('home') }}" class="sidebar-link" target="_blank" rel="noopener">
            <span class="icon">↗</span> View website
        </a>

    </nav>
</aside>
