<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Department;
use App\Models\KpiDefinition;
use App\Models\KpiValue;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
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

        foreach ($kpiTemplates as $tpl) {
            KpiDefinition::create(array_merge($tpl, ['is_template' => true]));
        }

        // Create department-assigned KPIs with time-series data
        $departmentKpis = [
            // Fertigung KPIs
            [
                'dept' => $fertigung, 'company' => $dentexProd,
                'name_de' => 'Fehlerquote', 'name_en' => 'Error Rate',
                'description_de' => 'Anteil fehlerhafter Produkte', 'description_en' => 'Defective product rate',
                'unit' => '%', 'target_value' => 5.00, 'warning_threshold' => 4.00, 'critical_threshold' => 6.00,
                'direction' => 'lower_better', 'category' => 'Manufacturing', 'frequency' => 'daily',
                'values' => fn () => $this->generateTimeSeries(30, 3.0, 6.5, 'lower_better', 5.0, 4.0, 6.0),
            ],
            [
                'dept' => $fertigung, 'company' => $dentexProd,
                'name_de' => 'OEE', 'name_en' => 'OEE',
                'description_de' => 'Gesamtanlageneffektivität', 'description_en' => 'Overall Equipment Effectiveness',
                'unit' => '%', 'target_value' => 85.00, 'warning_threshold' => 75.00, 'critical_threshold' => 65.00,
                'direction' => 'higher_better', 'category' => 'Manufacturing', 'frequency' => 'daily',
                'values' => fn () => $this->generateTimeSeries(30, 60.0, 92.0, 'higher_better', 85.0, 75.0, 65.0),
            ],
            [
                'dept' => $fertigung, 'company' => $dentexProd,
                'name_de' => 'Durchlaufzeit', 'name_en' => 'Processing Time',
                'description_de' => 'Durchschnittliche Bearbeitungszeit', 'description_en' => 'Average processing time',
                'unit' => 'h', 'target_value' => 3.00, 'warning_threshold' => 3.50, 'critical_threshold' => 4.50,
                'direction' => 'lower_better', 'category' => 'Manufacturing', 'frequency' => 'daily',
                'values' => fn () => $this->generateTimeSeries(30, 2.0, 5.0, 'lower_better', 3.0, 3.5, 4.5),
            ],
            // Qualitätskontrolle KPIs
            [
                'dept' => $qualitaet, 'company' => $dentexProd,
                'name_de' => 'Prüfquote', 'name_en' => 'Inspection Rate',
                'description_de' => 'Anteil geprüfter Produkte', 'description_en' => 'Percentage of inspected products',
                'unit' => '%', 'target_value' => 100.00, 'warning_threshold' => 95.00, 'critical_threshold' => 90.00,
                'direction' => 'higher_better', 'category' => 'Quality', 'frequency' => 'daily',
                'values' => fn () => $this->generateTimeSeries(30, 88.0, 100.0, 'higher_better', 100.0, 95.0, 90.0),
            ],
            // Logistik KPIs
            [
                'dept' => $logistik, 'company' => $dentexProd,
                'name_de' => 'Liefertreue', 'name_en' => 'On-Time Delivery',
                'description_de' => 'Anteil pünktlich gelieferter Aufträge', 'description_en' => 'Percentage of orders delivered on time',
                'unit' => '%', 'target_value' => 95.00, 'warning_threshold' => 90.00, 'critical_threshold' => 85.00,
                'direction' => 'higher_better', 'category' => 'Logistics', 'frequency' => 'weekly',
                'values' => fn () => $this->generateTimeSeries(12, 82.0, 98.0, 'higher_better', 95.0, 90.0, 85.0),
            ],
            // Innendienst KPIs
            [
                'dept' => $vertriebInnen, 'company' => $dentexVertrieb,
                'name_de' => 'Umsatz', 'name_en' => 'Revenue',
                'description_de' => 'Monatlicher Umsatz', 'description_en' => 'Monthly revenue',
                'unit' => 'EUR', 'target_value' => 150000.00, 'warning_threshold' => 120000.00, 'critical_threshold' => 100000.00,
                'direction' => 'higher_better', 'category' => 'Sales', 'frequency' => 'monthly',
                'values' => fn () => $this->generateTimeSeries(6, 90000.0, 180000.0, 'higher_better', 150000.0, 120000.0, 100000.0),
            ],
            [
                'dept' => $vertriebInnen, 'company' => $dentexVertrieb,
                'name_de' => 'Kundenzufriedenheit', 'name_en' => 'Customer Satisfaction',
                'description_de' => 'Kundenzufriedenheitsindex', 'description_en' => 'Customer satisfaction index',
                'unit' => '%', 'target_value' => 90.00, 'warning_threshold' => 80.00, 'critical_threshold' => 70.00,
                'direction' => 'higher_better', 'category' => 'Sales', 'frequency' => 'monthly',
                'values' => fn () => $this->generateTimeSeries(6, 65.0, 95.0, 'higher_better', 90.0, 80.0, 70.0),
            ],
            // Außendienst KPIs
            [
                'dept' => $vertriebAussen, 'company' => $dentexVertrieb,
                'name_de' => 'Conversion Rate', 'name_en' => 'Conversion Rate',
                'description_de' => 'Anteil abgeschlossener Deals', 'description_en' => 'Percentage of closed deals',
                'unit' => '%', 'target_value' => 25.00, 'warning_threshold' => 18.00, 'critical_threshold' => 12.00,
                'direction' => 'higher_better', 'category' => 'Sales', 'frequency' => 'monthly',
                'values' => fn () => $this->generateTimeSeries(6, 10.0, 32.0, 'higher_better', 25.0, 18.0, 12.0),
            ],
            // Marketing KPIs
            [
                'dept' => $marketing, 'company' => $dentexVertrieb,
                'name_de' => 'Kampagnen-ROI', 'name_en' => 'Campaign ROI',
                'description_de' => 'Return on Investment für Marketingkampagnen', 'description_en' => 'Return on investment for marketing campaigns',
                'unit' => '%', 'target_value' => 200.00, 'warning_threshold' => 150.00, 'critical_threshold' => 100.00,
                'direction' => 'higher_better', 'category' => 'Marketing', 'frequency' => 'monthly',
                'values' => fn () => $this->generateTimeSeries(6, 80.0, 280.0, 'higher_better', 200.0, 150.0, 100.0),
            ],
        ];

        foreach ($departmentKpis as $kpiDef) {
            $dept = $kpiDef['dept'];
            $company = $kpiDef['company'];
            $valuesGenerator = $kpiDef['values'];
            unset($kpiDef['values'], $kpiDef['dept'], $kpiDef['company']);

            $kpi = KpiDefinition::create(array_merge($kpiDef, [
                'department_id' => $dept->id,
                'company_id' => $company->id,
            ]));

            foreach ($valuesGenerator() as $val) {
                KpiValue::create(array_merge($val, [
                    'kpi_definition_id' => $kpi->id,
                    'recorded_by' => $admin->id,
                ]));
            }
        }
    }

    private function generateTimeSeries(int $count, float $min, float $max, string $direction, float $target, float $warn, float $crit): array
    {
        $values = [];
        $current = $min + ($max - $min) * 0.5;

        for ($i = $count - 1; $i >= 0; $i--) {
            $drift = (mt_rand(-20, 20) / 100.0) * ($max - $min) * 0.1;
            $current = max($min, min($max, $current + $drift));

            // Determine status
            if ($direction === 'higher_better') {
                $status = $current >= $target ? 'on_target' : ($current >= $warn ? 'on_target' : ($current >= $crit ? 'warning' : 'critical'));
            } else {
                $status = $current <= $target ? 'on_target' : ($current <= $warn ? 'on_target' : ($current <= $crit ? 'warning' : 'critical'));
            }

            $values[] = [
                'value' => round($current, 2),
                'recorded_at' => Carbon::now()->subDays($i)->format('Y-m-d'),
                'status' => $status,
            ];
        }

        return $values;
    }
}
