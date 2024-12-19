# Web page

## Features

### 1. Registration
* Social network authentication
* Two types of registration (Client or Partner)
* reCAPTCHA protection against fraud and abuse
* Password recovery with JWT token
* Session management with Redis cache

### 2. Localization
* Multiple locale support
* Backend language detection (4 strategies)
* Frontend language detection (4 strategies)

### 3. Error Handling
* User-friendly error pages
* Helpful empty states
* Inline error recovery suggestions
* Improved form validation messaging

### 4. Navigation and Information Architecture
* Mobile-first approach
* Previous page navigation
* Back-to-top functionality

### 5. UI/UX
* Dark theme support

### 6. SEO
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

### 7. Development
  * Asset versioning system
  * CI tools integration
  * Console hello message
  * Environment variables support

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