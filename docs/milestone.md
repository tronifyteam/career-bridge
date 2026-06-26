## **Milestone Development** 

_Aplikasi Job Board untuk Foreign Worker di Taiwan_ 

## **1. Milestone Summary** 

|**No.**|**Milestone**|**MainOutput**|
|---|---|---|
|0|Product Planning & System Design|PRD, user role, database awal, flow, verification logic, admin<br>flow.|
|1|Authentication & User Role|Register/login, role selection, RBAC, basic dashboard.|
|2|Worker Profile & CV|<br>Worker profile, CV upload, work status, skill, legal<br>acknowledgementbase.|
|3|Employer Profile & Verification|Company, factory, family care, agency verification.|
|4|Job Posting System|Create/edit/draft/submit/publish/pause/close job.|
|5|Job Review & Fake Vacancy Detection|Auto screening, red flag, risk level, manual review.|
|6|Job Search & Apply|Search/filter job, apply with CV, application tracking.|
|7|Applicant Management|Employer views CV, shortlist/reject, open chat.|
|8|<br>Chat System|<br>Chat per application, report/block, file sharing.|
|9|<br>Translation Subscription|<br>Chat translation, quota, daily/weekly/monthly pass.|
|10|<br>AI Job Safety Checker|<br>Analyze job/message/screenshot, risk level, suggested<br>questions.|
|11|Report & Trust System|Report job/chat/user, evidence, trust score, violation history.|
|12|Admin Panel|User, employer, agency, job, report, payment, ads management.|
|13|Payment & Subscription|Payment flow, subscription status, expiration, admin monitoring.|
|14|<br>Advertisement System|<br>Banner ads, featured job, sponsored label, ad review.|
|15|<br>Notification System|<br>Worker/employer/admin notifications.|
|16|<br>Multilingual UI & Guide|<br>Translation files and safety guide content.|
|17|<br>Security, Privacy & Audit Log|<br>RBAC, upload validation, privacy, audit logs.|
|18|<br>Testing & QA|<br>Critical scenarios, payment, permissions,fakejob testing.|
|19|BetaLaunch|Limited betawith workers, employers, agencies, admin.|
|20|Production Launch|Production server, domain, payment, monitoring, policies.|



## **Milestone 0 - Product Planning & System Design** 

**Tujuan:** Menjaga scope tetap jelas sebelum coding dimulai. 

## **Output** 

- Final PRD / Product Requirement Document 

- User role definition 

- Feature priority 

- Database structure awal 

- User flow diagram 

- Employer verification flow 

- Worker apply flow 

- Job review flow 

- Admin review flow 

- Payment/subscription flow 

## **Acceptance Criteria** 

- Semua role sudah terdefinisi 

- Flow apply dan chat sudah jelas 

- Flow verification employer sudah dipisah berdasarkan tipe 

- Admin review flow sudah siap sebelum development 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Milestone 1 - Authentication & User Role System** 

**Tujuan:** Membuat fondasi akun dan akses berdasarkan role. 

## **Output** 

- Register, login, logout, forgot password 

- Email verification 

- Phone verification/OTP optional 

- Role selection: Worker, Company, Factory, Family Care, Agency 

- Basic profile setup 

- Role-based access control 

## **Acceptance Criteria** 

- Worker tidak bisa akses employer dashboard 

- Employer tidak bisa akses worker-only features 

- Agency punya flow verifikasi berbeda 

- Admin bisa melihat user berdasarkan role 

## **Milestone 2 - Worker Profile & CV System** 

**Tujuan:** Worker bisa membuat profil dan mengirim CV saat apply. 

## **Output** 

- Worker profile 

- Nationality, city, preferred language 

- Worker type and current work status 

- Skills, experience, education, language ability 

- Upload/replace/delete CV PDF 

- CV visibility setting 

## **Acceptance Criteria** 

- Worker tidak bisa apply tanpa profile minimum 

- Worker tidak bisa apply tanpa CV 

- Worker harus menyetujui legal acknowledgement sebelum apply ke job foreign worker 

## **Milestone 3 - Employer Profile & Verification System** 

**Tujuan:** Employer masuk ke proses verifikasi sesuai tipe. 

## **Output** 

- Company employer verification 

- Factory employer verification 

- Family care employer verification 

- Agency license verification 

- Admin verification note 

- Verification status: unverified, pending, basic verified, manually verified, rejected 

## **Acceptance Criteria** 

- Employer belum verified tidak bisa publish job 

- Agency tanpa license number tidak bisa post migrant/caregiver job 

- Family care dan factory foreign-worker jobs wajib manual review 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Milestone 4 - Job Posting System** 

**Tujuan:** Employer bisa membuat lowongan sesuai tipe employer. 

## **Output** 

- Create job post 

- Edit job post 

- Save as draft 

- Submit for review 

- Publish after approval 

- Pause/close/duplicate job post 

- Field: title, location, duties, salary, hours, language, legal status, eligibility 

## **Acceptance Criteria** 

- Job dengan eligibility Unknown tidak bisa publish 

- Caregiver/factory/agency jobs wajib manual review 

- Worker hanya melihat job published 

## **Milestone 5 - Job Review & Fake Vacancy Detection** 

**Tujuan:** Mencegah fake job dan job berisiko sebelum publish. 

## **Output** 

- Auto screening 

- Rule-based red flags 

- Risk level: Low, Medium, High, Critical 

- Manual review queue 

- Admin approve/reject/suspend 

- Rejection reason 

## **Acceptance Criteria** 

- Job dengan illegal wording tidak langsung publish 

- Admin melihat alasan risk flag 

- Critical risk auto-reject/suspend 

- Employer melihat alasan jika job ditolak 

## **Milestone 6 - Job Search & Worker Application System** 

**Tujuan:** Worker bisa mencari job dan apply dengan CV. 

## **Output** 

- Search by keyword 

- Filter city/salary/category/language/employer type/legal eligibility 

- View job detail 

- Select CV 

- Add short message 

- Legal acknowledgement checkbox 

- Application tracking 

## **Acceptance Criteria** 

- Worker tidak bisa apply tanpa CV 

- Employer hanya bisa melihat CV worker yang apply 

- Worker bisa melihat status application 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Milestone 7 - Employer Applicant Management** 

**Tujuan:** Employer mengelola pelamar. 

## **Output** 

- View applicant list 

- View CV and worker profile 

- Shortlist applicant 

- Reject applicant 

- Open chat 

- Internal note 

- Filter applicant by status 

## **Acceptance Criteria** 

- Chat tidak terbuka otomatis saat apply 

- Chat hanya terbuka jika employer memilih continue/open chat 

- Worker mendapat update status 

## **Milestone 8 - Chat System** 

**Tujuan:** Employer dan worker bisa berkomunikasi setelah employer tertarik. 

## **Output** 

- Chat room per application 

- Text message 

- File/image attachment 

- CV and job post reference 

- Close chat 

- Block user 

- Report chat 

## **Acceptance Criteria** 

- Worker tidak bisa chat sebelum employer membuka chat 

- Employer tidak bisa chat worker yang belum apply 

- Semua chat memiliki job_id dan application_id 

## **Milestone 9 - Translation Chat Subscription** 

**Tujuan:** Monetisasi dari fitur translate dalam chat. 

## **Output** 

- Translate incoming/outgoing messages 

- Auto-detect language 

- Original + translated text 

- Manual re-translate 

- Translation quota 

- Free limited translation 

- Daily/weekly/monthly pass 

## **Acceptance Criteria** 

- Translation berhenti jika quota habis 

- User bisa membeli pass 

- Subscription tercatat di database 

- Translation history terhubung dengan chat message 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Milestone 10 - AI Job Safety Checker** 

**Tujuan:** Worker bisa mengecek risiko job atau pesan employer. 

## **Output** 

- Analyze job description 

- Analyze employer chat message 

- Analyze uploaded screenshot 

- Risk level 

- Risk reasons 

- Missing information 

- Suggested questions 

- Recommended action 

## **Acceptance Criteria** 

- AI tidak mengatakan 100% safe 

- AI tidak memberi legal guarantee 

- High/critical result menyarankan report/manual review 

## **Milestone 11 - Report & Trust System** 

**Tujuan:** Menjaga platform setelah job publish. 

## **Output** 

- Report job/employer/worker/chat 

- Upload screenshot evidence 

- Report reason 

- Report status 

- Employer/agency/worker trust score 

- Violation history 

## **Acceptance Criteria** 

- Banyak report membuat job masuk review ulang 

- Critical report bisa suspend job otomatis 

- Admin memberi keputusan valid/invalid 

- User mendapat notifikasi hasil report 

## **Milestone 12 - Admin Panel** 

**Tujuan:** Admin mengontrol operasional platform. 

## **Output** 

- Dashboard overview 

- User management 

- Employer verification 

- Agency verification 

- Job review queue 

- Report review 

- Trust score management 

- Payment/subscription monitoring 

- Ads review 

- Audit notes 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Acceptance Criteria** 

- Admin bisa approve/reject employer 

- Admin bisa approve/reject job 

- Admin bisa suspend user/job 

- Admin action tercatat di audit log 

## **Milestone 13 - Payment & Subscription System** 

**Tujuan:** Mengaktifkan monetisasi subscription. 

## **Output** 

- Daily/weekly/monthly pass 

- Worker/employer/agency translation plan 

- Payment history 

- Subscription status 

- Expiration date 

- Payment method integration 

## **Acceptance Criteria** 

- Payment success mengaktifkan subscription 

- Payment failed tidak mengaktifkan fitur 

- Expired subscription kembali ke free plan 

- Admin bisa melihat payment record 

## **Milestone 14 - Advertisement System** 

**Tujuan:** Monetisasi iklan dan featured job. 

## **Output** 

- Banner ads 

- Featured job 

- Sponsored job label 

- Ad package 

- Target language/nationality/job category 

- Impression/click tracking 

- Manual ad review 

## **Acceptance Criteria** 

- Iklan tidak muncul sebelum approved 

- Sponsored job diberi label jelas 

- Admin bisa pause iklan bermasalah 

## **Milestone 15 - Notification System** 

**Tujuan:** User mendapat update penting. 

## **Output** 

- Worker notification 

- Employer notification 

- Admin notification 

- In-app notification 

- Email notification 

- Notification preference 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Acceptance Criteria** 

- User menerima notifikasi event penting 

- User bisa mark as read 

- Notification preference bisa diatur 

## **Milestone 16 - Multilingual UI & Content Guide** 

**Tujuan:** Aplikasi bisa dipakai lintas bahasa. 

## **Output** 

- Translation files 

- UI language switcher 

- Indonesian, English, Traditional Chinese, Vietnamese, Tagalog, Thai, Japanese 

- Worker safety guide 

- Employer guide 

- Agency guide 

## **Acceptance Criteria** 

- UI text tidak hardcoded 

- Semua label utama pakai translation file 

- Guide tersedia minimal di bahasa utama 

## **Milestone 17 - Security, Privacy & Audit Log** 

**Tujuan:** Melindungi data user, CV, chat, dan dokumen. 

## **Output** 

- Password hashing 

- Role-based access control 

- Rate limiting 

- File upload validation 

- Admin permission control 

- Privacy control 

- Audit log 

## **Acceptance Criteria** 

- Employer tidak bisa melihat CV worker yang tidak apply 

- Admin action tercatat 

- File upload dibatasi tipe/ukuran 

- Sensitive data tidak tampil sembarangan 

## **Milestone 18 - Testing & QA** 

**Tujuan:** Memastikan fitur utama stabil. 

## **Output** 

- Auth testing 

- Role permission testing 

- Worker apply testing 

- Employer posting testing 

- Agency verification testing 

- Caregiver/factory review testing 

- Chat/translation/payment/report testing 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **Acceptance Criteria** 

- Critical bug = 0 

- Payment flow tested 

- Role permission tested 

- Manual review flow tested 

- Fake job filter tested 

## **Milestone 19 - Beta Launch** 

**Tujuan:** Test dengan user terbatas. 

## **Output** 

- 20-50 workers 

- 5-10 employers 

- 1-3 agencies if available 

- Admin internal 

- Collect usage data and feedback 

## **Acceptance Criteria** 

- Worker bisa apply tanpa dibantu developer 

- Employer bisa post job setelah verifikasi 

- Admin bisa review job/report 

- Translation dipakai di chat 

## **Milestone 20 - Production Launch** 

**Tujuan:** Rilis publik. 

## **Output** 

- Production server 

- Domain 

- Production database 

- Backup system 

- Payment production 

- Monitoring 

- Admin SOP 

- Terms of Service 

- Privacy Policy 

- Safety disclaimer 

## **Acceptance Criteria** 

- App bisa digunakan publik 

- Payment live 

- Admin siap review job/report 

- Backup dan monitoring aktif 

Draft product blueprint - Foreign Worker Job Board Taiwan 

## **2. Rekomendasi Pembagian Phase** 

|**Phase**|**Milestone**|**Target**|
|---|---|---|
|Phase 1 - Core MVP|Milestone 0-8|Job board dasar: auth, role, profile,<br>employer verification, job posting,<br>review, search, apply, applicant<br>management, chat.|
|Phase 2 - Safety & Monetization MVP|Milestone 9-13|<br>Translation subscription, AI safety<br>checker, report/trust, admin panel<br>lengkap, payment.|
|Phase 3 - Ads, Multilingual, Hardening|Milestone 14-18|<br>Ads, notification, multilingual UI,<br>security/privacy, QA.|
|Phase 4-Beta & Launch|Milestone 19-20|Beta test, feedback, production launch.|



## **3. MVP Paling Minimum** 

- Login/register 

- Worker profile 

- CV upload 

- Employer profile 

- Employer verification manual 

- Job posting 

- Admin job approval 

- Job search 

- Apply with CV 

- Employer view CV 

- Employer open chat 

- Basic chat 

- Translate chat 

- Report job/chat 

- Admin panel 

Draft product blueprint - Foreign Worker Job Board Taiwan 

