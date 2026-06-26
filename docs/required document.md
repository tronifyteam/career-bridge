Taiwan Job Board Required Documents | v1.2 Simple Logic 

**Taiwan Job Board Required Documents** 

**Simple Verification Logic | Version 1.2 | Internal Draft** 

## **1. Purpose** 

This document lists the minimum documents the platform should collect to review worker, employer, family employer, and agency accounts for a Taiwan job board model similar to 104/1111. 

The platform only provides job posting, worker application, and document-based filtering. It does not act as a recruitment agency, does not match candidates, and does not handle work permit applications. 

Worker badges are intentionally simple: Verified and Ready to Work. 

## **2. Worker Badge Definitions** 

|**Badge**|**Meaning**|**Platform Review Scope**|
|---|---|---|
|Verified|The worker identity/profile is reviewed.|Basic profile + ARC/APRC/Gold<br>Card/National ID + selfie match.|
|Ready to Work|The worker has uploaded enough status<br>proof for the selected category.|This is only a platform review label.<br>Employer still handles final legal hiring<br>checkswhen required.|



**Important:** Verified is not always the same as Ready to Work. The platform should not promise that every foreign worker can immediately start a job. 

## **3. Worker Document Checklist - 4 Simple Categories** 

|**Worker Category**|**Verified Requirement**|**Ready to Work Requirement**|**Admin Review Notes**|
|---|---|---|---|
|Student|ARC front/back + selfie + basic<br>profile.|Student work permit.|Approve Ready to Work only after<br>the student work permit is valid<br>and not expired.|
|Taiwanese|Taiwan National ID front/back +<br>selfie + basic profile.|Automatic after Verified.|For local Taiwanese users, no<br>foreign work permit logic is<br>needed.|
|Blue Collar Migrant Worker|ARC front/back + selfie + basic<br>profile.|Transfer letter / contract-ending<br>proof / employer-change proof.|Do not require current<br>employment/work permit for<br>Ready to Work. Use the uploaded<br>transfer-related document date to<br>create reminder/expiry alerts.|
|Other|ARC/APRC/Gold Card front/back<br>+ selfie + basic profile.|Approve if document clearly<br>shows APRC, Employment Gold<br>Card, ROC spouse residence, or<br>ARC Other/其他. White-collar<br>employment ARC = Verified only.|Gold Card and APRC should be<br>Ready to Work. ARC Other/其他<br>can be Ready to Work when<br>accepted by platform policy.<br>White-collar employment ARC is<br>not Ready to Work because new<br>employer hiring process happens<br>after acceptance.|



## **4. Other Category - Internal Review Rule** 

|**Uploaded Card / ARC Type**|**Result**|**Simple Rule**|
|---|---|---|
|APRC|Verified + Readyto Work|Approve if valid and identitymatches.|
|Employment Gold Card|Verified + Ready to Work|Approve if valid and identity matches. Gold<br>Card is treated as open work right.|
|Spouse of ROC Citizen ARC|Verified+Ready to Work|Approve if ARC/residence basis is clear.|
|ARC Other /其他|Verified + Ready to Work|Approve if ARC is valid and identity matches.<br>This keeps the MVP simple for users who hold<br>post-graduation/other residence status.|
|White-collar employment ARC|Verified only|Do not issue Ready to Work. Employer<br>handles hiring/work permit process after<br>choosingthe candidate.|



Internal draft - for product verification planning only 

Taiwan Job Board Required Documents | v1.2 Simple Logic 

## **5. Employer Document Checklist** 

|**Employer Type**|**Required Documents**|**Posting Permission**|**Admin Review Notes**|
|---|---|---|---|
|Company|Company registration / unified<br>business number / contact person<br>ID or authorization / company<br>address / phone / email.|Can post company jobs after<br>employer verification.|Job post must include salary<br>range, work location, working<br>hours, job description,<br>employment type, and contact<br>method.|
|Factory|Company registration / unified<br>business number / factory<br>registration or factory proof /<br>contact person authorization /<br>work location.|Can post factory jobs after<br>company + factory verification.|For blue-collar jobs, collect job<br>category, worker count, salary,<br>working hours, employment<br>period, dorm/meals info. Permit<br>documents are optional unless<br>platform wants higher review<br>level.|
|Family Employer|Employer ID / phone / address /<br>care recipient ID or basic care<br>need proof / relationship proof if<br>needed.|Can post caregiver/family jobs<br>after family employer verification.|Keep caregiver job posts<br>masked/reviewed. Do not publish<br>sensitive medical/care documents<br>publicly.|



## **6. Agency Document Checklist** 

|**Agency Type**|**Required Documents**|**Posting Permission**|**Admin Review Notes**|
|---|---|---|---|
|Agency Company|Company registration / unified<br>business number / private<br>employment service agency<br>permit / license number / license<br>expiry date / office address /<br>contact person.|Can post only after agency<br>verification.|Agency should be clearly labeled<br>as agency, not direct employer.|
|Agency Staff /業務|Staff name / phone / business<br>card or internal agency approval /<br>linkedverified agency company.|Can act only under verified<br>agency company.|Do not allow independent posting<br>unless approved by the agency<br>company admin.|
|Agency Job Post|Employer authorization or job<br>source proof / actual employer or<br>hiring source info for admin<br>review / fee table if applicable.|Publish only if source is clear.|High fake-job risk if agency<br>cannot show employer<br>authorization or job source.|



## **7. Job Post Minimum Required Fields** 

|**Field**|**Requirement**|
|---|---|
|Job title andjob category|Required.|
|Employer type|Company/ Factory/ FamilyEmployer / Agency.|
|Salary range|Required. Salary range must be displayed for jobs below<br>NT$40,000/month.|
|Work location|Required. City/district/address level depending on privacy risk.|
|Working hours and rest days|Required.|
|Employment type|Full-time / part-time / contract / shift / live-in caregiver.|
|Dormitory / meals / deductions|Required for blue-collar/factory/caregiver jobs if applicable.|
|Contact method|Required but can be masked through the platform.|



## **8. Admin Review Rules** 

- Check that document name/photo matches selfie and profile name. 

- Check document expiry date when visible. 

- Reject blurred, cropped, edited, mismatched, or suspicious documents. 

- Documents are private by default. Employers should see status labels, not full sensitive documents unless the worker gives 

- permission. 

- Do not display legal guarantees such as “100% legal to work”. Use “Ready to Work (platform-reviewed)” instead. 

- For blue-collar workers, use transfer-related document date to show reminder: “Your transfer/job-seeking period may be ending 

- soon.” 

- For white-collar employment ARC, show Verified only. Employer and worker handle hiring/work permit steps outside platform. 

Internal draft - for product verification planning only 

