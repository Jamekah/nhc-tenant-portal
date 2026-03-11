<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'default_payment_frequency',
                'value' => 'fortnightly',
                'type' => 'string',
                'group' => 'payment',
                'label' => 'Default Payment Frequency',
                'description' => 'Default billing frequency for new tenancies.',
            ],
            [
                'key' => 'overdue_threshold_days',
                'value' => '14',
                'type' => 'integer',
                'group' => 'payment',
                'label' => 'Overdue Threshold Days',
                'description' => 'Number of days after due date before invoice is marked overdue.',
            ],
            [
                'key' => 'arrears_threshold_days',
                'value' => '30',
                'type' => 'integer',
                'group' => 'payment',
                'label' => 'Arrears Threshold Days',
                'description' => 'Number of days overdue before tenant is flagged as in arrears.',
            ],
            [
                'key' => 'invoice_prefix',
                'value' => 'INV',
                'type' => 'string',
                'group' => 'invoice',
                'label' => 'Invoice Number Prefix',
                'description' => 'Prefix for auto-generated invoice numbers.',
            ],
            [
                'key' => 'invoice_due_days',
                'value' => '14',
                'type' => 'integer',
                'group' => 'invoice',
                'label' => 'Payment Due Days',
                'description' => 'Number of days from issue date until payment is due.',
            ],
            [
                'key' => 'organization_name',
                'value' => 'National Housing Corporation',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Organization Name',
                'description' => 'Name of the organization displayed in documents.',
            ],
            [
                'key' => 'support_email',
                'value' => 'support@nhc.gov.pg',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Support Email',
                'description' => 'Email address for tenant support inquiries.',
            ],
            [
                'key' => 'support_phone',
                'value' => '+675 321 7000',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Support Phone',
                'description' => 'Phone number for tenant support.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
