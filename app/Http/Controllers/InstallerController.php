<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InstallerController extends Controller
{
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return Inertia::render('Installer/Index', [
            'requirements' => $this->checkRequirements(),
            'isInstalled' => false,
        ]);
    }

    public function checkRequirementsApi()
    {
        return response()->json([
            'requirements' => $this->checkRequirements(),
        ]);
    }

    public function saveEnvironment(Request $request)
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:100'],
            'app_url' => ['required', 'url', 'max:255'],
            'app_locale' => ['required', 'in:de,en'],
            'db_connection' => ['required', 'in:mysql,pgsql'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'string', 'max:10'],
            'db_database' => ['required', 'string', 'max:255'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
        ]);

        $envPath = base_path('.env');
        $envContent = File::exists($envPath) ? File::get($envPath) : File::get(base_path('.env.example'));

        $replacements = [
            'APP_NAME' => $validated['app_name'],
            'APP_URL' => $validated['app_url'],
            'APP_LOCALE' => $validated['app_locale'],
            'APP_ENV' => 'production',
            'APP_DEBUG' => 'false',
            'DB_CONNECTION' => $validated['db_connection'],
            'DB_HOST' => $validated['db_host'],
            'DB_PORT' => $validated['db_port'],
            'DB_DATABASE' => $validated['db_database'],
            'DB_USERNAME' => $validated['db_username'],
            'DB_PASSWORD' => $validated['db_password'] ?? '',
            'CACHE_STORE' => 'file',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'file',
        ];

        foreach ($replacements as $key => $value) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}=\"{$value}\"",
                $envContent,
                1,
                $count
            );
            if ($count === 0) {
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        File::put($envPath, $envContent);

        // Reload config
        Artisan::call('config:clear');

        return response()->json(['success' => true]);
    }

    public function testDatabase(Request $request)
    {
        $validated = $request->validate([
            'db_connection' => ['required', 'in:mysql,pgsql'],
            'db_host' => ['required', 'string'],
            'db_port' => ['required', 'string'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        try {
            $dsn = $validated['db_connection'] === 'mysql'
                ? "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']}"
                : "pgsql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_database']}";

            $pdo = new \PDO(
                $dsn,
                $validated['db_username'],
                $validated['db_password'] ?? '',
                [\PDO::ATTR_TIMEOUT => 5]
            );

            return response()->json(['success' => true, 'message' => 'Connection successful']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function runMigrations()
    {
        try {
            // Generate app key if not set
            if (empty(config('app.key')) || config('app.key') === 'base64:') {
                Artisan::call('key:generate', ['--force' => true]);
            }

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);
            Artisan::call('storage:link', ['--force' => true]);

            return response()->json(['success' => true, 'output' => Artisan::output()]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function createAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'email_verified_at' => now(),
                'locale' => config('app.locale', 'de'),
            ]);

            $user->assignRole('super_admin');

            // Mark as installed
            File::put(storage_path('installed'), now()->toIso8601String());

            // Clear and cache config
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            return response()->json(['success' => true, 'email' => $user->email]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    private function checkRequirements(): array
    {
        $checks = [];

        // PHP version
        $phpVersion = PHP_VERSION;
        $checks[] = [
            'name' => 'PHP Version',
            'required' => '>= 8.2',
            'current' => $phpVersion,
            'passed' => version_compare($phpVersion, '8.2.0', '>='),
        ];

        // Extensions
        $extensions = [
            'pdo' => 'PDO',
            'mbstring' => 'Mbstring',
            'openssl' => 'OpenSSL',
            'tokenizer' => 'Tokenizer',
            'xml' => 'XML',
            'ctype' => 'Ctype',
            'json' => 'JSON',
            'bcmath' => 'BCMath',
            'curl' => 'cURL',
            'fileinfo' => 'Fileinfo',
            'dom' => 'DOM',
        ];

        foreach ($extensions as $ext => $name) {
            $checks[] = [
                'name' => "PHP {$name}",
                'required' => 'Enabled',
                'current' => extension_loaded($ext) ? 'Enabled' : 'Missing',
                'passed' => extension_loaded($ext),
            ];
        }

        // DB drivers
        $checks[] = [
            'name' => 'MySQL Driver',
            'required' => 'Recommended',
            'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Missing',
            'passed' => extension_loaded('pdo_mysql'),
            'optional' => true,
        ];

        $checks[] = [
            'name' => 'PostgreSQL Driver',
            'required' => 'Recommended',
            'current' => extension_loaded('pdo_pgsql') ? 'Enabled' : 'Missing',
            'passed' => extension_loaded('pdo_pgsql'),
            'optional' => true,
        ];

        // Writable directories
        $dirs = ['storage', 'bootstrap/cache'];
        foreach ($dirs as $dir) {
            $path = base_path($dir);
            $checks[] = [
                'name' => "{$dir}/ writable",
                'required' => 'Writable',
                'current' => is_writable($path) ? 'Writable' : 'Not writable',
                'passed' => is_writable($path),
            ];
        }

        return $checks;
    }
}
