<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <div class="welcome_page relative hidden h-full bg-zinc-900 p-10 text-white lg:flex">
            <video autoplay muted loop playsinline preload="auto" disable-picture-in-picture class="video-bg">
                <source src="/assets/videos/hero-section.mp4" type="video/mp4">
            </video>
            
            <!-- Brand -->
            <div class="relative z-20 flex items-center gap-2 text-xl font-bold leading-tight">
                <AppLogoIcon class="mr-2 size-8 fill-current text-white" />
                {{ name }}
            </div>

            <!-- Center content -->
            <div class="relative z-20 space-y-4">
                <h2 class="text-3xl font-semibold leading-tight">
                    Login
                </h2>

                <ul class="mt-4 space-y-2 text-sm text-zinc-300">
                    <li>Fast checkout workflows</li>
                    <li>Secure role-based access</li>
                    <li>Designed for POS terminals</li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="relative z-20 text-xs text-zinc-500">
                © {{ new Date().getFullYear() }} {{ name }}
            </div>
        </div>

        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-xl font-medium tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
