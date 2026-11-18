<?php

/*
|--------------------------------------------------------------------------
| ThOR BESS website settings
|--------------------------------------------------------------------------
| Option lists mirror the Next.js site (frontend/app/data/*.ts) so the API
| validates exactly what the website forms can send.
*/

$csv = fn (?string $v) => array_values(array_filter(array_map('trim', explode(',', (string) $v))));

return [

    'frontend_url' => rtrim((string) env('FRONTEND_URL', 'http://localhost:3000'), '/'),

    'allowed_origins' => $csv(env('FRONTEND_URL', 'http://localhost:3000')),

    // Next.js server IPs whose X-Forwarded-For header is trusted (see AppServiceProvider).
    'trusted_proxies' => $csv(env('TRUSTED_PROXIES')),

    // Shared with the website's REVALIDATE_SECRET so blog edits appear there immediately.
    'revalidate_secret' => env('WEBSITE_REVALIDATE_SECRET'),

    'allow_registration' => (bool) env('ADMIN_ALLOW_REGISTRATION', false),

    'notify' => [
        'default' => $csv(env('LEADS_NOTIFY_EMAIL')),
        'quote' => $csv(env('QUOTE_NOTIFY_EMAIL')),
        'contact' => $csv(env('CONTACT_NOTIFY_EMAIL')),
    ],

    'seed_admin' => [
        'name' => env('ADMIN_SEED_NAME', 'ThOR Admin'),
        'email' => env('ADMIN_SEED_EMAIL', 'admin@thorenergybox.com'),
        'password' => env('ADMIN_SEED_PASSWORD'),
    ],

    'roles' => [
        'admin' => 'Administrator',
        'editor' => 'Editor',
    ],

    'lead_types' => [
        'contact' => 'Contact enquiry',
        'datasheet' => 'Datasheet request',
        'quote' => 'e-Generator quote',
    ],

    'lead_statuses' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'closed' => 'Closed',
        'spam' => 'Spam',
    ],

    'industries' => [
        'Renewable Energy', 'Industrial', 'Commercial', 'Utility & Microgrid',
        'EV Charging', 'Defense', 'Disaster Relief', 'Off-grid Farming',
    ],

    'dial_codes' => ['+91', '+971', '+1', '+44'],

    // Datasheet form options besides the LFP batteries (those come from the Products admin).
    'datasheet_extra' => [
        'thor-e-generator' => '"ThOR" e-Generator Model',
        'thor-bess-container' => '"ThOR" BESS Container',
    ],

    'egen_segments' => [
        'light' => 'Light Duty (2 kWh – 15 kWh)',
        'medium' => 'Medium Duty (20 kWh – 150 kWh)',
        'heavy' => 'Heavy Duty (200 kWh – 5 MWh)',
        'unsure' => 'Not sure — please advise',
    ],

    'egen_applications' => [
        'Home / office backup', 'Hospital / medical', 'Defence / security', 'Disaster & emergency', 'Agriculture',
        'EV charging', 'Telecom / communication', 'Construction / remote site', 'Micro-grid / rural electrification',
        'Industrial / commercial', 'Other',
    ],

    'charging_sources' => ['Grid', 'Solar', 'Wind', 'Vehicle', 'Bio-fuel / Hydro'],

    // Website pages that show an FAQ list (Admin → FAQs). Add a page here once the website renders FAQs on it.
    'faq_pages' => [
        'e-generator' => 'e-Generator page',
    ],

    // Inner pages with a banner image (Admin → Hero & banners). Keys match the website's PageBanner `page` prop.
    'banner_pages' => [
        'about' => 'About Us',
        'products' => 'Products',
        'lfp-battery' => 'LFP Battery',
        'technology' => 'Technology',
        'blog' => 'Blog & News',
        'contact' => 'Contact Us',
    ],

    'blog_categories' => ['LFP Technology', 'Energy Storage', 'Renewables', 'Buying Guide', 'Company News'],

];
