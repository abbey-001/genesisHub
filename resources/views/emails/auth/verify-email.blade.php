{{-- resources/views/emails/auth/verify-email.blade.php --}}
@php
    $verifyUrl = $verificationUrl ?? '#';
    $userName  = $user->name ?? 'there';
    $userEmail = $user->email ?? '';
@endphp

<x-emails.layouts.base subject="Verify your email address — GenesisHub" tagline="Account Verification">

    <p class="greeting">Hey, {{ $userName }}!</p>
    <p class="email-tagline">One step to activate your account</p>

    <p>
        Welcome to GenesisHub — we're glad you're here. Before you can start
        shopping, please confirm your email address by clicking the button below.
    </p>

    <div class="info-card">
        <div class="info-card-title">Verification details</div>
        <div class="info-row">
            <div class="info-label">Email address</div>
            <div class="info-value">{{ $userEmail }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Link expires in</div>
            <div class="info-value">60 minutes</div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ $verifyUrl }}" class="cta-button">Verify Email Address</a>
    </div>

    <div class="secondary-link">
        Button not working? Paste this link into your browser:<br>
        <a href="{{ $verifyUrl }}">{{ $verifyUrl }}</a>
    </div>

    <hr class="divider">

    <div class="alert-box">
        <strong>Didn't sign up?</strong> If you didn't create a GenesisHub account,
        you can safely ignore this email — the link will expire on its own.
    </div>

</x-emails.layouts.base>