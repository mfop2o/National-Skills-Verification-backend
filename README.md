# National Skills Verification Platform - Backend

This is the official backend service for the Ethiopian National Skills Verification Platform, supporting the "Digital Ethiopia 2025" strategy. The service provides secure, verified digital credentials and professional portfolio management.

## Core Services

- **Identity & Access Management**: Secure authentication using Laravel Sanctum.
- **Professional Portfolios**: Management of digital qualifications, work history, and verified projects.
- **Verification Engine**: Workflow for authorized institutions to verify and issue digital badges.
- **Employer Services**: Talent search and secure credential verification for trusted employers.
- **System Governance**: Audit logs and administrative controls for platform integrity.

## API Architecture

The platform follows an API-first design principle to ensure interoperability and scalability.

- **Base URL**: `/api`
- **Documentation**: All endpoints are documented in `routes/api.php` with role-based access control.
- **Health Monitoring**: Check platform status at `/api/health`.

## Technology Stack

- **Framework**: Laravel 12.0
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel-Permission
- **Database**: PostgreSQL (Production ready)

## Development Workflow

1. **Setup**: Run `composer setup` to initialize the environment.
2. **Testing**: Execute Tests using `php artisan test`.
3. **Environment**: Configuration is managed via `.env` files.

---
© 2024 SkillTrust Ethiopia. Managed by the Ministry of Innovation and Technology.
