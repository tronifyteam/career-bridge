import re

filepath = r"d:\INFORMATICS\FREELANCE\migrant_work_tw_be\app\Http\Controllers\Admin\AdminWorkerController.php"

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

injections = [
    (r"(public function approveSelfie.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('approve_selfie', $user, 'Admin approved selfie');\n        \2"),

    (r"(public function rejectSelfie.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('reject_selfie', $user, 'Admin rejected selfie with note: ' . $request->note);\n        \2"),

    (r"(public function approveDocument.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('approve_document', $document, 'Admin approved worker document');\n        \2"),

    (r"(public function rejectDocument.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('reject_document', $document, 'Admin rejected worker document with note: ' . $request->note);\n        \2"),

    (r"(public function overrideBadge.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('override_badge', $user, 'Admin overrode verified badge to ' . $request->status);\n        \2"),

    (r"(public function approveEmployerDocument.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('approve_employer_document', $document, 'Admin approved employer document');\n        \2"),

    (r"(public function rejectEmployerDocument.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('reject_employer_document', $document, 'Admin rejected employer document with note: ' . $request->note);\n        \2"),

    (r"(public function approveEmployer.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('approve_employer', $employer, 'Admin approved employer registration');\n        \2"),

    (r"(public function rejectEmployer.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('reject_employer', $employer, 'Admin rejected employer registration with note: ' . $request->note);\n        \2"),

    (r"(public function suspendEmployer.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('suspend_employer', $employer, 'Admin suspended employer with note: ' . ($request->note ?? 'N/A'));\n        \2"),

    (r"(public function suspendJob.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('suspend_job', $job, 'Admin suspended job');\n        \2"),

    (r"(public function restoreJob.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('restore_job', $job, 'Admin restored job');\n        \2"),

    (r"(public function suspendUser.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('suspend_user', $user, 'Admin suspended user with reason: ' . ($request->reason ?? 'N/A'));\n        \2"),

    (r"(public function restoreUser.*?)(return response\(\)->json\(\[\s*'success'\s*=>\s*true)",
     r"\1\n        \\App\\Services\\AuditLogService::log('restore_user', $user, 'Admin restored user');\n        \2"),
]

for pattern, replacement in injections:
    content = re.sub(pattern, replacement, content, count=1, flags=re.DOTALL)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Updated AdminWorkerController.php")
