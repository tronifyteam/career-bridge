## **Job Board Profile & Verification Logic** 

## **1. Product Direction** 

- Platform model: job advertisement board, similar to 104/1111 style posting. 

- Main advantage: platform can help verify worker/employer documents before serious hiring. 

- Worker can search/apply easily; employer/agency/family employer must pass verification before posting sensitive jobs. 

- Employer can filter workers by document status: Ready to Work or Employer Self-Check Required. 

- Important rule: Verified Badge is not always the same as Ready to Work. 

## **2. User Roles** 

|**Role**|**Purpose**|**Main Permission**|
|---|---|---|
|Worker|Find job, apply, build profile|Search jobs, apply, upload verification<br>documents|
|Company / Factory|Post company jobs|Create job drafts, publish after verification|
|Agency Company /仲介公司|Post jobs as licensed agency|Publish only after agency verification|
|Agency Staff /業務|Recruit under agency company|Can act only after approved by agency<br>admin|
|Family Employer /看護工Employer|Find caregiver|Post masked caregiver job after review|
|Admin|Review and control risk|Approve/reject users, jobs, documents,<br>badges|



## **3. Worker Registration Flow** 

- Step 1: Login with phone number, Google, LINE, or other supported login. 

- Step 2: Choose worker category and residence/work status. 

- Step 3: Fill basic profile: name, nationality, city, job type, language, salary expectation, available date. 

- Step 4: Upload personal document + selfie. After admin/auto review, user gets Verified Badge. 

- Step 5: Upload extra work-status documents if needed. After approval, user gets Ready to Work label. 

## **4. Worker Status Logic** 

|**Worker Type / Status**|**Verified Badge Requirement**|**Ready to Work Requirement**|**Employer View**|
|---|---|---|---|
|Student ARC|Login + basic profile + personal<br>document + selfie|Student work permit approved|Ready / Need work permit<br>check|
|Blue Collar Migrant Worker|Login + basic profile + personal<br>document + selfie|Transfer document,<br>contract-ending proof, or valid<br>work-status document approved|Ready / Need transfer check|
|Professional / White Collar|Login + basic profile + personal<br>document + selfie|If open work right: Ready. If<br>sponsorship needed: Employer<br>Self-Check Required|Ready / Sponsorship needed|
|ARC Other /其他- Open Work<br>Right|Login + basic profile + personal<br>document + selfie|For MVP: no extra document<br>required after Verified Badge|Ready to Work|



Job Board MVP Spec - Profile & Verification Logic 

|APRC / Gold Card / Spouse<br>ARC with work right|Login + basic profile + personal<br>document + selfie|For MVP: no extra document<br>required after Verified Badge|Ready to Work|
|---|---|---|---|
|Not Sure / No ARC|Login + basic profile + selfie|Not eligible for Ready to Work<br>until admin review|Limited / Need verification|



## **5. Employer Side Logic** 

- Employer registers and selects type: Company, Agency Company, Agency Staff, or Family Employer. 

- Employer can create job draft before verification, but cannot freely publish high-risk jobs before approval. 

- After worker applies, employer sees worker status clearly: Ready to Work, Verified Only, or Unverified. 

- Employer can search workers in the same city and filter by work status, city, job category, nationality, language, availability, salary expectation, and sponsorship need. 

- Employer can choose: use platform-verified workers or verify documents by themselves. 

## **6. Employer Filter Requirement** 

- Filter: Ready to Work only. 

- Filter: Verified Badge only, documents need employer self-check. 

- Filter: Employer Self-Check Required. 

- Filter: No sponsorship needed. 

- Filter: Sponsorship needed. 

- Filter: City / district. 

- Filter: Worker type: student, blue collar, white collar, open work right. 

- Filter: Available date / urgent worker. 

- Filter: Language ability. 

## **7. Required Documents** 

|**Profile**|**Documents / Data**|**Result**|
|---|---|---|
|Worker - common|Phone/Google/LINE login, basic profile,<br>personal document, selfie|Verified Badge|
|Student worker|Student work permit|Ready to Work|
|Blue collar worker|Transfer document / contract ending proof /<br>work-status proof|Ready to Work|
|White collar worker|CV, diploma, portfolio, work experience. If<br>sponsorship needed, employer must check.|Verified / Sponsorship Needed|
|ARC Other / Open Work Right|No extra document required for MVP after<br>basic verification|Ready to Work|
|Company / Factory|Unified business number, company info,<br>contact person, business proof|Verified Employer|
|Agency Company|Agency license, unified business number,<br>company proof|Verified Agency|
|Agency Staff /業務|Business card or agency admin approval|Approved Staff|
|Family Employer /看護工|Identity, contact, city, basic caregiver need,<br>eligibility document if required|Verified Family Employer|



Job Board MVP Spec - Profile & Verification Logic 

## **8. Badges / Labels** 

- Verified Worker: identity/profile/selfie has been checked. 

- Ready to Work: user has enough verified work-status information for the selected worker type. 

- Employer Self-Check Required: worker is verified, but work eligibility documents are not fully verified by platform. 

- Sponsorship Needed: worker may need employer to process work permit. 

- Verified Employer: company/family employer passed platform verification. 

- Verified Agency: agency license/company status passed platform verification. 

## **9. Admin Panel Requirement** 

- View worker profile, selfie, documents, and selected residence/work status. 

- Approve/reject Verified Badge. 

- Approve/reject Ready to Work label. 

- Manually change worker status if needed. 

- View employer/company/agency verification documents. 

- Approve/reject employer permission to publish jobs. 

- Hide/suspend suspicious user or job posting. 

- Keep all documents private by default. 

## **10. Acceptance Criteria** 

- Worker can register with phone, Google, LINE, or other login method. 

- Worker can get Verified Badge after basic profile, personal document, and selfie are approved. 

- Student worker only gets Ready to Work after student work permit is approved. 

- Blue collar worker only gets Ready to Work after transfer/work-status document is approved. 

- ARC Other / 其他 - Open Work Right can get Ready to Work after basic verification in MVP. 

- Employer can filter workers by Ready to Work, Verified Only, Employer Self-Check Required, city, worker type, and availability. 

- Employer can see clear worker status after receiving applications. 

- Employer can decide to use platform-verified documents or verify documents by themselves. 

- Agency staff cannot act independently unless approved under a verified agency company. 

- Documents are not public and are visible only through permission/admin-controlled flow. 

- Admin can manually override verification status. 

## **11. Suggested Database Fields** 

workers: user_id, login_provider, worker_type, residence_status, verified_badge_status, ready_to_work_status, sponsorship_status, city, nationality, languages, available_date, expected_salary 

worker_documents: id, user_id, document_type, file_url, review_status, reviewed_by, reviewed_at 

employers: employer_id, employer_type, company_name, unified_number, verification_status, contact_person, city job_posts: job_id, employer_id, job_type, city, salary_range, publish_status, verification_required, created_at applications: application_id, job_id, worker_id, worker_status_snapshot, employer_review_status, created_at 

Job Board MVP Spec - Profile & Verification Logic 

