@extends('emails.layout')

@section('title', 'Welcome - Indonet Analytics Hub')

@section('header', 'Welcome to Indonet Analytics Hub')

@section('content')
    <h2>Welcome to the Platform!</h2>
    
    <p>Hello <strong>{{ $user->name }}</strong>,</p>
    
    <p>Congratulations! Your account has been successfully activated and you're now part of the Indonet Analytics Hub community.</p>
    
    <div style="text-align: center;">
        <a href="{{ $dashboardUrl }}" class="btn">Go to Dashboard</a>
    </div>
    
    <hr style="border: none; border-top: 1px solid #e9ecef; margin: 30px 0;">
    
    <h3>Getting Started:</h3>
    <ul>
        <li><strong>Explore Your Dashboard:</strong> Get familiar with the main navigation and available features</li>
        <li><strong>Set Up Your Profile:</strong> Complete your profile information and preferences</li>
        <li><strong>Review Analytics:</strong> Start exploring the available reports and data visualizations</li>
        <li><strong>Connect with Your Team:</strong> Collaborate with other users on shared projects</li>
    </ul>
    
    <h3>Key Features Available to You:</h3>
    <ul>
        <li>📊 Real-time Analytics Dashboards</li>
        <li>📈 Custom Report Generation</li>
        <li>🔍 Advanced Data Filtering</li>
        <li>👥 Team Collaboration Tools</li>
        <li>📧 Automated Report Scheduling</li>
        <li>🔒 Secure Data Access Controls</li>
    </ul>
    
    <div class="credentials-box">
        <h3 style="margin-top: 0;">Need Help?</h3>
        <p>Our support team is here to help you get the most out of the platform:</p>
        <ul style="margin: 10px 0;">
            <li>📚 Check out our documentation and tutorials</li>
            <li>💬 Contact support for technical assistance</li>
            <li>🎓 Join our training sessions</li>
        </ul>
    </div>
    
    <p>We're excited to have you on board and look forward to helping you unlock powerful insights from your data!</p>
    
    <p>Best regards,<br>The Indonet Analytics Hub Team</p>
    
    <p class="small-text">This welcome email was sent to {{ $user->email }}. You're receiving this because your account was recently activated on our platform.</p>
@endsection
