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

        <p class="sidebar-heading">Van Management</p>

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

        <span class="sidebar-link disabled">
            <span class="icon">↓</span> Receiving <span class="soon">soon</span>
        </span>

        <span class="sidebar-link disabled">
            <span class="icon">◔</span> Pending Receipts <span class="soon">soon</span>
        </span>

        <span class="sidebar-link disabled">
            <span class="icon">▥</span> Stock Ledger <span class="soon">soon</span>
        </span>

        <span class="sidebar-link disabled">
            <span class="icon">✓</span> Stock Check <span class="soon">soon</span>
        </span>

        <p class="sidebar-heading">Site</p>

        <a href="{{ route('home') }}" class="sidebar-link" target="_blank" rel="noopener">
            <span class="icon">↗</span> View website
        </a>

    </nav>
</aside>
