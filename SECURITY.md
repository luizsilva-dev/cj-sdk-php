# Security Policy

## Supported Versions

We release patches for security vulnerabilities. Which versions are eligible for receiving such patches depends on the CVSS v3.0 Rating:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |
| < 1.0   | :x:                |

## Reporting a Vulnerability

If you discover a security vulnerability within CJ Affiliate SDK, please send an email to the maintainer via GitHub. All security vulnerabilities will be promptly addressed.

**Please do not open a public issue for security vulnerabilities.**

### What to include in your report:

1. Description of the vulnerability
2. Steps to reproduce
3. Possible impact
4. Suggested fix (if any)

### What to expect:

1. Acknowledgment of your report within 48 hours
2. Regular updates about the progress
3. Credit in the security advisory (if you wish)

## Security Best Practices

When using this SDK:

1. **Never commit credentials** to version control
2. **Use environment variables** for sensitive data
3. **Keep the SDK updated** to the latest version
4. **Enable HTTPS** for all API communications
5. **Implement rate limiting** in your application
6. **Validate all input** before passing to SDK methods
7. **Handle errors gracefully** without exposing sensitive information

### Example: Secure Configuration

```php
// Good - Using environment variables
$config = [
    'access_token' => getenv('CJ_ACCESS_TOKEN'),
    'publisher_id' => getenv('CJ_PUBLISHER_ID')
];

// Bad - Hardcoded credentials
$config = [
    'access_token' => 'your_actual_token_here',  // DON'T DO THIS!
    'publisher_id' => '12345'
];
```

## Known Security Considerations

### API Credentials

- Personal Access Tokens should be treated as passwords
- Tokens cannot be recovered after creation
- Regularly rotate your access tokens
- Use separate tokens for development and production

### Caching

- Cached data may contain sensitive information
- Ensure cache directories have appropriate permissions
- Consider encrypting cached data for sensitive applications

### HTTPS

- This SDK communicates over HTTPS by default
- Never disable SSL verification in production

## Disclosure Policy

When we receive a security bug report, we will:

1. Confirm the problem and determine affected versions
2. Audit code to find any similar problems
3. Prepare fixes for all supported versions
4. Release new versions as soon as possible

Thank you for helping keep CJ Affiliate SDK secure!
