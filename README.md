# Web page

# Features

### Registration
* Social network authentication
* Two types of registration (Client or Partner)
* reCAPTCHA protection against fraud and abuse
* Password recovery with JWT token
* Session management with Redis cache

### Localization
* Multiple locale support
* Backend language detection (4 strategies)
* Frontend language detection (4 strategies)

### Location
* Use API service for search city by name

### Error Handling
* User-friendly error pages
* Helpful empty states
* Inline error recovery suggestions
* Improved form validation messaging

### Navigation and Information Architecture
* Mobile-first approach
* Previous page navigation
* Back-to-top functionality

### UI/UX
* Accessibility Features:
  * Proper ARIA roles (banner, main, contentinfo)
  * Skip navigation link for keyboard users
  * Proper language attributes and RTL support
  * ARIA live regions for flash messages
  * Semantic HTML structure
  
* Performance Optimizations:
  * Resource preloading for critical assets
  * Deferred loading of non-critical JavaScript
  * SRI (Subresource Integrity) for external resources
  * Proper asset organization (CSS/JS)

* Responsive Design:
  * Bootstrap 5 integration
  * Mobile-friendly structure
  * RTL support for Arabic and Hebrew languages

* User Experience:
  * Flash message system
  * Modal system for important interactions
  * Dark/Light theme toggle
  * Multi-language support

### SEO
* Security Headers
  * Comprehensive Content Security Policy (CSP)
  * X-Frame-Options
  * X-Content-Type-Options
  * Referrer-Policy
  * Detailed Permissions-Policy

* Basic SEO
  * Proper charset and viewport settings
  * Meta description and keywords
  * Advanced robots meta with extended parameters
  * Author metadata
  * Canonical URLs

* Mobile Optimization
  * Complete mobile-specific meta tags
  * Apple mobile web app capable
  * Theme color
  * Telephone format detection
  * Progressive Web App support

* Internationalization
  * Language alternates (hreflang)
  * Default language fallback
  * Multiple locale support

* Social Media
  * Complete Open Graph tags
  * Article-specific metadata
  * Twitter Card implementation
  * Image dimensions and types
  * Social media handles

* Technical SEO
  * Preconnect to external domains
  * Comprehensive favicon setup
  * Web manifest
  * RSS and Atom feeds
  * Schema.org markup for Website
  * Added preload directives for critical CSS and JavaScript

### Development
  * Asset versioning system
  * CI tools integration
  * Console hello message
  * Environment variables support

## Geonames

### Prerequisites
1. Register for a GeoNames account:
   - Go to [GeoNames](http://www.geonames.org/login)
   - Click on "Click here to register for a new username"
   - Fill in the registration form and submit
   - Check your email to activate your account

2. Enable free web services:
   - After logging in, go to your account management page
   - Click on "Click here to enable" in the Free Web Services section

3. Set your GeoNames username in the `.env` file:
```env
API_GEO_USER_NAME=your_username_here
```

Note: The free web service allows up to 20,000 credits per day.

## reCAPTCHA Integration

### Prerequisites
1. Get reCAPTCHA credentials from [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
   - Choose reCAPTCHA v2 "I'm not a robot"
   - Register your domain
   - Get Site Key and Secret Key

2. Set reCAPTCHA credentials to your `.env` file:
```env
GOOGLE_RECAPTCHA_SITE_KEY=your_secret_key_here
GOOGLE_RECAPTCHA_SECRET_KEY=your_site_key_here
```

## License

This project is licensed under the [MIT License](LICENSE).

## Author
[Dykyi Roman](https://dykyi-roman.github.io/)