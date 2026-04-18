<template>
    <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between mt-4">
        <p class="text-sm text-gray-500">
            Menampilkan {{ meta.from }}–{{ meta.to }} dari {{ meta.total }} data
        </p>
        <div class="flex gap-1">
            <button
                @click="$emit('change', meta.current_page - 1)"
                :disabled="meta.current_page === 1"
                class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >
                ‹
            </button>
            <button
                v-for="p in pages"
                :key="p"
                @click="$emit('change', p)"
                :class="[
                    'px-3 py-1.5 rounded-lg text-sm border',
                    p === meta.current_page
                        ? 'bg-blue-600 text-white border-blue-600'
                        : 'border-gray-200 hover:bg-gray-50',
                ]"
            >
                {{ p }}
            </button>
            <button
                @click="$emit('change', meta.current_page + 1)"
                :disabled="meta.current_page === meta.last_page"
                class="px-3 py-1.5 rounded-lg text-sm border border-gray-200 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed"
            >
                ›
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ meta: Object })
defineEmits(['change'])

const pages = computed(() => {
    if (!props.meta) return []
    const cur = props.meta.current_page
    const last = props.meta.last_page
    const range = []
    for (let i = Math.max(1, cur - 2); i <= Math.min(last, cur + 2); i++) range.push(i)
    return range
})
</script>
