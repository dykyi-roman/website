# Social Login Integration

## Facebook Login

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

## Google Login

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

## Notes

- Both integrations use KnpUOAuth2ClientBundle
- Ensure your domain is properly configured in the respective developer consoles
- For local development, you may need to use HTTPS (configure your web server accordingly)
- Default redirect after successful login is to the 'home' route
