<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { useFormatters } from '@/composables/useFormatters';

const { formatNumber, formatDecimal } = useFormatters();

defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: number;
    };
    stats: {
        total_staff: number;
        total_customers: number;
        total_products: number;
        total_product_categories: number;
        total_branches: number;
        total_sales: number;
        gross_sales: number;
        net_sales: number;
        cost_of_sales: number;
        gross_profit: number;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="AdminDashboard">
            <p class="salutation">Hi, {{ user.name }}</p>

            <div class="stats">
                <h2>Platform Statistics</h2>

                <div class="stats_wrapper">
                    <div class="stat">
                        <p class="count">{{ formatNumber(stats.total_customers) }}</p>
                        <p class="title">Customers</p>
                        <p class="extras">{{ stats.total_staff }} Staff</p>
                    </div>

                    <div class="stat">
                        <p class="count">{{ formatNumber(stats.total_products) }}</p>
                        <p class="title">Products</p>
                        <p class="extras">{{ stats.total_product_categories }} Categories</p>
                    </div>

                    <div class="stat">
                        <p class="count">{{ formatNumber(stats.total_sales) }}</p>
                        <p class="title">Sales</p>
                        <p class="extras"></p>
                    </div>

                    <div class="stat">
                        <p class="count">{{ formatNumber(stats.total_branches) }}</p>
                        <p class="title">Branches</p>
                        <p class="extras"></p>
                    </div>
                </div>
            </div>

            <div class="financial_stats">
                <h2>Fiscal Overview (Ksh)</h2>

                <div class="financial_stats_wrapper">
                    <div class="stat">
                        <p class="count">{{ formatDecimal(stats.gross_sales) }}</p>
                        <p class="title">Gross Sales</p>
                        <p class="extras">All sales at full price</p>
                    </div>

                    <div class="stat">
                        <p class="count">{{ formatDecimal(stats.net_sales) }}</p>
                        <p class="title">Net Sales</p>
                        <p class="extras">All sales minus discounts</p>
                    </div>

                    <div class="stat">
                        <p class="count text-red-600">{{ formatDecimal(stats.cost_of_sales) }}</p>
                        <p class="title">Cost of Sales</p>
                        <p class="extras"></p>
                    </div>

                    <div class="stat">
                        <p class="count text-green-800">{{ formatDecimal(stats.gross_profit) }}</p>
                        <p class="title">Gross Profit</p>
                        <p class="extras"></p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
