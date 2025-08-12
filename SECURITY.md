# Security Policy

## Supported Versions

We actively support the following versions with security updates:

| Version | Supported          |
| ------- | ------------------ |
| 1.x     | :white_check_mark: |

## Reporting a Vulnerability

We take the security of Laravel EnumShare seriously. If you discover a security vulnerability, please follow these guidelines:

### How to Report

**Please do NOT report security vulnerabilities through public GitHub issues.**

Instead, please report security vulnerabilities by emailing: **[enumshare.pastime560@aleeas.com]**

Replace with your actual security contact email before publishing.

### What to Include

Please include the following information in your report:

- **Type of issue** (e.g., buffer overflow, SQL injection, cross-site scripting, etc.)
- **Full paths** of source file(s) related to the manifestation of the issue
- **Location** of the affected source code (tag/branch/commit or direct URL)
- **Step-by-step instructions** to reproduce the issue
- **Proof-of-concept** or exploit code (if possible)
- **Impact** of the issue, including how an attacker might exploit it

### Response Timeline

- **Acknowledgment**: We will acknowledge receipt of your vulnerability report within 48 hours
- **Initial Response**: We will provide an initial response within 5 business days with next steps
- **Status Updates**: We will keep you informed of our progress throughout the investigation
- **Resolution**: We aim to resolve critical vulnerabilities within 30 days

### Disclosure Policy

- We ask that you give us reasonable time to investigate and fix the issue before any public disclosure
- We will work with you to determine an appropriate timeline for public disclosure
- We will credit you in the security advisory (unless you prefer to remain anonymous)

## Security Best Practices

When using Laravel EnumShare:

1. **Validate Input**: Always validate enum values before processing
2. **Keep Updated**: Regularly update to the latest version
3. **Review Exports**: Regularly review what methods and data are being exported to the frontend
4. **Sanitize Meta Data**: Ensure meta attributes don't contain sensitive information
5. **Access Control**: Implement proper access controls around enum export endpoints

## Security Features

Laravel EnumShare includes these security considerations:

- **No Direct File Access**: Generated TypeScript files contain no executable server-side code
- **Static Export**: Only explicitly marked methods and attributes are exported
- **Type Safety**: Generated TypeScript provides compile-time type checking
- **Sanitized Output**: All exported data is JSON-serialized and sanitized

## Vulnerability History

We maintain a record of security vulnerabilities and their fixes:

- **No known vulnerabilities** at this time

---

Thank you for helping keep Laravel EnumShare and our users safe!
