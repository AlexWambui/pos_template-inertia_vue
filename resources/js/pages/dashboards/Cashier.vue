<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { useFormatters } from '@/composables/useFormatters';
import { dashboard } from '@/routes';
import shifts from '@/routes/shifts';
import Button from '@/components/ui/button/Button.vue';

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
}

interface Stats {
    total_sales_today: number;
    total_transactions: number;
}

interface CurrentShift {
    id: number;
    opened_at: string;
    opening_cash: number;
    sales_total: number;
    transactions_count: number;
    duration_formatted: string;
}

interface Props {
    user: User;
    stats?: Stats;
    current_shift: CurrentShift;
}

const props = defineProps<Props>();

const { formatNumber, formatDecimal, formatDateTime } = useFormatters();
</script>

<template>
    <Head title="Cashier Dashboard" />

    <div class="AppContainer Dashboard CashierDashboard">
        <p class="salutation">Hi, {{ user.name }}</p>

        <section class="stats">
            <h2>Platform Statistics</h2>

            <div class="stats_wrapper">
                <div class="stat">
                    <p class="count">{{ formatNumber(1000) }}</p>
                    <p class="title">Customers</p>
                    <p class="extras">1000 Staff</p>
                </div>

                <div class="stat">
                    <p class="count">{{ formatNumber(1000) }}</p>
                    <p class="title">Products</p>
                    <p class="extras">1000 Categories</p>
                </div>

                <div class="stat">
                    <p class="count">{{ formatNumber(1000) }}</p>
                    <p class="title">Orders</p>
                    <p class="extras"></p>
                </div>
            </div>
        </section>

        <section class="shift">
            <h2>Shift Details</h2>

            <div class="shift_wrapper">
                <div class="shift_details">
                    <p>Opened at: {{ formatDateTime(current_shift.opened_at) }}</p>
                    <p>Opening Cash: {{ formatDecimal(current_shift.opening_cash) }}</p>   
                    <p>Duration: {{ current_shift.duration_formatted }}</p>
                    <p>Sales This Shift: {{ formatDecimal(current_shift.sales_total) }}</p>
                    <p>Transactions: {{ current_shift.transactions_count }}</p>
                </div>                

                <div class="shift_actions">
                    <Link :href="shifts.close()">
                        <Button>Close Shift</Button>
                    </Link>                        
                </div>
            </div>
        </section>
    </div>
</template>