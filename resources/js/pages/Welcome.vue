<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login, register } from '@/routes';

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div class="welcome_page">
        <div class="text">
            <h1>Aqilify POS</h1>
            <p>Cashiering Made Easy</p>
        </div>

        <div class="welcome_page_btn">
            <Link :href="login()">Get Started</Link>
        </div>
    </div>
</template>
