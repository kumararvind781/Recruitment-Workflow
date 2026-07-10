<?php
return [
    'app_name' => 'Recruitment Workflow System',
    'base_url' => '',
    'upload_dir' => __DIR__ . '/../../public/uploads/resumes/',
    'allowed_resume_types' => ['pdf','doc','docx'],
    'max_resume_size' => 5 * 1024 * 1024,
   'db' => [
    'host' => '68.178.227.144',
    'dbname' => 'recruitment_workflow',
    'username' => 'recruitment_workflow',
    'password' => 'recruitment_workflow',
    'charset' => 'utf8mb4'
]
];
