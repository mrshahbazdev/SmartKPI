<template>
  <AppShell>
    <div class="max-w-3xl mx-auto space-y-6">
      <div>
        <a href="/relationships" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary-600">← {{ $t('common.back') }}</a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2">{{ $t('relationship.root_cause_trace') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ locale === 'de' ? kpi.name_de : kpi.name_en }}</p>
      </div>

      <!-- Trace chain (vertical flow) -->
      <div v-if="chain.length" class="relative pl-6">
        <!-- Vertical line -->
        <div class="absolute left-2.5 top-0 bottom-0 w-0.5 bg-primary-200 dark:bg-primary-800"></div>

        <!-- Target KPI -->
        <div class="relative mb-6 -ml-6 pl-6">
          <div class="absolute left-0 w-5 h-5 rounded-full bg-red-500 border-2 border-white dark:border-gray-900 flex items-center justify-center">
            <span class="text-white text-xs">!</span>
          </div>
          <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-4 ml-3">
            <p class="text-xs text-red-500 dark:text-red-400 mb-1">{{ $t('relationship.effect') }}</p>
            <h3 class="font-semibold text-gray-900 dark:text-white">{{ locale === 'de' ? kpi.name_de : kpi.name_en }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ kpi.department }} · {{ kpi.company }}</p>
          </div>
        </div>

        <!-- Cause chain -->
        <CauseNode v-for="(node, i) in chain" :key="i" :node="node" :locale="locale" :depth="0" />
      </div>

      <div v-else class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <p class="text-gray-500 dark:text-gray-400">{{ $t('relationship.no_causes') }}</p>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, defineComponent, h } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AppShell from '@/Components/Layout/AppShell.vue';

const props = defineProps({
  kpi: { type: Object, required: true },
  chain: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || 'de');

// Recursive component for cause chain
const CauseNode = defineComponent({
  name: 'CauseNode',
  props: {
    node: { type: Object, required: true },
    locale: { type: String, default: 'de' },
    depth: { type: Number, default: 0 },
  },
  setup(props) {
    return () => h('div', { class: 'relative mb-4' }, [
      // Dot
      h('div', {
        class: `absolute -left-3.5 w-4 h-4 rounded-full border-2 border-white dark:border-gray-900 ${props.depth === 0 ? 'bg-orange-500' : 'bg-yellow-500'}`,
      }),
      // Card
      h('div', {
        class: 'bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 ml-3',
      }, [
        h('div', { class: 'flex items-center gap-2 mb-1' }, [
          h('span', { class: 'text-xs text-primary-600 dark:text-primary-400' }, `${props.locale === 'de' ? 'Gewicht' : 'Weight'}: ${props.node.weight}`),
          props.node.confidence ? h('span', { class: 'text-xs text-gray-500 dark:text-gray-400' }, `${props.locale === 'de' ? 'Konfidenz' : 'Confidence'}: ${Math.round(props.node.confidence * 100)}%`) : null,
          props.node.lag_days ? h('span', { class: 'text-xs text-gray-500 dark:text-gray-400' }, `${props.locale === 'de' ? 'Verzögerung' : 'Lag'}: ${props.node.lag_days}d`) : null,
        ]),
        h('h3', { class: 'font-semibold text-gray-900 dark:text-white' }, props.locale === 'de' ? props.node.name_de : props.node.name_en),
        h('p', { class: 'text-sm text-gray-500 dark:text-gray-400' }, `${props.node.department || ''} · ${props.node.company || ''}`),
      ]),
      // Recursion
      ...(props.node.causes || []).map((child, i) =>
        h('div', { class: 'pl-6 relative', key: i }, [
          h('div', { class: 'absolute left-2.5 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700' }),
          h(CauseNode, { node: child, locale: props.locale, depth: props.depth + 1 }),
        ])
      ),
    ]);
  },
});
</script>
