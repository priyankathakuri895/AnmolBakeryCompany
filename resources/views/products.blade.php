@extends('layouts.app')

@section('title', 'Products | Anmol')

@section('content')

<!-- Products Hero -->
<section class="page-hero">

    <div class="container">

        <p class="section-label">
            OUR PRODUCTS
        </p>

        <h1>
            Freshly Baked,
            Made With Care.
        </h1>

        <p>
            Explore our range of quality bakery products,
            prepared with carefully selected ingredients
            and consistent production.
        </p>

    </div>

</section>


<!-- Products Section -->
<section class="section products-page">

    <div class="container">

        <div class="products-heading">

            <p class="section-label">
                ANMOL BAKERY
            </p>

            <h2 class="section-title">
                Discover Our Products
            </h2>

            <p class="section-text">
                From everyday bread to delicious treats,
                discover products made for every occasion.
            </p>

        </div>


        <div class="product-grid">


            <!-- Bread -->
            <article class="product-card">

                <div class="product-image">
                    <img
                        src="{{ asset('images/products/bread.jpg') }}"
                        alt="Anmol Bread"
                    >
                </div>

                <div class="product-info">

                    <p class="product-category">
                        BAKERY
                    </p>

                    <h3>Bread</h3>

                    <p class="product-description">
                        Soft and fresh bread made for everyday
                        meals and delicious moments.
                    </p>

                    <div class="product-variants">
                        <span>Available Sizes</span>
                        <p>Regular • Family Pack</p>
                    </div>

                    <a href="#" class="product-link">
                        WhatsApp Inquiry
                        <span>→</span>
                    </a>

                </div>

            </article>


            <!-- Donuts -->
            <article class="product-card">

                <div class="product-image">
                    <img
                        src="{{ asset('images/products/donuts.jpg') }}"
                        alt="Anmol Donuts"
                    >
                </div>

                <div class="product-info">

                    <p class="product-category">
                        SWEET BAKERY
                    </p>

                    <h3>Donuts</h3>

                    <p class="product-description">
                        Delicious donuts with a soft texture
                        and irresistible taste.
                    </p>

                    <div class="product-variants">
                        <span>Available Variants</span>
                        <p>Classic • Chocolate • Glazed</p>
                    </div>

                    <a href="#" class="product-link">
                        WhatsApp Inquiry
                        <span>→</span>
                    </a>

                </div>

            </article>


            <!-- Croissants -->
            <article class="product-card">

                <div class="product-image">
                    <img
                        src="{{ asset('images/products/croissants.jpg') }}"
                        alt="Anmol Croissants"
                    >
                </div>

                <div class="product-info">

                    <p class="product-category">
                        PASTRY
                    </p>

                    <h3>Croissants</h3>

                    <p class="product-description">
                        Light and flaky pastries prepared
                        with care and attention to texture.
                    </p>

                    <div class="product-variants">
                        <span>Available Variants</span>
                        <p>Plain • Chocolate • Filled</p>
                    </div>

                    <a href="#" class="product-link">
                        WhatsApp Inquiry
                        <span>→</span>
                    </a>

                </div>

            </article>


            <!-- Puffs -->
            <article class="product-card">

                <div class="product-image">
                    <img
                        src="{{ asset('images/products/puffs.jpg') }}"
                        alt="Anmol Puffs"
                    >
                </div>

                <div class="product-info">

                    <p class="product-category">
                        SAVOURY
                    </p>

                    <h3>Puffs</h3>

                    <p class="product-description">
                        Crispy and flavourful baked puffs
                        perfect for a quick snack.
                    </p>

                    <div class="product-variants">
                        <span>Available Variants</span>
                        <p>Vegetable • Chicken • Cheese</p>
                    </div>

                    <a href="#" class="product-link">
                        WhatsApp Inquiry
                        <span>→</span>
                    </a>

                </div>

            </article>


            <!-- Cupcakes -->
            <article class="product-card">

                <div class="product-image">
                    <img
                        src="{{ asset('images/products/cupcakes.jpg') }}"
                        alt="Anmol Cupcakes"
                    >
                </div>

                <div class="product-info">

                    <p class="product-category">
                        SWEET BAKERY
                    </p>

                    <h3>Cupcakes</h3>

                    <p class="product-description">
                        Soft and delicious cupcakes suitable
                        for celebrations and everyday treats.
                    </p>

                    <div class="product-variants">
                        <span>Available Variants</span>
                        <p>Vanilla • Chocolate • Cream</p>
                    </div>

                    <a href="#" class="product-link">
                        WhatsApp Inquiry
                        <span>→</span>
                    </a>

                </div>

            </article>


        </div>

    </div>

</section>

@endsection