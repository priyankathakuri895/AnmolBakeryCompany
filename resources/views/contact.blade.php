@extends('layouts.app')

@section('title', 'Contact Anmol | Get In Touch')

@section('content')

{{-- =========================================
     CONTACT HERO
========================================= --}}
<section class="contact-hero">

    <div class="container">

        <p class="eyebrow">CONTACT ANMOL</p>

        <h1>
            Let's Talk
            About Great Bakery.
        </h1>

        <p>
            Have a question about our products, availability,
            or business inquiries? We'd love to hear from you.
        </p>

    </div>

</section>


{{-- =========================================
     CONTACT INFO
========================================= --}}
<section class="contact-info section">

    <div class="container">

        <div class="contact-info-grid">

            <div class="contact-info-card">

                <span class="contact-icon">01</span>

                <p class="eyebrow">CALL US</p>

                <h3>
                    +977 98XXXXXXXX
                </h3>

                <p>
                    Give us a call during business hours.
                </p>

            </div>


            <div class="contact-info-card">

                <span class="contact-icon">02</span>

                <p class="eyebrow">WHATSAPP</p>

                <h3>
                    Chat With Us
                </h3>

                <p>
                    Send us a message directly on WhatsApp.
                </p>

                <a href="#" class="text-link">
                    Start Conversation →
                </a>

            </div>


            <div class="contact-info-card">

                <span class="contact-icon">03</span>

                <p class="eyebrow">BUSINESS HOURS</p>

                <h3>
                    We're Here For You
                </h3>

                <p>
                    Sunday – Friday<br>
                    9:00 AM – 6:00 PM
                </p>

            </div>

        </div>

    </div>

</section>


{{-- =========================================
     CONTACT FORM + MAP
========================================= --}}
<section class="contact-main section">

    <div class="container contact-grid">

        {{-- Form --}}
        <div class="contact-form-wrapper">

            <p class="eyebrow">SEND AN INQUIRY</p>

            <h2>
                We'd Love To
                Hear From You.
            </h2>

            <p class="contact-form-intro">
                Fill in the form and our team will get back to you.
            </p>


            <form class="contact-form">

                <div class="form-row">

                    <div class="form-group">
                        <label for="name">Your Name</label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your name"
                        >
                    </div>


                    <div class="form-group">
                        <label for="phone">Phone</label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Your phone number"
                        >
                    </div>

                </div>


                <div class="form-group">

                    <label for="email">Email</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Your email address"
                    >

                </div>


                <div class="form-group">

                    <label for="message">Message</label>

                    <textarea
                        id="message"
                        name="message"
                        rows="6"
                        placeholder="How can we help?"
                    ></textarea>

                </div>


                <button type="submit" class="btn btn-primary">
                    Send Inquiry
                </button>

            </form>

        </div>


        {{-- Map --}}
        <div class="contact-map-wrapper">

            <p class="eyebrow">FIND US</p>

            <h2>
                Visit Anmol
            </h2>

            <div class="map-placeholder">

                <div>
                    <span>MAP</span>

                    <p>
                        Google Maps location will be added here.
                    </p>
                </div>

            </div>

        </div>

    </div>

</section>


{{-- =========================================
     ADDRESS
========================================= --}}
<section class="contact-address section">

    <div class="container address-grid">

        <div>

            <p class="eyebrow">OUR LOCATION</p>

            <h2>
                Come Say Hello.
            </h2>

        </div>


        <div class="address-content">

            <h3>
                Anmol Bakery
            </h3>

            <p>
                Factory / Office Address<br>
                City, Nepal
            </p>

            <p>
                <strong>Phone:</strong>
                +977 98XXXXXXXX
            </p>

            <p>
                <strong>Email:</strong>
                info@example.com
            </p>

        </div>

    </div>

</section>


{{-- =========================================
     FINAL CTA
========================================= --}}
<section class="contact-cta">

    <div class="container">

        <p class="eyebrow">QUICK & EASY</p>

        <h2>
            Prefer To Chat Directly?
        </h2>

        <p>
            Connect with Anmol on WhatsApp for quick product inquiries.
        </p>

        <a href="#" class="btn btn-primary">
            WhatsApp Us
        </a>

    </div>

</section>

@endsection