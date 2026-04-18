<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="show" class="modal-overlay" @click.self="$emit('cancel')">
                <div class="modal max-w-sm">
                    <div class="modal-header">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center"
                            >
                                <ExclamationTriangleIcon class="w-5 h-5 text-red-600" />
                            </div>
                            <h3 class="font-semibold text-gray-800">{{ title || 'Konfirmasi' }}</h3>
                        </div>
                    </div>
                    <div class="modal-body">
                        <p class="text-sm text-gray-600">{{ message || 'Apakah Anda yakin?' }}</p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn-secondary" @click="$emit('cancel')">Batal</button>
                        <button class="btn-danger" @click="$emit('confirm')" :disabled="loading">
                            <ArrowPathIcon v-if="loading" class="w-4 h-4 animate-spin" />
                            {{ confirmText || 'Hapus' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ExclamationTriangleIcon, ArrowPathIcon } from '@heroicons/vue/24/outline'
defineProps({
    show: Boolean,
    title: String,
    message: String,
    confirmText: String,
    loading: Boolean,
})
defineEmits(['confirm', 'cancel'])
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
