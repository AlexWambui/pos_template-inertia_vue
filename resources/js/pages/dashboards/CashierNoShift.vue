<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import { dashboard } from '@/routes';
import shifts from '@/routes/shifts';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
        ],
    },
});

interface User {
    id: number;
    name: string;
    email: string;
    role: number;
};

interface Shift {
    id: number;
    opened_at: string;
    closed_at: string | null;
    sales_count?: number;
    opening_cash?: number;
    closing_cash?: number;
};

interface Stats {
    total_sales_today: number;
    total_transactions_today: number;
};

interface Props {
    user: User;
    lastShift?: Shift | null;
    stats?: Stats;
};

defineProps<Props>();
</script>

<template>
    <Head title="CashierNoShift Dashboard" />

    <div class="AppContainer Dashboard CashierNoShiftDashboard">
        <p class="salutation">Hi, {{ user.name }}</p>
        <p class="text-gray-600 mb-6">You need to open a shift before you can start selling</p>

        <Link :href="shifts.open()">
            <Button>
                Start Shift
            </Button>
        </Link>
        
        <div v-if="lastShift" class="mt-8 p-4 bg-gray-50 rounded-lg text-left">
            <h3 class="font-semibold mb-2">Your Last Shift</h3>
            <p>Opened: {{ new Date(lastShift.opened_at).toLocaleString() }}</p>
            <p v-if="lastShift.closed_at">Closed: {{ new Date(lastShift.closed_at).toLocaleString() }}</p>
            <p>Sales: {{ lastShift.sales_count || 0 }} transactions</p>
        </div>
    </div>
</template>