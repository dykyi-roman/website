# Web page

## Install

* Update [.env](.env)
* Update [manifest.json](public%2Fmanifest.json)
* Update [sitemap.xml](public%2Fsitemap.xml)
* Update [robots.txt](public%2Frobots.txt)
* Check /feed/rss.xml & /feed/atom.xml
* Call command: `make copy`

# Features

### General
* Browser's location permission request
* Agreement with cookies popup
* Display promotion banner
* Language and Currency detection
* Light and dark themas
* User-friendly error pages (401, 403, 404, 500)
* Event storage
* User Online/Offline Statuses

### Settings
* Privacy
  - Activation/Deactivation
  - Delete user account
  - Change password/Create password
* General
  - Change language 
  - Change currency 
  - Change theme
* Account
  - Upload photo
  - Change name
  - Verify phone
  - Verify email

### User Online/Offline Statuses

At load (>500k concurrent users): 
- use a separate microservice for statuses 
- use a NoSQL database for statuses (MongoDB, Cassandra)
- implement event sourcing
- websocket for real-time updates
- circuit breaker for fault tolerance

### Notifications
 - Receive/Read/Delete

#### Current Architecture

The notification system uses WebSocket for real-time communication with a TCP-based internal messaging system:

```
Browser <---(WebSocket/1004)---> WebSocketServer <---(TCP/{port})---> PHP Backend
```

Components:
1. **WebSocket Server** (port 1004):
   - Handles client connections and authentication
   - Maintains active user connections
   - Single process for connection consistency

2. **Internal Communication** (TCP/{port}):
   - Connects PHP backend with WebSocket server
   - Uses JSON format for messages
   - Runs in same process as WebSocket

3. **Message Flow**:
   - Client connects via WebSocket and authenticates
   - PHP creates notification and sends via TCP
   - WebSocket server delivers to specific user

#### Scaling Architecture

To support multiple servers, the architecture needs to change:

```
Browser ---> Load Balancer ---> WebSocket Servers <---> Redis Pub/Sub <--- PHP Backend
```

Required Changes:

1. **Message Broker (Redis)**:
   - Replace TCP with Redis Pub/Sub
   - Add user-to-server mapping
   - Implement message persistence

2. **Load Balancer**:
   - Add sticky sessions
   - Configure health checks
   - Setup SSL termination

3. **High Availability**:
   - Redis Sentinel/Cluster
   - Multiple WebSocket servers
   - Automatic failover
   - Connection migration

Benefits:
- Horizontal scalability
- Better fault tolerance
- Geographic distribution
- Easier maintenance

### Login
* Manual
* Social network 

### Registration
* Social network authentication
* reCAPTCHA protection against fraud and abuse
* Password recovery with JWT token
* Session management with Redis cache
* Referral registration

### Localization
* Multiple locale support
* Backend language detection (4 strategies)
* Frontend language detection (4 strategies)

### Location
* Use GEO API for get location
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
  * API Spec + versioning https://jsonapi.org/format/ 
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

## Currency Integration
1. Register for a GeoNames account [exchangerate](https://app.exchangerate-api.com/)
2. Receive key
3. Set key to your `.env` file:
```env
EXCHANGE_RATE_API_KEY=your_secret_key
```

## Twilio
1. Register for a Twilio account [twilio](https://console.twilio.com/)
2. Receive SID and token
3. Set key to your `.env` file:
```env
TWILIO_DSN=twilio://SID:TOKEN@default?from=FROM
```

```
SID - your Account SID from Twilio
TOKEN - your Auth Token from Twilio
PHONE - your phone number from Twilio
```

## Social Login Integration

### Facebook Login

1. Create a Facebook App:
  - Go to [Facebook Developers](https://developers.facebook.com/)
  - Create a new app or use an existing one
  - Add Facebook Login product to your app
  - Set OAuth redirect URI: `https://your-domain/connect/facebook/check`

2. Configure Environment Variables:
   ```env
   FACEBOOK_APP_ID=your_facebook_app_id
   FACEBOOK_APP_SECRET=your_facebook_app_secret
   ```

3. Routes Available:
  - Login start: `/connect/facebook`
  - OAuth callback: `/connect/facebook/check`

### Google Login

1. Create Google OAuth Credentials:
  - Go to [Google Cloud Console](https://console.cloud.google.com)
  - Create a new project or select existing one
  - Enable Google+ API
  - Create OAuth 2.0 credentials
  - Add authorized redirect URI: `https://your-domain/connect/google/check`

2. Configure Environment Variables:
   ```env
   GOOGLE_CLIENT_ID=your_google_client_id
   GOOGLE_CLIENT_SECRET=your_google_client_secret
   ```

3. Routes Available:
  - Login start: `/connect/google`
  - OAuth callback: `/connect/google/check`

### Notes

- Both integrations use KnpUOAuth2ClientBundle
- Ensure your domain is properly configured in the respective developer consoles
- For local development, you may need to use HTTPS (configure your web server accordingly)
- Default redirect after successful login is to the 'home' route

## DEV

- Phpstan
- Psalm
- CS Fixer
- Deptrac
- PHP Unit
- PHP Metrics

### Ngrok: Secure Tunneling for Local Development

#### Overview
Ngrok provides secure tunnels to expose local servers to the internet, which is crucial for testing webhooks, OAuth callbacks, and external service integrations.

#### Example Configuration

Current Test Tunnel: 
- Public URL: https://wildly-pro-guinea.ngrok-free.app 
- Forwarding to: https://127.0.0.1:1001

Bash: `ngrok http --url=wildly-pro-guinea.ngrok-free.app 1000`

## License

This project is licensed under the [MIT License](LICENSE).

## Author
[Dykyi Roman](https://dykyi-roman.github.io/)