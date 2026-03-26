<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { dashboard } from '@/routes';
import { useFormatters } from '@/composables/userFormatters';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    }
];

defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: number;
    };

    stats: {
        total_branches: number;
        total_inactive_branches: number;
    }
}>();

const { formatNumber, formatDecimal } = useFormatters();
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="AppContainer Dashboard AdminDashboard">
        <p class="salutation">Hi, {{ user.name }}</p>

        <div class="stats">
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

                <div class="stat">
                    <p class="count">{{ formatNumber(stats.total_branches) }}</p>
                    <p class="title">Branches</p>
                    <p class="extras" :class="[ stats.total_inactive_branches > 0 ? 'inactive' : '' ]">{{ formatNumber(stats.total_inactive_branches) }} Inactive</p>
                </div>
            </div>
        </div>
    </div>
</template>