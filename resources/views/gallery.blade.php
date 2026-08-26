@extends('layouts.app')

@section('title', 'Gallery | Anmol Bakery')

@section('content')

<section class="gallery-hero">
    <div class="container">
        <p class="eyebrow">OUR GALLERY</p>

        <h1>
            A Look Inside
            Anmol.
        </h1>

        <p>
            Explore our factory, products, packaging,
            and the people behind Anmol.
        </p>
    </div>
</section>


<section class="gallery-section section">

    <div class="container">

        <div class="gallery-filters">
            <button class="gallery-filter active" type="button">
                All
            </button>

            <button class="gallery-filter" type="button">
                Factory
            </button>

            <button class="gallery-filter" type="button">
                Products
            </button>

            <button class="gallery-filter" type="button">
                Packaging
            </button>

            <button class="gallery-filter" type="button">
                Team
            </button>
        </div>


        <div class="gallery-grid">

            <div class="gallery-item gallery-large">
                <div class="gallery-placeholder">
                    <span>Factory Photo</span>
                </div>
            </div>

            <div class="gallery-item">
                <div class="gallery-placeholder">
                    <span>Product Photo</span>
                </div>
            </div>

            <div class="gallery-item">
                <div class="gallery-placeholder">
                    <span>Production Photo</span>
                </div>
            </div>

            <div class="gallery-item gallery-wide">
                <div class="gallery-placeholder">
                    <span>Packaging Photo</span>
                </div>
            </div>

            <div class="gallery-item">
                <div class="gallery-placeholder">
                    <span>Team Photo</span>
                </div>
            </div>

            <div class="gallery-item">
                <div class="gallery-placeholder">
                    <span>Bakery Product</span>
                </div>
            </div>

        </div>

    </div>

</section>


<section class="gallery-cta">

    <div class="container">

        <p class="eyebrow">BEHIND ANMOL</p>

        <h2>
            Behind Every Product
            Is A Process Built On Quality.
        </h2>

        <p>
            Discover our products or get in touch with Anmol.
        </p>

        <div class="cta-actions">

            <a href="{{ route('products') }}" class="btn btn-primary">
                Explore Products
            </a>

            <a href="#" class="btn btn-outline">
                WhatsApp Us
            </a>

        </div>

    </div>

</section>

@endsection