# Implementation Tasks

## Task 1: Run Backend Database Seeders

- [ ] 1.1 Run CategorySeeder on production to populate the categories table with 8 job categories
- [ ] 1.2 Run CitySeeder on production to populate the cities table with 9 Taiwan cities
- [ ] 1.3 Verify GET /api/categories returns non-empty data array with id, name, slug, icon fields
- [ ] 1.4 Verify GET /api/cities returns non-empty data array with id, name, region fields

## Task 2: Enhance API Client Layer (base_connect.dart)

- [ ] 2.1 Create a standardized ApiResponse model class with success, data, message, errorCode, and validationErrors fields
- [ ] 2.2 Add centralized response envelope parsing to extract success status and data payload from all API responses
- [ ] 2.3 Add 401 Unauthorized handler that clears stored auth token and navigates to login screen
- [ ] 2.4 Add 422 Validation Error parser that extracts field-level error messages from the response body
- [ ] 2.5 Add 403 Forbidden handler that maps error codes (unverified_employer, chat_not_opened, no_application) to user-facing messages
- [ ] 2.6 Add network timeout and connectivity error handling with user-friendly error messages
- [ ] 2.7 Ensure Authorization Bearer header is attached to all authenticated API requests

## Task 3: Authentication API Integration

- [ ] 3.1 Implement register method in auth repository calling POST /api/auth/register with full_name, email, password, password_confirmation
- [ ] 3.2 Implement login method in auth repository calling POST /api/auth/login with email and password
- [ ] 3.3 Parse login/register response to extract all user fields (id, email, full_name, token, role_id, profile_completed, nationality, current_city, company_name, industry, avatar_url, phone, verification_status, cv_url, preferred_language)
- [ ] 3.4 Store Auth_Token securely using GetStorage or flutter_secure_storage after successful login/register
- [ ] 3.5 Implement logout method calling POST /api/auth/logout, clearing token, and navigating to login screen
- [ ] 3.6 Handle invalid_credentials error response and display error message to user

## Task 4: Profile Setup API Integration

- [ ] 4.1 Implement updateRole method calling PUT /api/auth/role with selected role_id
- [ ] 4.2 Implement saveProfile method calling PUT /api/auth/profile with Worker fields (full_name, nationality, current_city, phone)
- [ ] 4.3 Implement saveProfile method for Employer fields (full_name, company_name, industry, phone)
- [ ] 4.4 Implement uploadCv method calling POST /api/auth/cv with multipart PDF file upload
- [ ] 4.5 Display field-level validation errors from 422 responses on the profile form
- [ ] 4.6 Update local user state with response data after successful profile save

## Task 5: Job Listings API Integration

- [ ] 5.1 Replace mock job repository with API repository calling GET /api/jobs on worker home page load
- [ ] 5.2 Implement search and filter with query parameters (search, city, category) on GET /api/jobs
- [ ] 5.3 Implement job detail fetch calling GET /api/jobs/{id} with full field parsing (title, employer_name, employer_type, location, salary, salary_period, tags, category, description, duties, requirements, benefits, is_urgent, posted_at, risk_level)
- [ ] 5.4 Connect shimmer loading state to API request lifecycle (show shimmer on loading, hide on response)
- [ ] 5.5 Connect pull-to-refresh to re-fetch job data from API

## Task 6: Job Creation API Integration

- [ ] 6.1 Fetch categories from GET /api/categories to populate category dropdown on create job form
- [ ] 6.2 Fetch cities from GET /api/cities to populate location dropdown on create job form
- [ ] 6.3 Implement job submission calling POST /api/jobs with all form fields
- [ ] 6.4 Handle unverified_employer error (403) with appropriate user message
- [ ] 6.5 Handle missing_license error (403) for agency employers with appropriate user message
- [ ] 6.6 Navigate to employer dashboard and refresh job list on successful creation

## Task 7: Job Applications API Integration

- [ ] 7.1 Implement apply method calling POST /api/jobs/{job_id}/apply with optional cover_letter
- [ ] 7.2 Handle incomplete_profile error (400) with message directing user to complete profile
- [ ] 7.3 Handle missing_cv error (400) with message directing user to upload CV
- [ ] 7.4 Replace applications page FutureBuilder/dummy data with GET /api/applications call
- [ ] 7.5 Implement employer applicants view calling GET /api/jobs/{jobId}/applicants
- [ ] 7.6 Implement application status update calling PUT /api/applications/{id}/status

## Task 8: Chat System API Integration

- [ ] 8.1 Create ChatRepository class with methods for conversations, messages, send, and markAsRead
- [ ] 8.2 Replace worker chat placeholder page with conversation list from GET /api/chats
- [ ] 8.3 Replace employer chat placeholder page with conversation list from GET /api/chats
- [ ] 8.4 Implement chat room page fetching messages from GET /api/chats/{userId}
- [ ] 8.5 Implement send message calling POST /api/chats/{userId} with optimistic UI update
- [ ] 8.6 Implement mark as read calling PUT /api/chats/{messageId}/read when opening conversation
- [ ] 8.7 Handle chat_not_opened error for Worker with user-friendly message
- [ ] 8.8 Handle no_application error for Employer with user-friendly message
- [ ] 8.9 Display unread_count badge on conversation list items
- [ ] 8.10 Add polling mechanism (5-10 second interval) for new messages while chat room is open

## Task 9: Dashboard API Integration

- [ ] 9.1 Replace worker dashboard dummy data with GET /api/dashboard/worker API call
- [ ] 9.2 Replace employer dashboard dummy data with GET /api/dashboard/employer API call
- [ ] 9.3 Connect shimmer loading state to dashboard API request lifecycle
- [ ] 9.4 Connect pull-to-refresh to re-fetch dashboard data

## Task 10: Verification API Integration

- [ ] 10.1 Implement email verification send code calling POST /api/auth/email/send-code
- [ ] 10.2 Implement email verification verify calling POST /api/auth/email/verify with code
- [ ] 10.3 Implement phone OTP send calling POST /api/auth/phone/send-otp
- [ ] 10.4 Implement phone OTP verify calling POST /api/auth/phone/verify-otp with OTP code
- [ ] 10.5 Implement employer document upload calling POST /api/auth/employer/document with multipart file and document_type
- [ ] 10.6 Update local verification_status after successful verification

## Task 11: Error Handling and Loading States

- [ ] 11.1 Ensure all pages display shimmer loading placeholders while waiting for API responses
- [ ] 11.2 Implement generic error state widget with retry button for 500 errors
- [ ] 11.3 Implement timeout error display with retry button
- [ ] 11.4 Ensure pull-to-refresh works as retry mechanism on all data-loading pages
- [ ] 11.5 Remove all remaining mock/dummy data references after API integration is complete
