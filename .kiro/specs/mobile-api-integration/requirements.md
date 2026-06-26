# Requirements Document

## Introduction

This document defines the requirements for integrating the Flutter mobile app (migrant_work_tw) with the Laravel backend APIs. The mobile app currently uses dummy/local data across multiple UI pages. This feature ensures the backend APIs are fully functional (including database seeding) and connects all mobile app pages to consume real backend data via REST APIs with proper authentication, error handling, and state management.

## Glossary

- **Mobile_App**: The Flutter mobile application (migrant_work_tw) using GetX for state management
- **Backend_API**: The Laravel REST API server deployed at the production host, using Sanctum token authentication
- **API_Client**: The base HTTP client layer in the mobile app (base_connect.dart) responsible for making authenticated requests
- **Auth_Token**: A Laravel Sanctum bearer token issued upon login/register, used to authenticate all protected API requests
- **Worker**: A user with role ROLE_WORKER who searches and applies for jobs on the platform
- **Employer**: A user with role ROLE_COMPANY, ROLE_FACTORY, ROLE_FAMILY_CARE, or ROLE_AGENCY who posts job listings
- **Category_Seeder**: A Laravel database seeder that populates the categories table with job category data
- **City_Seeder**: A Laravel database seeder that populates the cities table with Taiwan city data
- **API_Envelope**: The standard JSON response wrapper `{ "success": bool, "data": ..., "message": ..., "error": ... }`
- **Chat_System**: The REST-based messaging system between Workers and Employers via /api/chats endpoints

## Requirements

### Requirement 1: Backend Database Seeding

**User Story:** As a developer, I want the backend database to be seeded with categories and cities data, so that the mobile app can retrieve non-empty dropdown options for job creation and filtering.

#### Acceptance Criteria

1. WHEN the Category_Seeder is executed, THE Backend_API SHALL populate the categories table with at least 8 job categories including Manufacturing, Construction, Domestic Care, Logistics, Agriculture, Fisheries, Hospitality, and Technology
2. WHEN the City_Seeder is executed, THE Backend_API SHALL populate the cities table with at least 9 Taiwan cities including Taipei, New Taipei City, Taoyuan, Taichung, Tainan, Kaohsiung, Hsinchu, Keelung, and Hualien
3. WHEN the GET /api/categories endpoint is called after seeding, THE Backend_API SHALL return a JSON response with success=true and a non-empty data array containing id, name, slug, and icon fields for each category
4. WHEN the GET /api/cities endpoint is called after seeding, THE Backend_API SHALL return a JSON response with success=true and a non-empty data array containing id, name, and region fields for each city

### Requirement 2: Authentication API Integration

**User Story:** As a user, I want to register and login through the mobile app, so that I can access personalized features with a secure session.

#### Acceptance Criteria

1. WHEN a user submits valid registration data (full_name, email, password, password_confirmation), THE Mobile_App SHALL send a POST request to /api/auth/register and store the returned Auth_Token and user profile locally
2. WHEN a user submits valid login credentials (email, password), THE Mobile_App SHALL send a POST request to /api/auth/login and store the returned Auth_Token and user profile locally
3. WHEN the Backend_API returns a login or register response, THE API_Client SHALL parse the API_Envelope and extract the user object including id, email, full_name, token, role_id, profile_completed, nationality, current_city, company_name, industry, avatar_url, phone, verification_status, cv_url, and preferred_language fields
4. IF the Backend_API returns an invalid_credentials error on login, THEN THE Mobile_App SHALL display the error message to the user without storing any token
5. WHEN a user triggers logout, THE Mobile_App SHALL send a POST request to /api/auth/logout, clear the stored Auth_Token, and navigate to the login screen

### Requirement 3: Profile Setup API Integration

**User Story:** As a user, I want to complete my profile setup through the mobile app, so that my information is persisted on the server.

#### Acceptance Criteria

1. WHEN a Worker submits profile data (full_name, nationality, current_city, phone), THE Mobile_App SHALL send a PUT request to /api/auth/profile with the form data and update the local user state with the response
2. WHEN an Employer submits profile data (full_name, company_name, industry, phone), THE Mobile_App SHALL send a PUT request to /api/auth/profile with the form data and update the local user state with the response
3. WHEN a user selects a role during onboarding, THE Mobile_App SHALL send a PUT request to /api/auth/role with the selected role_id and update the local user state
4. WHEN a Worker uploads a CV file, THE Mobile_App SHALL send a POST multipart request to /api/auth/cv with the PDF file and update the local cv_url field from the response
5. IF the Backend_API returns a validation error on profile save, THEN THE Mobile_App SHALL display field-level error messages to the user

### Requirement 4: Job Listings API Integration

**User Story:** As a Worker, I want to browse real job listings from the backend, so that I can find and apply to actual job opportunities.

#### Acceptance Criteria

1. WHEN the Worker opens the home page, THE Mobile_App SHALL send a GET request to /api/jobs and display the returned job listings replacing the current dummy data
2. WHEN a Worker applies search or filter parameters (search text, city, category), THE Mobile_App SHALL append query parameters to the GET /api/jobs request and display the filtered results
3. WHEN a Worker taps on a job listing, THE Mobile_App SHALL send a GET request to /api/jobs/{id} and display the full job detail including title, employer_name, employer_type, location, salary, salary_period, tags, category, description, duties, requirements, benefits, is_urgent, posted_at, and risk_level
4. WHILE the Mobile_App is loading job data from the Backend_API, THE Mobile_App SHALL display shimmer loading placeholders
5. WHEN a Worker performs pull-to-refresh on the job list, THE Mobile_App SHALL re-fetch job data from GET /api/jobs and update the displayed list

### Requirement 5: Job Creation API Integration

**User Story:** As an Employer, I want to create job listings through the mobile app, so that Workers can discover my job opportunities.

#### Acceptance Criteria

1. WHEN the Employer opens the create job form, THE Mobile_App SHALL fetch categories from GET /api/categories and cities from GET /api/cities to populate the dropdown fields
2. WHEN an Employer submits a new job listing, THE Mobile_App SHALL send a POST request to /api/jobs with the job data including title, description, duties, requirements, benefits, salary, salary_period, category, location, tags, and is_urgent fields
3. IF the Backend_API returns an unverified_employer error, THEN THE Mobile_App SHALL display a message indicating the employer must complete verification before posting jobs
4. IF the Backend_API returns a missing_license error for agency employers, THEN THE Mobile_App SHALL display a message indicating a license number is required
5. WHEN the job creation request succeeds, THE Mobile_App SHALL navigate back to the employer dashboard and display the newly created job in the listings

### Requirement 6: Job Applications API Integration

**User Story:** As a Worker, I want to apply for jobs and track my applications through the mobile app, so that I can manage my job search progress.

#### Acceptance Criteria

1. WHEN a Worker taps the apply button on a job detail page, THE Mobile_App SHALL send a POST request to /api/jobs/{job_id}/apply with an optional cover_letter field
2. IF the Backend_API returns an incomplete_profile error on job application, THEN THE Mobile_App SHALL display a message indicating the Worker must complete profile setup first
3. IF the Backend_API returns a missing_cv error on job application, THEN THE Mobile_App SHALL display a message indicating the Worker must upload a CV first
4. WHEN a Worker opens the applications tracker page, THE Mobile_App SHALL send a GET request to /api/applications and display the list of applications with job_title, employer_name, status, and applied_at fields
5. WHEN an Employer views applicants for a specific job, THE Mobile_App SHALL send a GET request to /api/jobs/{jobId}/applicants and display the list of applicants with user details and application status
6. WHEN an Employer updates an application status, THE Mobile_App SHALL send a PUT request to /api/applications/{id}/status with the new status value

### Requirement 7: Chat System API Integration

**User Story:** As a user, I want to exchange messages with other users through the chat system, so that Workers and Employers can communicate about job opportunities.

#### Acceptance Criteria

1. WHEN a user opens the chat tab, THE Mobile_App SHALL send a GET request to /api/chats and display the list of conversations with partner name, avatar, last message, last message timestamp, and unread count
2. WHEN a user opens a specific conversation, THE Mobile_App SHALL send a GET request to /api/chats/{userId} and display the message history in chronological order
3. WHEN a user sends a message in a conversation, THE Mobile_App SHALL send a POST request to /api/chats/{userId} with the message text and append the sent message to the chat view
4. IF the Backend_API returns a chat_not_opened error when a Worker tries to send a message, THEN THE Mobile_App SHALL display a message indicating the Employer must initiate the conversation first
5. IF the Backend_API returns a no_application error when an Employer tries to initiate a chat, THEN THE Mobile_App SHALL display a message indicating the Worker must have applied to one of the Employer's job listings first
6. WHEN a user opens a conversation, THE Mobile_App SHALL mark received messages as read by calling PUT /api/chats/{messageId}/read for unread messages
7. WHILE the Mobile_App is on the chat list page, THE Mobile_App SHALL display the unread_count badge for each conversation with unread messages

### Requirement 8: API Client Authentication Layer

**User Story:** As a developer, I want a centralized API client that handles authentication headers and token management, so that all API requests are properly authenticated without duplicating logic.

#### Acceptance Criteria

1. THE API_Client SHALL attach the Authorization header with "Bearer {Auth_Token}" to all requests targeting authenticated endpoints
2. WHEN the API_Client receives a 401 Unauthorized response from the Backend_API, THE Mobile_App SHALL clear the stored Auth_Token and navigate the user to the login screen
3. THE API_Client SHALL use the base URL configured for the Backend_API (production host) for all API requests
4. WHEN the API_Client receives any API response, THE API_Client SHALL parse the API_Envelope format and extract the success status, data payload, and error message
5. IF the API_Client encounters a network connectivity error, THEN THE Mobile_App SHALL display a user-friendly offline error message

### Requirement 9: Dashboard API Integration

**User Story:** As a user, I want to see my personalized dashboard with real data, so that I can get an overview of my activity on the platform.

#### Acceptance Criteria

1. WHEN a Worker opens the dashboard, THE Mobile_App SHALL send a GET request to /api/dashboard/worker and display the returned statistics and summary data
2. WHEN an Employer opens the dashboard, THE Mobile_App SHALL send a GET request to /api/dashboard/employer and display the returned statistics and summary data
3. WHILE the Mobile_App is loading dashboard data, THE Mobile_App SHALL display shimmer loading placeholders
4. WHEN a user performs pull-to-refresh on the dashboard, THE Mobile_App SHALL re-fetch dashboard data from the appropriate endpoint

### Requirement 10: Error Handling and Loading States

**User Story:** As a user, I want to see clear loading indicators and error messages, so that I understand the current state of the app and can take corrective action when something goes wrong.

#### Acceptance Criteria

1. WHILE the Mobile_App is waiting for any API response, THE Mobile_App SHALL display shimmer loading placeholders appropriate to the page layout
2. IF the Backend_API returns a validation error (HTTP 422), THEN THE Mobile_App SHALL parse the field-level error messages and display them next to the corresponding form fields
3. IF the Backend_API returns a server error (HTTP 500), THEN THE Mobile_App SHALL display a generic error message with a retry option
4. WHEN an API request fails due to network timeout, THE Mobile_App SHALL display a timeout error message with a retry button
5. WHEN a user taps a retry button or performs pull-to-refresh after an error, THE Mobile_App SHALL re-attempt the failed API request

### Requirement 11: Verification API Integration

**User Story:** As a user, I want to verify my email and phone number through the mobile app, so that I can increase my trust level and access more platform features.

#### Acceptance Criteria

1. WHEN a user requests email verification, THE Mobile_App SHALL send a POST request to /api/auth/email/send-code and notify the user that a verification code has been sent
2. WHEN a user submits an email verification code, THE Mobile_App SHALL send a POST request to /api/auth/email/verify with the code and update the local verification status on success
3. WHEN a user requests phone OTP, THE Mobile_App SHALL send a POST request to /api/auth/phone/send-otp and notify the user that an OTP has been sent
4. WHEN a user submits a phone OTP code, THE Mobile_App SHALL send a POST request to /api/auth/phone/verify-otp with the OTP and update the local verification status on success
5. WHEN an Employer uploads a verification document, THE Mobile_App SHALL send a POST multipart request to /api/auth/employer/document with the file and document_type, and update the verification_status to pending
