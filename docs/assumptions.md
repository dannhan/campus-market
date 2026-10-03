# CampusMarket — Sprint 1 Engineering Assumptions

## 1. Baseline Repository Inspection
- Repository initially contains `CampusMarket_Spesifikasi(1).md` and wireframe mockups under `campusmarket_images_ui/`.
- No pre-existing Laravel code, `composer.json`, `.env.example`, migrations, models, or routes were present.
- PHP version 8.4 is available in the environment with SQLite (`pdo_sqlite`, `sqlite3`), OpenSSL, cURL, DOM, MBString, and Zip extensions.
- Node.js (v22.x) and npm (v10.x) are available.

## 2. Technology & Architecture Choices
- **Framework**: Modern Laravel (Laravel 12 / latest stable), utilizing standard Laravel patterns:
  - Eloquent Models with explicit relationships.
  - Form Requests (`RegisterRequest`, `LoginRequest`, `UpdateProfileRequest`) for clean input validation.
  - Domain Services (`StudentVerificationService`) to isolate academic verification logic.
  - Standard Laravel Session-based Authentication with CSRF protection and session regeneration.
  - Standard Laravel `MustVerifyEmail` interface, signed verification routes, and `verified` middleware.
  - Standard Blade templating with Tailwind CSS for a mobile-first, clean UI inspired by the design wireframes.
- **Database**:
  - SQLite for local development (`database/database.sqlite`), adhering to the prompt instruction to use SQLite when no database is configured.
  - Database schema clearly decouples the academic registry (`students` table, `universities` table) from user accounts (`users` table).
- **Authentication**:
  - Email + Password login.
  - "Remember Me" functionality using Laravel's standard `remember_token` and `Auth::attempt($credentials, $remember)`.
  - Student registration requires a valid university selection, matching university email domain, and a matching pre-existing entry in the university student registry.

## 3. Scope Boundaries for Sprint 1
- Strictly implementing Sprint 1 requirements:
  - SRS-001: Student registration with valid university email and status verification.
  - SRS-002: University email domain verification and student database integration.
  - SRS-003: Login/logout with Remember Me option.
  - SRS-004: Student profile displaying name, university, major, NIM, email, verification badge, member since date, and placeholder ratings (seller/buyer).
  - Profile edit for allowed non-identity fields (e.g. display name/avatar) while strictly protecting immutable student identity records (university, NIM, verified email).
- Out of scope for Sprint 1 (deferred to later sprints as instructed):
  - Product catalog, listings, search, filtering.
  - Bargaining, chat, wishlist, checkout, payments, escrow, shipping, transaction tracking, reviews, seller dashboard, admin dashboard.
