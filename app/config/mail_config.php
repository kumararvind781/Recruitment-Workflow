
<?php
/**
 * Recruitment Workflow SMTP Configuration
 */

// return [
//     'host'       => 'smtp.gmail.com',
//     'port'       => 587,
//     'encryption' => 'tls',

//     // Gmail SMTP authentication
//     'username'   => 'arvindunire@gmail.com',
//     'password'   => 'fqjkymdstdbsnyqd',

//     // Sender details
//     'from_email' => 'arvindunire@gmail.com',
//     'from_name'  => 'Recruitment Workflow - Password Reset',

//     // OTP recipient
//     'to_email'   => 'arvind.sharma@unire.co.in',
// ];




return [
    'host'       => 'sg2plzcpnl493864.prod.sin2.secureserver.net',
    'port'       => 465,
    'encryption' => 'ssl',

    // SMTP authentication
    'username'   => 'crm@unire.in',
     'password'   => '4gK*7EHayPQKjpBT',

    // Sender
    'from_email' => 'crm@unire.in',
    'from_name'  => 'Recruitment Workflow - OTP',

    // OTP receiver
    'to_email'   => 'arvind.sharma@unire.co.in',
];