@extends('emails.layout')

@section('title', 'Account Invitation - Indonet Analytics Hub')

@section('header', 'Welcome to Indonet Analytics Hub')

@section('content')
    <h2>You're Invited to Join Our Analytics Platform</h2>
    
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    
    <p>You have been invited by <strong>{{ $invitedBy }}</strong> to join the Indonet Analytics Hub. Our platform provides comprehensive business intelligence and analytics tools to help drive data-driven decisions.</p>
    
    <div class="credentials-box">
        <h3 style="margin-top: 0;">Your Account Details:</h3>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Temporary Password:</strong> {{ $temporaryPassword }}</p>
    </div>
    
    <div class="warning">
        <strong>Important:</strong> This is a temporary password. You will be required to change it upon your first login for security reasons.
    </div>
    
    <p>To get started, please click the button below to log in to your account:</p>
    
    <div style="text-align: center;">
        <a href="{{ $loginUrl }}" class="btn">Log In to Your Account</a>
    </div>
    
    <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #667eea;"><a href="{{ $loginUrl }}">{{ $loginUrl }}</a></p>
    
    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
    
    <h3>What you can do with Indonet Analytics Hub:</h3>
    <ul>
        <li>Access comprehensive business intelligence dashboards</li>
        <li>Generate detailed analytics reports</li>
        <li>Collaborate with team members on data insights</li>
        <li>Monitor key performance indicators in real-time</li>
    </ul>
    
    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
    
    <p>Welcome aboard!</p>
    
    <p class="small-text">This invitation was sent to {{ $user->email }}. If you believe this was sent in error, please contact our support team.</p>
@endsection
