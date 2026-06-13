<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\KpiDefinition;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $roles = ['super_admin', 'holding_admin', 'company_admin', 'dept_manager', 'employee'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create demo tenant (Dentex Holding)
        $tenant = Tenant::create([
            'id' => 'dentex',
            'name' => 'Dentex Holding GmbH',
        ]);

        // Create companies
        $dentexProd = Company::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dentex Produktion GmbH',
            'description' => 'Herstellung von Zahnpflegeprodukten',
            'industry' => 'Manufacturing',
            'size' => '50-200',
            'timezone' => 'Europe/Berlin',
        ]);

        $dentexVertrieb = Company::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dentex Vertrieb GmbH',
            'description' => 'Vertrieb und Marketing',
            'industry' => 'Sales & Distribution',
            'size' => '20-50',
            'timezone' => 'Europe/Berlin',
        ]);

        // Create departments for Dentex Produktion
        $fertigung = Department::create([
            'company_id' => $dentexProd->id,
            'name' => 'Fertigung',
            'description' => 'Produktionslinie für Zahnpflegeprodukte',
            'industry_type' => 'Manufacturing',
        ]);

        $qualitaet = Department::create([
            'company_id' => $dentexProd->id,
            'name' => 'Qualitätskontrolle',
            'description' => 'Qualitätssicherung und Prüfung',
            'industry_type' => 'Quality',
        ]);

        $logistik = Department::create([
            'company_id' => $dentexProd->id,
            'name' => 'Logistik',
            'description' => 'Lager und Versand',
            'industry_type' => 'Logistics',
        ]);

        // Create departments for Dentex Vertrieb
        $vertriebInnen = Department::create([
            'company_id' => $dentexVertrieb->id,
            'name' => 'Innendienst',
            'description' => 'Kundenbetreuung und Auftragsabwicklung',
            'industry_type' => 'Sales',
        ]);

        $vertriebAussen = Department::create([
            'company_id' => $dentexVertrieb->id,
            'name' => 'Außendienst',
            'description' => 'Kundenakquise und Bestandskundenpflege',
            'industry_type' => 'Sales',
        ]);

        $marketing = Department::create([
            'company_id' => $dentexVertrieb->id,
            'name' => 'Marketing',
            'description' => 'Marketingkampagnen und Markenführung',
            'industry_type' => 'Marketing',
        ]);

        // Create demo users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@smartkpi.com',
            'password' => Hash::make('password'),
            'locale' => 'de',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('super_admin');

        $holdingManager = User::create([
            'name' => 'Hans Müller',
            'email' => 'mueller@dentex.de',
            'password' => Hash::make('password'),
            'locale' => 'de',
            'email_verified_at' => now(),
        ]);
        $holdingManager->assignRole('holding_admin');

        $companyAdmin = User::create([
            'name' => 'Petra Schmidt',
            'email' => 'schmidt@dentex.de',
            'password' => Hash::make('password'),
            'locale' => 'de',
            'department_id' => $fertigung->id,
            'email_verified_at' => now(),
        ]);
        $companyAdmin->assignRole('company_admin');

        $deptManager = User::create([
            'name' => 'Thomas Weber',
            'email' => 'weber@dentex.de',
            'password' => Hash::make('password'),
            'locale' => 'en',
            'department_id' => $qualitaet->id,
            'email_verified_at' => now(),
        ]);
        $deptManager->assignRole('dept_manager');

        // Create KPI Template Library (bilingual)
        $kpiTemplates = [
            ['name_de' => 'Fehlerquote', 'name_en' => 'Error Rate', 'description_de' => 'Anteil fehlerhafter Produkte an der Gesamtproduktion', 'description_en' => 'Percentage of defective products in total production', 'unit' => '%', 'target_value' => 5.00, 'warning_threshold' => 4.00, 'critical_threshold' => 6.00, 'direction' => 'lower_better', 'category' => 'Manufacturing', 'frequency' => 'daily'],
            ['name_de' => 'Umsatz', 'name_en' => 'Revenue', 'description_de' => 'Gesamtumsatz in Euro', 'description_en' => 'Total revenue in EUR', 'unit' => 'EUR', 'target_value' => 150000.00, 'warning_threshold' => 120000.00, 'critical_threshold' => 100000.00, 'direction' => 'higher_better', 'category' => 'Sales', 'frequency' => 'monthly'],
            ['name_de' => 'Durchlaufzeit', 'name_en' => 'Processing Time', 'description_de' => 'Durchschnittliche Bearbeitungszeit pro Auftrag', 'description_en' => 'Average processing time per order', 'unit' => 'h', 'target_value' => 3.00, 'warning_threshold' => 3.50, 'critical_threshold' => 4.50, 'direction' => 'lower_better', 'category' => 'Manufacturing', 'frequency' => 'daily'],
            ['name_de' => 'Kundenzufriedenheit', 'name_en' => 'Customer Satisfaction', 'description_de' => 'Kundenzufriedenheitsindex auf Basis von Umfragen', 'description_en' => 'Customer satisfaction index based on surveys', 'unit' => '%', 'target_value' => 90.00, 'warning_threshold' => 80.00, 'critical_threshold' => 70.00, 'direction' => 'higher_better', 'category' => 'Sales', 'frequency' => 'monthly'],
            ['name_de' => 'Fluktuation', 'name_en' => 'Turnover', 'description_de' => 'Mitarbeiterfluktuation pro Quartal', 'description_en' => 'Employee turnover per quarter', 'unit' => '%', 'target_value' => 5.00, 'warning_threshold' => 8.00, 'critical_threshold' => 12.00, 'direction' => 'lower_better', 'category' => 'HR', 'frequency' => 'quarterly'],
            ['name_de' => 'Gewinnmarge', 'name_en' => 'Profit Margin', 'description_de' => 'Netto-Gewinnmarge in Prozent', 'description_en' => 'Net profit margin in percent', 'unit' => '%', 'target_value' => 15.00, 'warning_threshold' => 10.00, 'critical_threshold' => 5.00, 'direction' => 'higher_better', 'category' => 'Finance', 'frequency' => 'monthly'],
            ['name_de' => 'OEE', 'name_en' => 'OEE', 'description_de' => 'Overall Equipment Effectiveness — Gesamtanlageneffektivität', 'description_en' => 'Overall Equipment Effectiveness', 'unit' => '%', 'target_value' => 85.00, 'warning_threshold' => 75.00, 'critical_threshold' => 65.00, 'direction' => 'higher_better', 'category' => 'Manufacturing', 'frequency' => 'daily'],
            ['name_de' => 'Liefertreue', 'name_en' => 'On-Time Delivery', 'description_de' => 'Anteil pünktlich gelieferter Aufträge', 'description_en' => 'Percentage of orders delivered on time', 'unit' => '%', 'target_value' => 95.00, 'warning_threshold' => 90.00, 'critical_threshold' => 85.00, 'direction' => 'higher_better', 'category' => 'Logistics', 'frequency' => 'weekly'],
        ];

        // Create templates
        foreach ($kpiTemplates as $tpl) {
            KpiDefinition::create(array_merge($tpl, ['is_template' => true]));
        }

        // Assign some KPIs to departments
        KpiDefinition::create([
            'department_id' => $fertigung->id,
            'company_id' => $dentexProd->id,
            'name_de' => 'Fehlerquote',
            'name_en' => 'Error Rate',
            'description_de' => 'Anteil fehlerhafter Produkte',
            'description_en' => 'Defective product rate',
            'unit' => '%',
            'target_value' => 5.00,
            'warning_threshold' => 4.00,
            'critical_threshold' => 6.00,
            'direction' => 'lower_better',
            'category' => 'Manufacturing',
            'frequency' => 'daily',
        ]);

        KpiDefinition::create([
            'department_id' => $vertriebInnen->id,
            'company_id' => $dentexVertrieb->id,
            'name_de' => 'Umsatz',
            'name_en' => 'Revenue',
            'description_de' => 'Monatlicher Umsatz',
            'description_en' => 'Monthly revenue',
            'unit' => 'EUR',
            'target_value' => 150000.00,
            'warning_threshold' => 120000.00,
            'critical_threshold' => 100000.00,
            'direction' => 'higher_better',
            'category' => 'Sales',
            'frequency' => 'monthly',
        ]);
    }
}
