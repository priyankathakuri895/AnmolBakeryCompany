@extends('layouts.app')

@section('title', 'Anmol | Quality Baked Products')

@section('content')

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-grid">

        <div class="hero-content">

            <p class="hero-label">
                FRESHLY BAKED • QUALITY YOU CAN TRUST
            </p>

            <h1>
                Quality Baked Products,
                <span>Made Fresh.</span>
            </h1>

            <p class="hero-description">
                Delicious bakery products crafted with quality
                ingredients, careful preparation, and a commitment
                to consistent taste and freshness.
            </p>

            <div class="hero-actions">
                <a href="/products" class="btn btn-primary">
                    Explore Products
                </a>

                <a href="#" class="btn btn-outline">
                    WhatsApp Us
                </a>
            </div>

            <div class="hero-features">
                <span>✓ Quality Ingredients</span>
                <span>✓ Hygienic Production</span>
                <span>✓ Freshly Made</span>
            </div>

        </div>

        <div class="hero-visual">
            <div class="hero-image-placeholder">
                <span>ANMOL</span>
                <small>Freshly Baked</small>
            </div>
        </div>

    </div>
</section>
<!-- Products Section -->
<section class="section products-section">
    <div class="container">

        <div class="section-heading">
            <p class="section-label">OUR PRODUCTS</p>

            <h2 class="section-title">
                Freshly Made For Every Occasion
            </h2>

            <p class="section-text">
                Explore our range of freshly baked products,
                prepared with care and attention to quality.
            </p>
        </div>


        <div class="products-grid">

            <!-- Bread -->
            <article class="product-card">
                <div class="product-image product-bread">
                    <span>BREAD</span>
                </div>

                <div class="product-content">
                    <p class="product-number">01</p>

                    <h3>Bread</h3>

                    <p>
                        Soft, fresh and delicious bread made
                        for everyday enjoyment.
                    </p>

                    <a href="/products" class="product-link">
                        View Products →
                    </a>
                </div>
            </article>


            <!-- Donuts -->
            <article class="product-card">
                <div class="product-image product-donut">
                    <span>DONUTS</span>
                </div>

                <div class="product-content">
                    <p class="product-number">02</p>

                    <h3>Donuts</h3>

                    <p>
                        Deliciously soft donuts made for
                        a satisfying sweet treat.
                    </p>

                    <a href="/products" class="product-link">
                        View Products →
                    </a>
                </div>
            </article>


            <!-- Croissants -->
            <article class="product-card">
                <div class="product-image product-croissant">
                    <span>CROISSANTS</span>
                </div>

                <div class="product-content">
                    <p class="product-number">03</p>

                    <h3>Croissants</h3>

                    <p>
                        Light, flaky and carefully baked
                        croissants with a delicious texture.
                    </p>

                    <a href="/products" class="product-link">
                        View Products →
                    </a>
                </div>
            </article>


            <!-- Puffs -->
            <article class="product-card">
                <div class="product-image product-puff">
                    <span>PUFFS</span>
                </div>

                <div class="product-content">
                    <p class="product-number">04</p>

                    <h3>Puffs</h3>

                    <p>
                        Crisp and flavorful baked puffs
                        prepared with care.
                    </p>

                    <a href="/products" class="product-link">
                        View Products →
                    </a>
                </div>
            </article>


            <!-- Cupcakes -->
            <article class="product-card">
                <div class="product-image product-cupcake">
                    <span>CUPCAKES</span>
                </div>

                <div class="product-content">
                    <p class="product-number">05</p>

                    <h3>Cupcakes</h3>

                    <p>
                        Soft and delightful cupcakes perfect
                        for celebrations and everyday moments.
                    </p>

                    <a href="/products" class="product-link">
                        View Products →
                    </a>
                </div>
            </article>

        </div>

    </div>
</section>

<!-- About Section -->
<section class="section about-section">
    <div class="container about-grid">

        <div class="about-visual">
            <div class="about-image">
                <div class="about-image-content">
                    <span>ANMOL</span>
                    <small>BAKING WITH CARE</small>
                </div>
            </div>

            <div class="about-badge">
                <strong>QUALITY</strong>
                <span>IN EVERY BITE</span>
            </div>
        </div>


        <div class="about-content">

            <p class="section-label">
                ABOUT ANMOL
            </p>

            <h2 class="section-title">
                Baking Quality Into Every Product
            </h2>

            <p class="about-lead">
                At Anmol, we believe great bakery products begin
                with quality ingredients, careful preparation,
                and genuine attention to detail.
            </p>

            <p>
                Our focus is on producing delicious baked products
                while maintaining consistent quality, hygienic
                production practices, and reliable packaging.
            </p>

            <p>
                From everyday bakery favorites to products made
                for special occasions, every product represents
                our commitment to freshness and customer
                satisfaction.
            </p>

            <div class="about-stats">

                <div class="about-stat">
                    <strong>01</strong>
                    <span>Quality First</span>
                </div>

                <div class="about-stat">
                    <strong>02</strong>
                    <span>Fresh Production</span>
                </div>

                <div class="about-stat">
                    <strong>03</strong>
                    <span>Customer Focus</span>
                </div>

            </div>

            <a href="/about" class="btn btn-primary">
                Discover Our Story
            </a>

        </div>

    </div>
</section>

<!-- Why Anmol Section -->
<section class="section why-section">
    <div class="container">

        <div class="why-heading">
            <div>
                <p class="section-label">WHY ANMOL</p>

                <h2 class="section-title">
                    Quality You Can Trust
                </h2>
            </div>

            <p class="section-text">
                Every Anmol product is made with attention to
                freshness, quality, hygiene, and consistency.
            </p>
        </div>


        <div class="why-grid">

            <!-- Freshness -->
            <article class="why-card">
                <div class="why-icon">
                    01
                </div>

                <h3>Freshness</h3>

                <p>
                    We focus on delivering freshly prepared
                    bakery products with great taste and texture.
                </p>
            </article>


            <!-- Quality -->
            <article class="why-card">
                <div class="why-icon">
                    02
                </div>

                <h3>Quality Ingredients</h3>

                <p>
                    Carefully selected ingredients help us
                    maintain the quality and consistency of
                    our products.
                </p>
            </article>


            <!-- Hygiene -->
            <article class="why-card">
                <div class="why-icon">
                    03
                </div>

                <h3>Hygienic Production</h3>

                <p>
                    Clean and careful production practices are
                    an important part of our process.
                </p>
            </article>


            <!-- Consistency -->
            <article class="why-card">
                <div class="why-icon">
                    04
                </div>

                <h3>Consistent Quality</h3>

                <p>
                    We aim to deliver reliable quality across
                    every product and every batch.
                </p>
            </article>

        </div>

    </div>
</section>

<!-- Quality & Manufacturing Section -->
<section class="section quality-section">
    <div class="container quality-grid">

        <div class="quality-content">

            <p class="section-label">
                QUALITY & MANUFACTURING
            </p>

            <h2 class="section-title">
                Quality From Production To Packaging
            </h2>

            <p class="quality-intro">
                Quality is not just about the finished product.
                It begins with ingredients and continues through
                production, handling, quality control, and packaging.
            </p>

            <a href="/quality" class="btn btn-primary">
                Explore Our Quality
            </a>

        </div>


        <div class="quality-process">

            <div class="process-item">
                <div class="process-number">01</div>

                <div>
                    <h3>Ingredients</h3>

                    <p>
                        Carefully selected ingredients form
                        the foundation of every product.
                    </p>
                </div>
            </div>


            <div class="process-item">
                <div class="process-number">02</div>

                <div>
                    <h3>Production</h3>

                    <p>
                        Products are prepared with attention
                        to consistency and quality.
                    </p>
                </div>
            </div>


            <div class="process-item">
                <div class="process-number">03</div>

                <div>
                    <h3>Hygiene</h3>

                    <p>
                        Clean and hygienic practices are
                        maintained throughout production.
                    </p>
                </div>
            </div>


            <div class="process-item">
                <div class="process-number">04</div>

                <div>
                    <h3>Packaging</h3>

                    <p>
                        Products are carefully packaged to
                        help maintain freshness and quality.
                    </p>
                </div>
            </div>

        </div>

    </div>
</section>
<!-- WhatsApp CTA -->
<section class="whatsapp-section">
    <div class="container">

        <div class="whatsapp-card">

            <div class="whatsapp-content">

                <p class="whatsapp-label">
                    HAVE A QUESTION?
                </p>

                <h2>
                    Let's Talk About
                    <span>Fresh Products.</span>
                </h2>

                <p>
                    Interested in our products, availability,
                    sizes, or bulk inquiries? Contact Anmol
                    directly through WhatsApp.
                </p>

                <a href="#" class="btn whatsapp-button">
                    <span>WhatsApp Us</span>
                    <span>→</span>
                </a>

            </div>

            <div class="whatsapp-decoration">
                <span>ANMOL</span>
            </div>

        </div>

    </div>
</section>

@endsection