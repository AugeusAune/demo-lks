<!-- StatusBadge.vue -->
<template>
    <span :class="cls" class="badge">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'
const props = defineProps({ status: String })

const map = {
    received: { cls: 'badge-blue', label: 'Diterima' },
    diagnosa: { cls: 'badge-yellow', label: 'Diagnosa' },
    perbaikan: { cls: 'badge-orange', label: 'Perbaikan' },
    selesai: { cls: 'badge-green', label: 'Selesai' },
    diambil: { cls: 'badge-gray', label: 'Diambil' },
    batal: { cls: 'badge-red', label: 'Batal' },
}

const cls = computed(() => map[props.status]?.cls || 'badge-gray')
const label = computed(() => map[props.status]?.label || props.status)
</script>
