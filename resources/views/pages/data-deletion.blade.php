@extends('layouts.app')

@section('title', 'Data Deletion Request')

@section('content')
<div class="wrapper ovh bgc-gmart-gray">
    @php
        $categoriesWithSubs = App\Models\Category::select('id', 'name', 'slug', 'image')
                        ->with(['subcategories' => fn($q) =>
                            $q->select('id', 'category_id', 'name', 'slug')
                              ->orderBy('sort_order')
                              ->limit(10)
                        ])
                        ->limit(10)
                        ->get();
    @endphp
    @include('partials.header')
    @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

    <div class="body_content_wrapper position-relative">

        <!-- Breadcrumb -->
        <section class="breadcumb-section pt30 pb30">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="breadcumb-style1">
                            <div class="breadcumb-list">
                                <a href="{{ route('home') }}">Home</a>
                                <a href="#">Data Deletion Request</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content -->
        <section class="our-terms bgc-white pb90">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2">

                        <div class="main-title text-center mb40">
                            <h2>Data Deletion Request</h2>
                            <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>
                        </div>

                        <div class="terms_condition_grid">

                            <div class="grids mb40">
                                <h4 class="mb20">Your Right to Data Deletion</h4>
                                <p class="mb15">At GenesisHub, we respect your privacy and your right to control your personal data. If you have used Facebook Login to sign in to GenesisHub and wish to have your data removed from our platform, you can request deletion using any of the methods below.</p>
                                <p class="mb15">Once your request is processed, we will delete or permanently anonymize all personal information associated with your account, including your name, email address, profile information, and any linked social account IDs.</p>
                            </div>

                            <div class="grids mb40">
                                <h4 class="mb20">What Data We Hold</h4>
                                <p class="mb15">When you log in with Facebook, we may store the following information:</p>
                                <ul class="mb20">
                                    <li>Your name and email address as provided by Facebook</li>
                                    <li>Your Facebook user ID (used to link your account)</li>
                                    <li>Your account activity on GenesisHub (orders, reviews, shop details if a seller)</li>
                                </ul>
                                <p class="mb15">We do not store your Facebook password, payment information from Facebook, or any data beyond what is needed to operate your GenesisHub account.</p>
                            </div>

                            <div class="grids mb40">
                                <h4 class="mb20">How to Request Data Deletion</h4>
                                <p class="mb15">You have two ways to request that your data be deleted:</p>

                                <h5 class="mb15">Option 1 — Delete Your Account Directly</h5>
                                <p class="mb15">The fastest way to remove your data is to log in to your GenesisHub account and delete it from your account settings:</p>
                                <ul class="mb20">
                                    <li>Log in to <a href="{{ url('/') }}" style="color:#714e32;font-weight:600;">genesishub.ng</a></li>
                                    <li>Go to <strong>Account Settings → Privacy</strong></li>
                                    <li>Click <strong>"Delete My Account"</strong></li>
                                    <li>Confirm the deletion — your data will be permanently removed within <strong>30 days</strong></li>
                                </ul>

                                <h5 class="mb15">Option 2 — Contact Us Directly</h5>
                                <p class="mb15">If you are unable to access your account, send us a deletion request by email and we will process it manually within 30 days:</p>
                                <ul class="mb20">
                                    <li>Email: <a href="mailto:support@genesishub.ng" style="color:#714e32;font-weight:600;">support@genesishub.ng</a></li>
                                    <li>Subject line: <strong>Data Deletion Request</strong></li>
                                    <li>Include the email address associated with your GenesisHub account</li>
                                </ul>
                            </div>

                            <div class="grids mb40">
                                <h4 class="mb20">What Happens After Deletion</h4>
                                <p class="mb15">Once your deletion request is confirmed:</p>
                                <ul class="mb20">
                                    <li>Your personal information (name, email, social IDs) will be permanently deleted or anonymized</li>
                                    <li>You will no longer be able to log in to GenesisHub with that account</li>
                                    <li>Any active orders at the time of deletion will be handled according to our refund policy</li>
                                    <li>Transaction records may be retained in anonymized form for up to <strong>7 years</strong> for legal and accounting compliance under Nigerian law — but these records will not contain any personally identifiable information</li>
                                </ul>
                                <p class="mb15">Deletion is <strong>permanent and irreversible</strong>. If you wish to use GenesisHub again after deletion, you will need to create a new account.</p>
                            </div>

                            <div class="grids mb40">
                                <h4 class="mb20">Revoking Facebook Access Without Deleting Your Account</h4>
                                <p class="mb15">If you only want to remove GenesisHub's access to your Facebook account (without deleting your GenesisHub account), you can do so directly from Facebook:</p>
                                <ul class="mb20">
                                    <li>Go to <strong>Facebook → Settings → Security and Login → Apps and Websites</strong></li>
                                    <li>Find <strong>GenesisHub</strong> in the list and click <strong>Remove</strong></li>
                                </ul>
                                <p class="mb15">This removes Facebook's connection to your GenesisHub account. You can still log in to GenesisHub using your email and password if one is set, or contact us to set up a password for your account.</p>
                            </div>

                            <div class="grids mb40">
                                <h4 class="mb20">Contact Us</h4>
                                <p class="mb15">If you have any questions about your data or this process, please reach out:</p>
                                <ul class="mb20">
                                    <li>Email: <a href="mailto:support@genesishub.ng" style="color:#714e32;font-weight:600;">support@genesishub.ng</a></li>
                                    <li>Address: Lagos, Nigeria</li>
                                </ul>
                                <p class="mb15">We aim to respond to all data requests within <strong>5 business days</strong> and complete deletions within <strong>30 days</strong> of confirmation.</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('partials.footer')
        <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
    </div>
</div>
@endsection