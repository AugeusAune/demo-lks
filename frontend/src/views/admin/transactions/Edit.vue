<template><TransactionForm :transaction-id="route.params.id" /></template>
<script setup>
import TransactionForm from '@/components/TransactionForm.vue'
import { useRoute } from 'vue-router'
const route = useRoute()
</script>
