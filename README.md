# Web page

## Features
1. Login, Registration, Forgot Password
2. 

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

## SEO

Currently Implemented (Excellent Coverage):
1. Security Headers:
* Comprehensive Content Security Policy (CSP)
* X-Frame-Options
* X-Content-Type-Options
* Referrer-Policy
* Detailed Permissions-Policy
2. Basic SEO:
* Proper charset and viewport settings
* Meta description and keywords
* Advanced robots meta with extended parameters
* Author metadata
* Canonical URLs
3. Mobile Optimization:
* Complete mobile-specific meta tags
* Apple mobile web app capable
* Theme color
* Telephone format detection
* Progressive Web App support
4. Internationalization:
* Language alternates (hreflang)
* Default language fallback
* Multiple locale support (en, es, uk)
5. Social Media:
* Complete Open Graph tags
* Article-specific metadata
* Twitter Card implementation
* Image dimensions and types
* Social media handles
6. Technical SEO:
* Preconnect to external domains
* Comprehensive favicon setup
* Web manifest
* RSS and Atom feeds
* Schema.org markup for Website
* Added preload directives for critical CSS and JavaScrip

## License

This project is licensed under the [MIT License](LICENSE).

## Author
[Dykyi Roman](https://dykyi-roman.github.io/)