<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[100] space-y-2 pointer-events-none">
            <TransitionGroup name="toast">
                <div
                    v-for="t in toasts"
                    :key="t.id"
                    :class="[
                        'flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg pointer-events-auto max-w-sm',
                        t.type === 'success'
                            ? 'bg-green-600 text-white'
                            : t.type === 'error'
                              ? 'bg-red-600 text-white'
                              : 'bg-gray-800 text-white',
                    ]"
                >
                    <CheckCircleIcon
                        v-if="t.type === 'success'"
                        class="w-5 h-5 flex-shrink-0 mt-0.5"
                    />
                    <ExclamationCircleIcon
                        v-else-if="t.type === 'error'"
                        class="w-5 h-5 flex-shrink-0 mt-0.5"
                    />
                    <InformationCircleIcon v-else class="w-5 h-5 flex-shrink-0 mt-0.5" />
                    <p class="text-sm font-medium">{{ t.message }}</p>
                    <button @click="remove(t.id)" class="ml-auto opacity-70 hover:opacity-100">
                        <XMarkIcon class="w-4 h-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { ref } from 'vue'
import {
    CheckCircleIcon,
    ExclamationCircleIcon,
    InformationCircleIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline'

const toasts = ref([])
let counter = 0

function add(message, type = 'info', duration = 3500) {
    const id = ++counter
    toasts.value.push({ id, message, type })
    setTimeout(() => remove(id), duration)
}

function remove(id) {
    toasts.value = toasts.value.filter((t) => t.id !== id)
}

defineExpose({ add })
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
