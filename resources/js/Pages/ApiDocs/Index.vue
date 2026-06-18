<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
      <div class="max-w-6xl mx-auto flex items-center justify-between">
        <a href="/" class="text-xl font-bold text-primary-600">SmartKPI API</a>
        <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">v1</span>
      </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-12 flex flex-col lg:flex-row gap-8">
      <!-- Sidebar -->
      <div class="lg:w-56 shrink-0">
        <div class="sticky top-20 space-y-1">
          <a v-for="(s, i) in sections" :key="i" :href="'#' + s.id"
            class="block px-3 py-2 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">
            {{ s.title }}
          </a>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 space-y-12">
        <section>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ $t('api_docs.title') }}</h1>
          <p class="text-gray-600 dark:text-gray-400">{{ $t('api_docs.intro') }}</p>
          <div class="mt-4 p-4 bg-gray-800 rounded-lg text-sm font-mono text-green-400">
            Base URL: <span class="text-white">https://your-domain.com/api/v1</span>
          </div>
        </section>

        <!-- Auth -->
        <section id="auth">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Authentication</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">All API requests require a Bearer token via Laravel Sanctum.</p>
          <div class="p-4 bg-gray-800 rounded-lg text-sm font-mono text-gray-300 overflow-x-auto">
            <span class="text-blue-400">Authorization</span>: Bearer <span class="text-yellow-400">&lt;your-api-token&gt;</span>
          </div>
        </section>

        <!-- Endpoints -->
        <section v-for="ep in endpoints" :key="ep.id" :id="ep.id" class="border-t border-gray-200 dark:border-gray-700 pt-8">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ ep.title }}</h2>
          <div v-for="(route, j) in ep.routes" :key="j" class="mb-6 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-100 dark:border-gray-700">
              <span class="px-2 py-0.5 text-xs font-mono font-bold rounded"
                :class="route.method === 'GET' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'">
                {{ route.method }}
              </span>
              <code class="text-sm text-gray-700 dark:text-gray-300">{{ route.path }}</code>
            </div>
            <div class="px-4 py-3">
              <p class="text-sm text-gray-600 dark:text-gray-400">{{ route.desc }}</p>
              <div v-if="route.params" class="mt-3">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Parameters</p>
                <table class="w-full text-sm">
                  <tr v-for="(p, k) in route.params" :key="k" class="border-b border-gray-100 dark:border-gray-700/50">
                    <td class="py-1.5 font-mono text-xs text-gray-700 dark:text-gray-300 w-32">{{ p.name }}</td>
                    <td class="py-1.5 text-xs text-gray-400">{{ p.type }}</td>
                    <td class="py-1.5 text-xs text-gray-500">{{ p.desc }}</td>
                  </tr>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
const sections = [
  { id: 'auth', title: 'Authentication' },
  { id: 'kpis', title: 'KPIs' },
  { id: 'values', title: 'KPI Values' },
  { id: 'analytics', title: 'Analytics' },
];

const endpoints = [
  {
    id: 'kpis', title: 'KPIs',
    routes: [
      { method: 'GET', path: '/api/v1/kpis', desc: 'List all KPIs. Supports pagination and filtering.',
        params: [
          { name: 'company_id', type: 'integer', desc: 'Filter by company' },
          { name: 'category', type: 'string', desc: 'Filter by category' },
          { name: 'page', type: 'integer', desc: 'Page number' },
        ]
      },
      { method: 'GET', path: '/api/v1/kpis/{kpi}', desc: 'Get a single KPI with relationships.' },
    ]
  },
  {
    id: 'values', title: 'KPI Values',
    routes: [
      { method: 'GET', path: '/api/v1/kpis/{kpi}/values', desc: 'List values for a KPI. Supports date range filtering.',
        params: [
          { name: 'from', type: 'date', desc: 'Start date (YYYY-MM-DD)' },
          { name: 'to', type: 'date', desc: 'End date (YYYY-MM-DD)' },
        ]
      },
      { method: 'POST', path: '/api/v1/kpis/{kpi}/values', desc: 'Record a new value. Triggers problem detection.',
        params: [
          { name: 'value', type: 'number', desc: 'The KPI value (required)' },
          { name: 'recorded_at', type: 'date', desc: 'Recording date (defaults to today)' },
          { name: 'notes', type: 'string', desc: 'Optional notes' },
        ]
      },
    ]
  },
  {
    id: 'analytics', title: 'Analytics',
    routes: [
      { method: 'GET', path: '/api/v1/kpis/{kpi}/analytics', desc: 'Get statistical analytics: count, min, max, avg, median, stddev, trend, latest value.' },
    ]
  },
];
</script>
