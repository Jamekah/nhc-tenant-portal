<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Region;
use App\Models\SupportTicket;
use App\Models\Tenancy;
use App\Models\TicketComment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed realistic Papua New Guinea demo data for the NHC Portal.
     *
     * Scenarios:
     *  - 5 tenants: in_good_standing (fully paid, no overdue)
     *  - 4 tenants: overdue (1-2 months behind)
     *  - 3 tenants: in_arrears (3+ months behind)
     *  - 2 tenants: with active support tickets
     *  - 1 tenant: terminated lease (historical data)
     *  - 1 tenant (David Kila): partial payment demo scenario
     */
    public function run(): void
    {
        // ─── REGIONS ─────────────────────────────────────────────
        $regions = $this->createRegions();

        // ─── STAFF USERS ─────────────────────────────────────────
        $staffUsers = $this->createStaffUsers($regions);

        // ─── TENANT USERS ────────────────────────────────────────
        $tenantUsers = $this->createTenantUsers();

        // ─── PROPERTIES ──────────────────────────────────────────
        $properties = $this->createProperties($regions);

        // ─── TENANCIES, INVOICES & PAYMENTS ──────────────────────
        $this->createTenanciesAndBilling(
            $tenantUsers,
            $properties,
            $staffUsers
        );

        // ─── SUPPORT TICKETS ─────────────────────────────────────
        $this->createSupportTickets($tenantUsers, $staffUsers);
    }

    /**
     * Create the 4 PNG regions with real suburb names.
     */
    private function createRegions(): array
    {
        $regionData = [
            [
                'name' => 'National Capital District',
                'code' => 'NCD',
                'description' => 'Includes Boroko, Hohola, Waigani, Korobosea, Gerehu suburbs of Port Moresby',
                'is_active' => true,
            ],
            [
                'name' => 'Morobe Province',
                'code' => 'MBP',
                'description' => 'Includes Eriku, Top Town, Chinatown suburbs of Lae City',
                'is_active' => true,
            ],
            [
                'name' => 'Eastern Highlands Province',
                'code' => 'EHP',
                'description' => 'Includes North Goroka, South Goroka suburbs of Goroka Town',
                'is_active' => true,
            ],
            [
                'name' => 'Madang Province',
                'code' => 'MDP',
                'description' => 'Includes Town Area, Newtown suburbs of Madang Town',
                'is_active' => true,
            ],
        ];

        $regions = [];
        foreach ($regionData as $data) {
            $regions[$data['code']] = Region::create($data);
        }

        return $regions;
    }

    /**
     * Create staff users (Super Admin, Admins) and the featured demo client.
     */
    private function createStaffUsers(array $regions): array
    {
        $password = Hash::make('demo2026!');

        // Super Admin
        $superAdmin = User::create([
            'name' => 'James Kuru',
            'email' => 'superadmin@nhc.gov.pg',
            'password' => $password,
            'phone' => '+675 7000 1001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        // Admin NCD
        $adminNcd = User::create([
            'name' => 'Mary Toea',
            'email' => 'admin.ncd@nhc.gov.pg',
            'password' => $password,
            'phone' => '+675 7000 1002',
            'is_active' => true,
            'region_id' => $regions['NCD']->id,
            'email_verified_at' => now(),
        ]);
        $adminNcd->assignRole('admin');

        // Admin Morobe
        $adminMorobe = User::create([
            'name' => 'Peter Lahu',
            'email' => 'admin.morobe@nhc.gov.pg',
            'password' => $password,
            'phone' => '+675 7000 1003',
            'is_active' => true,
            'region_id' => $regions['MBP']->id,
            'email_verified_at' => now(),
        ]);
        $adminMorobe->assignRole('admin');

        // Featured client user (David Kila — the demo login account)
        $featuredClient = User::create([
            'name' => 'David Kila',
            'email' => 'tenant@example.pg',
            'password' => $password,
            'phone' => '+675 7100 2001',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $featuredClient->assignRole('client');

        return [
            'super_admin' => $superAdmin,
            'admin_ncd' => $adminNcd,
            'admin_morobe' => $adminMorobe,
            'featured_client' => $featuredClient,
        ];
    }

    /**
     * Create 15 tenant users with realistic PNG names.
     */
    private function createTenantUsers(): array
    {
        $password = Hash::make('demo2026!');

        $tenants = [
            ['name' => 'Sarah Waim',       'email' => 'sarah.waim@example.pg',       'phone' => '+675 7100 2002'],
            ['name' => 'Thomas Mondo',      'email' => 'thomas.mondo@example.pg',     'phone' => '+675 7100 2003'],
            ['name' => 'Grace Hombiri',     'email' => 'grace.hombiri@example.pg',    'phone' => '+675 7100 2004'],
            ['name' => 'Michael Aku',       'email' => 'michael.aku@example.pg',      'phone' => '+675 7100 2005'],
            ['name' => 'Rebecca Loi',       'email' => 'rebecca.loi@example.pg',      'phone' => '+675 7100 2006'],
            ['name' => 'John Opa',          'email' => 'john.opa@example.pg',         'phone' => '+675 7100 2007'],
            ['name' => 'Martha Kaia',       'email' => 'martha.kaia@example.pg',      'phone' => '+675 7100 2008'],
            ['name' => 'Simon Gure',        'email' => 'simon.gure@example.pg',       'phone' => '+675 7100 2009'],
            ['name' => 'Elizabeth Ruma',     'email' => 'elizabeth.ruma@example.pg',   'phone' => '+675 7100 2010'],
            ['name' => 'Anna Balo',         'email' => 'anna.balo@example.pg',        'phone' => '+675 7100 2011'],
            ['name' => 'Peter Nao',         'email' => 'peter.nao@example.pg',        'phone' => '+675 7100 2012'],
            ['name' => 'Ruth Siga',         'email' => 'ruth.siga@example.pg',        'phone' => '+675 7100 2013'],
            ['name' => 'Steven Koi',        'email' => 'steven.koi@example.pg',       'phone' => '+675 7100 2014'],
            ['name' => 'Agnes Wari',        'email' => 'agnes.wari@example.pg',       'phone' => '+675 7100 2015'],
            ['name' => 'Paul Mek',          'email' => 'paul.mek@example.pg',         'phone' => '+675 7100 2016'],
        ];

        $tenantUsers = [];
        foreach ($tenants as $tenant) {
            $user = User::create([
                'name' => $tenant['name'],
                'email' => $tenant['email'],
                'password' => $password,
                'phone' => $tenant['phone'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('client');
            $tenantUsers[] = $user;
        }

        return $tenantUsers;
    }

    /**
     * Create 20 properties across 4 regions with realistic PGK rents.
     */
    private function createProperties(array $regions): array
    {
        $properties = [];

        // ── NCD: 8 properties (6 residential, 1 institutional, 1 land) ──
        $ncdProperties = [
            [
                'property_code' => 'NCD-RES-0001',
                'type' => 'residential',
                'title' => 'Section 23, Lot 5 - Boroko',
                'address' => 'Lot 5, Section 23, Boroko',
                'suburb' => 'Boroko',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => 3,
                'size_sqm' => 120.00,
                'monthly_rent' => 2800.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-RES-0002',
                'type' => 'residential',
                'title' => 'Section 15, Lot 12 - Hohola',
                'address' => 'Lot 12, Section 15, Hohola',
                'suburb' => 'Hohola',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => 2,
                'size_sqm' => 85.00,
                'monthly_rent' => 1800.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-RES-0003',
                'type' => 'residential',
                'title' => 'Section 42, Lot 8 - Waigani',
                'address' => 'Lot 8, Section 42, Waigani',
                'suburb' => 'Waigani',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => 4,
                'size_sqm' => 160.00,
                'monthly_rent' => 3500.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-RES-0004',
                'type' => 'residential',
                'title' => 'Section 30, Lot 3 - Korobosea',
                'address' => 'Lot 3, Section 30, Korobosea',
                'suburb' => 'Korobosea',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => 2,
                'size_sqm' => 90.00,
                'monthly_rent' => 2000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-RES-0005',
                'type' => 'residential',
                'title' => 'Section 56, Lot 14 - Gerehu',
                'address' => 'Lot 14, Section 56, Gerehu Stage 3',
                'suburb' => 'Gerehu',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => 3,
                'size_sqm' => 110.00,
                'monthly_rent' => 1500.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-RES-0006',
                'type' => 'residential',
                'title' => 'Section 10, Lot 22 - Boroko',
                'address' => 'Lot 22, Section 10, Boroko',
                'suburb' => 'Boroko',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => 1,
                'size_sqm' => 55.00,
                'monthly_rent' => 1200.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-INS-0001',
                'type' => 'institutional',
                'title' => 'NHC Office Complex - Waigani',
                'address' => 'Lot 1, Section 44, Waigani Drive',
                'suburb' => 'Waigani',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => null,
                'size_sqm' => 450.00,
                'monthly_rent' => 15000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'NCD-LND-0001',
                'type' => 'land',
                'title' => 'Vacant Land - Gerehu Stage 6',
                'address' => 'Lot 45, Section 62, Gerehu Stage 6',
                'suburb' => 'Gerehu',
                'city' => 'Port Moresby',
                'province' => 'National Capital District',
                'bedrooms' => null,
                'size_sqm' => 600.00,
                'monthly_rent' => 2000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
        ];

        foreach ($ncdProperties as $prop) {
            $prop['region_id'] = $regions['NCD']->id;
            $properties[] = Property::create($prop);
        }

        // ── MBP: 5 properties (4 residential, 1 institutional) ──
        $mbpProperties = [
            [
                'property_code' => 'MBP-RES-0001',
                'type' => 'residential',
                'title' => 'Section 7, Lot 3 - Eriku',
                'address' => 'Lot 3, Section 7, Eriku',
                'suburb' => 'Eriku',
                'city' => 'Lae',
                'province' => 'Morobe Province',
                'bedrooms' => 3,
                'size_sqm' => 100.00,
                'monthly_rent' => 2000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'MBP-RES-0002',
                'type' => 'residential',
                'title' => 'Section 12, Lot 6 - Top Town',
                'address' => 'Lot 6, Section 12, Top Town',
                'suburb' => 'Top Town',
                'city' => 'Lae',
                'province' => 'Morobe Province',
                'bedrooms' => 2,
                'size_sqm' => 75.00,
                'monthly_rent' => 1400.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'MBP-RES-0003',
                'type' => 'residential',
                'title' => 'Section 5, Lot 9 - Eriku',
                'address' => 'Lot 9, Section 5, Eriku',
                'suburb' => 'Eriku',
                'city' => 'Lae',
                'province' => 'Morobe Province',
                'bedrooms' => 4,
                'size_sqm' => 135.00,
                'monthly_rent' => 2500.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'MBP-RES-0004',
                'type' => 'residential',
                'title' => 'Section 3, Lot 18 - Top Town',
                'address' => 'Lot 18, Section 3, Top Town',
                'suburb' => 'Top Town',
                'city' => 'Lae',
                'province' => 'Morobe Province',
                'bedrooms' => 1,
                'size_sqm' => 50.00,
                'monthly_rent' => 800.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'MBP-INS-0001',
                'type' => 'institutional',
                'title' => 'NHC Regional Office - Lae',
                'address' => 'Lot 2, Section 1, Eriku',
                'suburb' => 'Eriku',
                'city' => 'Lae',
                'province' => 'Morobe Province',
                'bedrooms' => null,
                'size_sqm' => 280.00,
                'monthly_rent' => 8000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
        ];

        foreach ($mbpProperties as $prop) {
            $prop['region_id'] = $regions['MBP']->id;
            $properties[] = Property::create($prop);
        }

        // ── EHP: 4 properties (3 residential, 1 land) ──
        $ehpProperties = [
            [
                'property_code' => 'EHP-RES-0001',
                'type' => 'residential',
                'title' => 'Section 4, Lot 7 - North Goroka',
                'address' => 'Lot 7, Section 4, North Goroka',
                'suburb' => 'North Goroka',
                'city' => 'Goroka',
                'province' => 'Eastern Highlands Province',
                'bedrooms' => 3,
                'size_sqm' => 105.00,
                'monthly_rent' => 1800.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'EHP-RES-0002',
                'type' => 'residential',
                'title' => 'Section 9, Lot 2 - South Goroka',
                'address' => 'Lot 2, Section 9, South Goroka',
                'suburb' => 'South Goroka',
                'city' => 'Goroka',
                'province' => 'Eastern Highlands Province',
                'bedrooms' => 2,
                'size_sqm' => 80.00,
                'monthly_rent' => 1200.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'EHP-RES-0003',
                'type' => 'residential',
                'title' => 'Section 6, Lot 11 - North Goroka',
                'address' => 'Lot 11, Section 6, North Goroka',
                'suburb' => 'North Goroka',
                'city' => 'Goroka',
                'province' => 'Eastern Highlands Province',
                'bedrooms' => 4,
                'size_sqm' => 140.00,
                'monthly_rent' => 2000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'EHP-LND-0001',
                'type' => 'land',
                'title' => 'Vacant Land - South Goroka',
                'address' => 'Lot 30, Section 15, South Goroka',
                'suburb' => 'South Goroka',
                'city' => 'Goroka',
                'province' => 'Eastern Highlands Province',
                'bedrooms' => null,
                'size_sqm' => 500.00,
                'monthly_rent' => 1500.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
        ];

        foreach ($ehpProperties as $prop) {
            $prop['region_id'] = $regions['EHP']->id;
            $properties[] = Property::create($prop);
        }

        // ── MDP: 3 properties (2 residential, 1 institutional) ──
        $mdpProperties = [
            [
                'property_code' => 'MDP-RES-0001',
                'type' => 'residential',
                'title' => 'Section 8, Lot 4 - Town Area',
                'address' => 'Lot 4, Section 8, Town Area',
                'suburb' => 'Town Area',
                'city' => 'Madang',
                'province' => 'Madang Province',
                'bedrooms' => 3,
                'size_sqm' => 95.00,
                'monthly_rent' => 1800.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'MDP-RES-0002',
                'type' => 'residential',
                'title' => 'Section 2, Lot 15 - Newtown',
                'address' => 'Lot 15, Section 2, Newtown',
                'suburb' => 'Newtown',
                'city' => 'Madang',
                'province' => 'Madang Province',
                'bedrooms' => 1,
                'size_sqm' => 50.00,
                'monthly_rent' => 800.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
            [
                'property_code' => 'MDP-INS-0001',
                'type' => 'institutional',
                'title' => 'NHC District Office - Madang',
                'address' => 'Lot 1, Section 5, Town Area',
                'suburb' => 'Town Area',
                'city' => 'Madang',
                'province' => 'Madang Province',
                'bedrooms' => null,
                'size_sqm' => 200.00,
                'monthly_rent' => 6000.00,
                'payment_frequency' => 'monthly',
                'status' => 'available',
            ],
        ];

        foreach ($mdpProperties as $prop) {
            $prop['region_id'] = $regions['MDP']->id;
            $properties[] = Property::create($prop);
        }

        return $properties;
    }

    /**
     * Create tenancies, invoices, and payments for all tenant users.
     *
     * Distribution (16 tenants total):
     *  Index 0  = David Kila      : partial payment (demo showcase)
     *  Index 1-5  = Good standing  : all invoices fully paid
     *  Index 6-9  = Overdue        : 1-2 unpaid invoices
     *  Index 10-12 = In arrears    : 3+ unpaid invoices
     *  Index 13 = Steven Koi      : good standing + support ticket
     *  Index 14 = Agnes Wari      : good standing
     *  Index 15 = Paul Mek        : terminated lease (historical data)
     */
    private function createTenanciesAndBilling(
        array $tenantUsers,
        array $properties,
        array $staffUsers
    ): void {
        // Build combined list: featured client + 15 tenant users = 16 tenants
        $allTenants = array_merge([$staffUsers['featured_client']], $tenantUsers);

        // Properties index: 0-7 NCD, 8-12 MBP, 13-16 EHP, 17-19 MDP
        $propertyAssignments = [
            0  => 0,   // David Kila        -> NCD-RES-0001 (Boroko, PGK 2,800)
            1  => 1,   // Sarah Waim        -> NCD-RES-0002 (Hohola, PGK 1,800)
            2  => 2,   // Thomas Mondo      -> NCD-RES-0003 (Waigani, PGK 3,500)
            3  => 3,   // Grace Hombiri     -> NCD-RES-0004 (Korobosea, PGK 2,000)
            4  => 4,   // Michael Aku       -> NCD-RES-0005 (Gerehu, PGK 1,500)
            5  => 5,   // Rebecca Loi       -> NCD-RES-0006 (Boroko, PGK 1,200)
            6  => 6,   // John Opa          -> NCD-INS-0001 (Waigani, PGK 15,000)
            7  => 8,   // Martha Kaia       -> MBP-RES-0001 (Eriku, PGK 2,000)
            8  => 9,   // Simon Gure        -> MBP-RES-0002 (Top Town, PGK 1,400)
            9  => 10,  // Elizabeth Ruma     -> MBP-RES-0003 (Eriku, PGK 2,500)
            10 => 11,  // Anna Balo         -> MBP-RES-0004 (Top Town, PGK 800)
            11 => 13,  // Peter Nao         -> EHP-RES-0001 (N. Goroka, PGK 1,800)
            12 => 14,  // Ruth Siga         -> EHP-RES-0002 (S. Goroka, PGK 1,200)
            13 => 15,  // Steven Koi        -> EHP-RES-0003 (N. Goroka, PGK 2,000)
            14 => 17,  // Agnes Wari        -> MDP-RES-0001 (Town Area, PGK 1,800)
            15 => 18,  // Paul Mek          -> MDP-RES-0002 (Newtown, PGK 800)
        ];

        $scenarios = [
            0  => 'partial',          // David Kila — demo account, partial payment
            1  => 'in_good_standing', // Sarah Waim
            2  => 'in_good_standing', // Thomas Mondo
            3  => 'in_good_standing', // Grace Hombiri
            4  => 'in_good_standing', // Michael Aku
            5  => 'in_good_standing', // Rebecca Loi
            6  => 'overdue',          // John Opa
            7  => 'overdue',          // Martha Kaia
            8  => 'overdue',          // Simon Gure
            9  => 'overdue',          // Elizabeth Ruma
            10 => 'in_arrears',       // Anna Balo
            11 => 'in_arrears',       // Peter Nao
            12 => 'in_arrears',       // Ruth Siga
            13 => 'in_good_standing', // Steven Koi (has support ticket)
            14 => 'in_good_standing', // Agnes Wari
            15 => 'terminated',       // Paul Mek — terminated lease
        ];

        $invoiceCounter = 0;
        $paymentCounter = 0;

        // Determine which admin recorded payments based on property region
        $adminRecorders = [
            'NCD' => $staffUsers['admin_ncd']->id,
            'MBP' => $staffUsers['admin_morobe']->id,
            'EHP' => $staffUsers['admin_ncd']->id,       // No EHP admin, NCD covers
            'MDP' => $staffUsers['admin_morobe']->id,     // No MDP admin, Morobe covers
        ];

        foreach ($propertyAssignments as $tenantIdx => $propIdx) {
            $tenant = $allTenants[$tenantIdx];
            $property = $properties[$propIdx];
            $scenario = $scenarios[$tenantIdx];
            $regionCode = $property->region->code;
            $recordedBy = $adminRecorders[$regionCode];

            // Varied lease start dates (6-24 months ago)
            $monthsAgo = match ($scenario) {
                'terminated' => 18,
                'in_arrears' => rand(12, 20),
                'partial' => 8,
                default => rand(6, 16),
            };
            $leaseStart = Carbon::now()->subMonths($monthsAgo)->startOfMonth();
            $leaseEnd = $leaseStart->copy()->addYears(2);

            // Determine tenancy and tenant_status
            $tenancyStatus = $scenario === 'terminated' ? 'terminated' : 'active';
            $tenantStatus = match ($scenario) {
                'partial' => 'in_good_standing',
                'in_good_standing' => 'in_good_standing',
                'overdue' => 'overdue',
                'in_arrears' => 'in_arrears',
                'terminated' => 'in_good_standing',
            };

            // Terminated lease ended 3 months ago
            if ($scenario === 'terminated') {
                $leaseEnd = Carbon::now()->subMonths(3)->endOfMonth();
            }

            $tenancy = Tenancy::create([
                'tenant_id' => $tenant->id,
                'property_id' => $property->id,
                'lease_start' => $leaseStart,
                'lease_end' => $leaseEnd,
                'agreed_rent' => $property->monthly_rent,
                'payment_frequency' => 'monthly',
                'status' => $tenancyStatus,
                'tenant_status' => $tenantStatus,
                'notes' => $scenario === 'terminated'
                    ? 'Tenancy terminated. Tenant relocated to private rental.'
                    : null,
            ]);

            // Mark property status
            $property->update([
                'status' => $scenario === 'terminated' ? 'available' : 'occupied',
            ]);

            // Generate invoices and payments based on scenario
            switch ($scenario) {
                case 'in_good_standing':
                    $numInvoices = rand(4, 6);
                    for ($i = 0; $i < $numInvoices; $i++) {
                        $invoiceCounter++;
                        $billingStart = Carbon::now()->subMonths($numInvoices - $i)->startOfMonth();
                        $billingEnd = $billingStart->copy()->endOfMonth();
                        $dueDate = $billingStart->copy()->addDays(14);

                        $invoice = Invoice::create([
                            'tenancy_id' => $tenancy->id,
                            'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                            'billing_period_start' => $billingStart,
                            'billing_period_end' => $billingEnd,
                            'amount_due' => $property->monthly_rent,
                            'amount_paid' => $property->monthly_rent,
                            'balance' => 0,
                            'due_date' => $dueDate,
                            'status' => 'paid',
                            'issued_at' => $billingStart,
                        ]);

                        $paymentCounter++;
                        Payment::create([
                            'invoice_id' => $invoice->id,
                            'tenancy_id' => $tenancy->id,
                            'amount' => $property->monthly_rent,
                            'payment_method' => $this->randomPaymentMethod(),
                            'reference_number' => sprintf('PAY-2026-%04d', $paymentCounter),
                            'status' => 'completed',
                            'paid_at' => $dueDate->copy()->subDays(rand(0, 5)),
                            'recorded_by' => $recordedBy,
                        ]);
                    }
                    break;

                case 'overdue':
                    $numInvoices = rand(4, 5);
                    $unpaidCount = rand(1, 2);
                    for ($i = 0; $i < $numInvoices; $i++) {
                        $invoiceCounter++;
                        $billingStart = Carbon::now()->subMonths($numInvoices - $i)->startOfMonth();
                        $billingEnd = $billingStart->copy()->endOfMonth();
                        $dueDate = $billingStart->copy()->addDays(14);
                        $isUnpaid = $i >= ($numInvoices - $unpaidCount);

                        $invoice = Invoice::create([
                            'tenancy_id' => $tenancy->id,
                            'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                            'billing_period_start' => $billingStart,
                            'billing_period_end' => $billingEnd,
                            'amount_due' => $property->monthly_rent,
                            'amount_paid' => $isUnpaid ? 0 : $property->monthly_rent,
                            'balance' => $isUnpaid ? $property->monthly_rent : 0,
                            'due_date' => $dueDate,
                            'status' => $isUnpaid ? 'overdue' : 'paid',
                            'issued_at' => $billingStart,
                        ]);

                        if (! $isUnpaid) {
                            $paymentCounter++;
                            Payment::create([
                                'invoice_id' => $invoice->id,
                                'tenancy_id' => $tenancy->id,
                                'amount' => $property->monthly_rent,
                                'payment_method' => $this->randomPaymentMethod(),
                                'reference_number' => sprintf('PAY-2026-%04d', $paymentCounter),
                                'status' => 'completed',
                                'paid_at' => $dueDate->copy()->subDays(rand(0, 3)),
                                'recorded_by' => $recordedBy,
                            ]);
                        }
                    }
                    break;

                case 'in_arrears':
                    $numInvoices = rand(5, 7);
                    $paidCount = rand(1, 2);
                    for ($i = 0; $i < $numInvoices; $i++) {
                        $invoiceCounter++;
                        $billingStart = Carbon::now()->subMonths($numInvoices - $i)->startOfMonth();
                        $billingEnd = $billingStart->copy()->endOfMonth();
                        $dueDate = $billingStart->copy()->addDays(14);
                        $isPaid = $i < $paidCount;

                        $invoice = Invoice::create([
                            'tenancy_id' => $tenancy->id,
                            'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                            'billing_period_start' => $billingStart,
                            'billing_period_end' => $billingEnd,
                            'amount_due' => $property->monthly_rent,
                            'amount_paid' => $isPaid ? $property->monthly_rent : 0,
                            'balance' => $isPaid ? 0 : $property->monthly_rent,
                            'due_date' => $dueDate,
                            'status' => $isPaid ? 'paid' : 'overdue',
                            'issued_at' => $billingStart,
                        ]);

                        if ($isPaid) {
                            $paymentCounter++;
                            Payment::create([
                                'invoice_id' => $invoice->id,
                                'tenancy_id' => $tenancy->id,
                                'amount' => $property->monthly_rent,
                                'payment_method' => $this->randomPaymentMethod(),
                                'reference_number' => sprintf('PAY-2026-%04d', $paymentCounter),
                                'status' => 'completed',
                                'paid_at' => $dueDate->copy()->subDays(rand(0, 5)),
                                'recorded_by' => $recordedBy,
                            ]);
                        }
                    }
                    break;

                case 'partial':
                    // David Kila: 6 months of invoices — 4 paid, 1 partially paid, 1 current (sent)
                    for ($i = 0; $i < 6; $i++) {
                        $invoiceCounter++;
                        $billingStart = Carbon::now()->subMonths(6 - $i)->startOfMonth();
                        $billingEnd = $billingStart->copy()->endOfMonth();
                        $dueDate = $billingStart->copy()->addDays(14);

                        if ($i < 4) {
                            // Fully paid invoices
                            $invoice = Invoice::create([
                                'tenancy_id' => $tenancy->id,
                                'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                                'billing_period_start' => $billingStart,
                                'billing_period_end' => $billingEnd,
                                'amount_due' => $property->monthly_rent,
                                'amount_paid' => $property->monthly_rent,
                                'balance' => 0,
                                'due_date' => $dueDate,
                                'status' => 'paid',
                                'issued_at' => $billingStart,
                            ]);

                            $paymentCounter++;
                            $methods = ['bank_transfer', 'bank_transfer', 'cash', 'online_gateway'];
                            Payment::create([
                                'invoice_id' => $invoice->id,
                                'tenancy_id' => $tenancy->id,
                                'amount' => $property->monthly_rent,
                                'payment_method' => $methods[$i],
                                'reference_number' => sprintf('PAY-2026-%04d', $paymentCounter),
                                'gateway_transaction_id' => $methods[$i] === 'online_gateway'
                                    ? 'GW-' . strtoupper(substr(md5(rand()), 0, 12))
                                    : null,
                                'status' => 'completed',
                                'paid_at' => $dueDate->copy()->subDays(rand(1, 5)),
                                'recorded_by' => $recordedBy,
                            ]);
                        } elseif ($i === 4) {
                            // Partially paid invoice
                            $partialAmount = round($property->monthly_rent * 0.5, 2);
                            $remainingBalance = $property->monthly_rent - $partialAmount;

                            $invoice = Invoice::create([
                                'tenancy_id' => $tenancy->id,
                                'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                                'billing_period_start' => $billingStart,
                                'billing_period_end' => $billingEnd,
                                'amount_due' => $property->monthly_rent,
                                'amount_paid' => $partialAmount,
                                'balance' => $remainingBalance,
                                'due_date' => $dueDate,
                                'status' => 'partially_paid',
                                'issued_at' => $billingStart,
                            ]);

                            $paymentCounter++;
                            Payment::create([
                                'invoice_id' => $invoice->id,
                                'tenancy_id' => $tenancy->id,
                                'amount' => $partialAmount,
                                'payment_method' => 'online_gateway',
                                'gateway_transaction_id' => 'GW-' . strtoupper(substr(md5(rand()), 0, 12)),
                                'reference_number' => sprintf('PAY-2026-%04d', $paymentCounter),
                                'status' => 'completed',
                                'paid_at' => $dueDate->copy()->addDays(1),
                                'recorded_by' => $recordedBy,
                            ]);
                        } else {
                            // Current month — sent, not yet paid
                            Invoice::create([
                                'tenancy_id' => $tenancy->id,
                                'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                                'billing_period_start' => $billingStart,
                                'billing_period_end' => $billingEnd,
                                'amount_due' => $property->monthly_rent,
                                'amount_paid' => 0,
                                'balance' => $property->monthly_rent,
                                'due_date' => $dueDate,
                                'status' => 'sent',
                                'issued_at' => $billingStart,
                            ]);
                        }
                    }
                    break;

                case 'terminated':
                    // Paul Mek: terminated lease — 6 months of paid history, then stopped
                    $terminatedMonths = 6;
                    for ($i = 0; $i < $terminatedMonths; $i++) {
                        $invoiceCounter++;
                        $billingStart = $leaseStart->copy()->addMonths($i);
                        $billingEnd = $billingStart->copy()->endOfMonth();
                        $dueDate = $billingStart->copy()->addDays(14);

                        $invoice = Invoice::create([
                            'tenancy_id' => $tenancy->id,
                            'invoice_number' => sprintf('INV-2026-%s-%06d', $regionCode, $invoiceCounter),
                            'billing_period_start' => $billingStart,
                            'billing_period_end' => $billingEnd,
                            'amount_due' => $property->monthly_rent,
                            'amount_paid' => $property->monthly_rent,
                            'balance' => 0,
                            'due_date' => $dueDate,
                            'status' => 'paid',
                            'issued_at' => $billingStart,
                        ]);

                        $paymentCounter++;
                        Payment::create([
                            'invoice_id' => $invoice->id,
                            'tenancy_id' => $tenancy->id,
                            'amount' => $property->monthly_rent,
                            'payment_method' => $this->randomPaymentMethod(),
                            'reference_number' => sprintf('PAY-2026-%04d', $paymentCounter),
                            'status' => 'completed',
                            'paid_at' => $dueDate->copy()->subDays(rand(0, 4)),
                            'recorded_by' => $recordedBy,
                        ]);
                    }
                    break;
            }
        }
    }

    /**
     * Create support tickets with threaded comments.
     */
    private function createSupportTickets(array $tenantUsers, array $staffUsers): void
    {
        $davidKila = $staffUsers['featured_client'];
        $adminNcd = $staffUsers['admin_ncd'];

        // Get David Kila's tenancy for the ticket
        $davidTenancy = Tenancy::where('tenant_id', $davidKila->id)->first();

        // Ticket 1: David Kila - Plumbing issue (open)
        $ticket1 = SupportTicket::create([
            'tenancy_id' => $davidTenancy->id,
            'submitted_by' => $davidKila->id,
            'ticket_number' => 'TKT-2026-000001',
            'category' => 'plumbing',
            'subject' => 'Leaking kitchen tap',
            'description' => 'The kitchen tap has been leaking steadily for the past week. Water drips continuously even when the tap is fully closed. The leak is getting worse and water is pooling under the sink cabinet. Please send a plumber to inspect and repair.',
            'priority' => 'medium',
            'status' => 'open',
            'assigned_to' => null,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket1->id,
            'user_id' => $davidKila->id,
            'body' => 'I have placed a bucket under the sink to catch the water for now, but it fills up within a few hours. This needs attention soon before it causes water damage to the cabinet.',
            'is_internal' => false,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket1->id,
            'user_id' => $adminNcd->id,
            'body' => 'Thank you for reporting this, Mr. Kila. We have noted your request and will arrange for a plumber to visit your property within the next 2-3 business days. We will contact you to confirm the appointment time.',
            'is_internal' => false,
        ]);

        // Internal admin note (not visible to tenant)
        TicketComment::create([
            'ticket_id' => $ticket1->id,
            'user_id' => $adminNcd->id,
            'body' => 'Contacted PNG Plumbing Services (Ph: 325 4567). They can schedule for Thursday. Estimated cost PGK 150-300 for tap replacement.',
            'is_internal' => true,
        ]);

        // Ticket 2: Steven Koi - Electrical issue (in_progress, assigned)
        $stevenKoi = $tenantUsers[12]; // Steven Koi is at index 12
        $stevenTenancy = Tenancy::where('tenant_id', $stevenKoi->id)->first();

        $ticket2 = SupportTicket::create([
            'tenancy_id' => $stevenTenancy->id,
            'submitted_by' => $stevenKoi->id,
            'ticket_number' => 'TKT-2026-000002',
            'category' => 'electrical',
            'subject' => 'Power outlet not working in bedroom',
            'description' => 'The double power outlet on the east wall of the main bedroom has stopped working completely. No power comes through when I plug in any appliance. Other outlets in the house are working fine. I suspect there may be a wiring issue behind the wall.',
            'priority' => 'high',
            'status' => 'in_progress',
            'assigned_to' => $adminNcd->id,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket2->id,
            'user_id' => $adminNcd->id,
            'body' => 'We have assigned an electrician to inspect this issue. He is scheduled to visit on Thursday between 9am and 12pm. Please ensure someone is home to provide access. For safety, please do not attempt any repairs yourself.',
            'is_internal' => false,
        ]);

        // Ticket 3: David Kila - General enquiry (resolved)
        $ticket3 = SupportTicket::create([
            'tenancy_id' => $davidTenancy->id,
            'submitted_by' => $davidKila->id,
            'ticket_number' => 'TKT-2026-000003',
            'category' => 'general_query',
            'subject' => 'Request for rent payment history letter',
            'description' => 'I need a formal letter confirming my rent payment history for the past 12 months. This is required for a bank loan application. Could you please provide this at your earliest convenience?',
            'priority' => 'low',
            'status' => 'resolved',
            'assigned_to' => $adminNcd->id,
            'resolved_at' => Carbon::now()->subDays(5),
        ]);

        TicketComment::create([
            'ticket_id' => $ticket3->id,
            'user_id' => $adminNcd->id,
            'body' => 'Good day Mr. Kila. Your payment history letter has been prepared and is ready for collection at the NHC office in Waigani. Please bring your ID for verification. Office hours are 8am to 4pm, Monday to Friday.',
            'is_internal' => false,
        ]);

        TicketComment::create([
            'ticket_id' => $ticket3->id,
            'user_id' => $davidKila->id,
            'body' => 'Thank you very much, I will come and collect it tomorrow morning.',
            'is_internal' => false,
        ]);
    }

    /**
     * Return a random payment method with realistic distribution.
     */
    private function randomPaymentMethod(): string
    {
        // Weighted distribution: bank transfer most common, then cash, then online
        $methods = [
            'bank_transfer', 'bank_transfer', 'bank_transfer',
            'cash', 'cash',
            'online_gateway',
        ];

        return $methods[array_rand($methods)];
    }
}
