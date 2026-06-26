# Design Document: Mobile API Integration

## Overview

This design describes how to integrate the Flutter mobile app (migrant_work_tw) with the Laravel backend APIs. The work involves two areas: (1) ensuring the backend database is seeded with required reference data, and (2) replacing all dummy/local data in the mobile app with real API calls using the existing base_connect.dart HTTP client layer and GetX state management.

## Architecture

### System Components

```
┌─────────────────────────────────────────────────────────────────┐
│ Flutter Mobile App (migrant_work_tw)                            │
│                                                                 │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────────┐  │
│  │  UI Pages    │───▶│  Controllers │───▶│  Repositories    │  │
│  │  (GetX View) │    │  (GetX Ctrl) │    │  (API Repos)     │  │
│  └──────────────┘    └──────────────┘    └────────┬─────────┘  │
│                                                    │            │
│                                           ┌────────▼─────────┐  │
│                                           │  API_Client      │  │
│                                           │  (base_connect)  │  │
│                                           └────────┬─────────┘  │
└────────────────────────────────────────────────────┼────────────┘
                                                     │ HTTP/REST
                                                     ▼
┌─────────────────────────────────────────────────────────────────┐
│ Laravel Backend (migrant_work_tw_be)                            │
│                                                                 │
│  ┌──────────┐    ┌──────────────┐    ┌───────────────────────┐ │
│  │  Routes  │───▶│  Controllers │───▶│  Models / Database    │ │
│  │  api.php │    │  (Api/)      │    │  (Eloquent + MySQL)   │ │
│  └──────────┘    └──────────────┘    └───────────────────────┘ │
│                                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  Seeders: CategorySeeder, CitySeeder, JobSeeder, etc.   │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

### Key Design Decisions

1. **Repository Pattern**: Each feature module in the mobile app uses a repository interface. We replace mock repositories with API-backed implementations that call the backend via the API_Client.

2. **GetX State Management**: Controllers hold observable state (`.obs`). When API data arrives, the controller updates observable values, and the UI auto-rebuilds.

3. **Centralized API Client (base_connect.dart)**: All HTTP calls go through this single class. It handles:
   - Base URL configuration
   - Auth token injection (Bearer header)
   - Response envelope parsing
   - Error code mapping (401 → logout, 422 → field errors, 500 → generic error)
   - Network timeout handling

4. **REST Chat (not Firebase)**: The backend already has a working REST chat system. The mobile app will consume `/api/chats` endpoints instead of using Firebase, matching the backend architecture.

5. **Shimmer + Pull-to-Refresh**: Already implemented in the mobile UI. The API repositories just need to trigger the existing loading state correctly.

## Detailed Design

### 1. Backend Seeding (Laravel)

**Action**: Run `php artisan db:seed` on the production server to execute CategorySeeder and CitySeeder.

The seeders use `updateOrCreate` to be idempotent — safe to run multiple times without duplicating data.

```
php artisan db:seed --class=CategorySeeder
php artisan db:seed --class=CitySeeder
```

This resolves the issue where `/api/categories` and `/api/cities` return empty arrays.

### 2. API Client Layer Enhancement

**File**: `lib/data/repositories/base_connect.dart`

The existing base_connect.dart needs to be enhanced with:

```dart
// Standardized response handling
class ApiResponse<T> {
  final bool success;
  final T? data;
  final String? message;
  final String? errorCode;
  final Map<String, List<String>>? validationErrors;
}
```

**Error handling strategy:**
- HTTP 401 → Clear token, redirect to login
- HTTP 403 → Show permission error (e.g., unverified_employer, chat_not_opened)
- HTTP 422 → Parse `errors` field, map to form fields
- HTTP 500 → Generic "server error, try again" message
- Network timeout → "No internet connection" message with retry

### 3. Authentication Flow

**Files affected:**
- `lib/data/repositories/api_auth_repository.dart` (new/enhanced)
- `lib/modules/auth/` controllers

**Flow:**
1. User submits login/register form
2. Controller calls `AuthRepository.login(email, password)`
3. Repository calls `POST /api/auth/login` via API_Client
4. On success: parse user object + token, store token in secure storage, store user in GetX state
5. On failure: return error to controller, controller shows error in UI

**Token storage**: Use `GetStorage` or `flutter_secure_storage` for persisting the Auth_Token across app restarts.

### 4. Job Listings Integration

**Files affected:**
- `lib/data/repositories/api_job_repository.dart` (replace mock)
- `lib/modules/worker/home/worker_home_controller.dart`

**Flow:**
1. Controller calls `fetchJobs({search, city, category})` on init
2. Repository builds query string: `GET /api/jobs?search=X&city=Y&category=Z`
3. Parse response into `List<JobModel>` from `data` array
4. Controller sets `jobs.value = parsedJobs`, UI rebuilds
5. Pull-to-refresh calls the same `fetchJobs()` method

### 5. Job Creation Integration

**Files affected:**
- `lib/modules/employer/create_job/create_job_controller.dart`

**Flow:**
1. On form open: fetch categories from `GET /api/categories`, cities from `GET /api/cities`
2. Populate dropdown items from API response
3. On submit: `POST /api/jobs` with form data
4. Handle specific error codes: `unverified_employer`, `missing_license`

### 6. Applications Integration

**Files affected:**
- `lib/data/repositories/api_application_repository.dart` (new/enhanced)
- `lib/modules/worker/applications/` controller

**Flow:**
- Worker apply: `POST /api/jobs/{id}/apply` → handle `incomplete_profile`, `missing_cv` errors
- Worker tracker: `GET /api/applications` → parse into `List<ApplicationModel>`
- Employer applicants: `GET /api/jobs/{id}/applicants` → display applicant list
- Status update: `PUT /api/applications/{id}/status`

### 7. Chat System Integration

**Files affected:**
- `lib/data/repositories/api_chat_repository.dart` (new)
- `lib/modules/worker/chat/` (replace placeholder)
- `lib/modules/employer/chat/` (replace placeholder)

**Flow:**
1. Chat list: `GET /api/chats` → parse conversations with partner info, last message, unread count
2. Chat room: `GET /api/chats/{userId}` → parse messages, display in bubbles
3. Send: `POST /api/chats/{userId}` → append to local list immediately (optimistic), confirm with response
4. Mark read: `PUT /api/chats/{messageId}/read` → call when opening a conversation
5. Error handling: display business rule errors (chat_not_opened, no_application) as snackbar messages

**Polling strategy**: For real-time feel without WebSocket, poll `GET /api/chats/{userId}` every 5-10 seconds while the chat room is open.

### 8. Dashboard Integration

**Files affected:**
- `lib/modules/dashboard/worker_dashboard_page.dart`
- `lib/modules/dashboard/employer_dashboard_page.dart`

**Flow:**
1. On page load: call `GET /api/dashboard/worker` or `GET /api/dashboard/employer`
2. Parse statistics data
3. Display with shimmer during loading, pull-to-refresh support

### 9. Verification Integration

**Files affected:**
- `lib/data/repositories/api_verification_repository.dart` (new)
- Verification UI pages (may need creation)

**Flow:**
- Email: send code → user enters code → verify
- Phone: send OTP → user enters OTP → verify
- Document: upload file with document_type → status becomes "pending"

## Data Models

The mobile app's existing models (`UserModel`, `JobModel`, `JobApplicationModel`) already match the API response structure defined in `API_SPECIFICATIONS.md`. A new `ChatModel` is needed:

```dart
class ChatConversation {
  final String partnerId;
  final String partnerName;
  final String? partnerAvatar;
  final String? partnerRole;
  final String? lastMessage;
  final DateTime? lastMessageAt;
  final int unreadCount;
}

class ChatMessage {
  final String id;
  final String senderId;
  final String receiverId;
  final String message;
  final bool isRead;
  final DateTime createdAt;
}
```

## Correctness Properties

1. **API Envelope Parsing Invariant**: For any valid JSON response from the Backend_API matching the envelope format `{"success": bool, "data": ...}`, the API_Client parser SHALL extract the success boolean and data payload without data loss. Parsing a serialized envelope and re-serializing the extracted data SHALL produce equivalent content.

2. **Auth Token Attachment Property**: For ALL HTTP requests made by the API_Client to authenticated endpoints (any endpoint under the `auth:sanctum` middleware group), the request SHALL contain an `Authorization: Bearer {token}` header where token matches the currently stored Auth_Token.

3. **Query Parameter Construction Property**: For any combination of filter inputs (search text, city name, category name) where at least one is non-empty, the constructed URL query string SHALL contain exactly the non-null parameters as key=value pairs, and parsing the query string back SHALL yield the original filter values.

4. **422 Validation Error Parsing Property**: For any HTTP 422 response containing a JSON body with field-level errors in the format `{"errors": {"field": ["message1", "message2"]}}`, the error parser SHALL extract all field names and their associated error messages without omission.

5. **Seeder Idempotence Property**: Running the CategorySeeder or CitySeeder multiple times SHALL produce the same final state in the database (same number of records, same data) as running it once, due to the use of `updateOrCreate`.

6. **Chat Business Rule Invariant**: A Worker SHALL only be able to send messages to an Employer who has previously sent at least one message to that Worker. An Employer SHALL only be able to initiate chat with a Worker who has at least one application to the Employer's job listings.

## Testing Strategy

- **Backend**: Run `php artisan test` with feature tests for seeder verification and API endpoint responses
- **Mobile unit tests**: Test repository classes with mocked HTTP responses to verify parsing logic
- **Mobile integration tests**: Test full controller → repository → mock server flow
- **Manual E2E**: Verify each page loads real data after integration

## Migration Plan

1. Run seeders on production server (immediate fix for empty categories/cities)
2. Enhance API_Client with proper error handling and envelope parsing
3. Replace mock repositories with API repositories one module at a time
4. Replace chat placeholder pages with REST API-backed implementation
5. Test each module individually before moving to the next
6. Remove all dummy/mock data files once integration is verified
