<header class="site-header">
    <div class="container nav-wrapper">

        <!-- Brand -->
        <a href="{{ route('home') }}" class="brand">
            <span class="brand-main">ANMOL</span>
            <span class="brand-sub">BAKERY</span>
        </a>


        <!-- Desktop Navigation -->
        <nav class="main-nav">

            <a
                href="{{ route('home') }}"
                class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}"
            >
                Home
            </a>

            <a
                href="{{ route('about') }}"
                class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}"
            >
                About
            </a>

            <a
                href="{{ route('products') }}"
                class="nav-link {{ request()->routeIs('products') ? 'active' : '' }}"
            >
                Products
            </a>

            <a
                href="{{ route('gallery') }}"
                class="nav-link {{ request()->routeIs('gallery') ? 'active' : '' }}"
            >
                Gallery
            </a>

            <a
                href="{{ route('contact') }}"
                class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}"
            >
                Contact
            </a>

        </nav>


        <!-- WhatsApp -->
        <a href="#" class="btn btn-primary nav-button">
            WhatsApp Us
        </a>


        <!-- Mobile Menu -->
        <button
            class="mobile-menu-button"
            type="button"
            aria-label="Open menu"
        >
            ☰
        </button>

    </div>
</header>