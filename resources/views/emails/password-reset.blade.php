@extends('emails.layout')

@section('title', 'Password Reset - Indonet Analytics Hub')

@section('header', 'Password Reset Request')

@section('content')
    <h2>Reset Your Password</h2>
    
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    
    <p>We received a request to reset your password for your Indonet Analytics Hub account. If you made this request, please click the button below to reset your password:</p>
    
    <div style="text-align: center;">
        <a href="{{ $resetUrl }}" class="btn">Reset Your Password</a>
    </div>
    
    <p>If the button doesn't work, you can also copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #667eea;"><a href="{{ $resetUrl }}">{{ $resetUrl }}</a></p>
    
    <div class="warning">
        <strong>Security Notice:</strong> This password reset link will expire in {{ $expirationTime }}. If you don't reset your password within this time, you'll need to request a new reset link.
    </div>
    
    <p><strong>If you didn't request this password reset:</strong></p>
    <ul>
        <li>You can safely ignore this email</li>
        <li>Your password will remain unchanged</li>
        <li>Consider reviewing your account security</li>
        <li>Contact support if you have concerns</li>
    </ul>
    
    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
    
    <h3>Security Tips:</h3>
    <ul>
        <li>Choose a strong password with at least 8 characters</li>
        <li>Include uppercase and lowercase letters, numbers, and symbols</li>
        <li>Don't reuse passwords from other accounts</li>
        <li>Consider using a password manager</li>
    </ul>
    
    <p>If you continue to have trouble accessing your account, please contact our support team for assistance.</p>
    
    <p class="small-text">This password reset was requested for {{ $user->email }}. If you didn't make this request, please contact our support team immediately.</p>
@endsection
