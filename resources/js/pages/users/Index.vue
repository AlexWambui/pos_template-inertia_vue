<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash-es';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import users from '@/routes/users';
import Toast from '@/components/ui/ToastNotification/Index.vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableFooter,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/ui/delete-dialog/DeleteConfirmation.vue';

interface User {
    id: number
    name: string
    email: string
    role_label: string
};

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
};

interface PaginatedUsers {
    data: User[]
    current_page: number
    per_page: number
    total: number
    links: PaginationLink[]
};

interface RoleCount {
    count: number
    label: string
}

interface Props {
    users: PaginatedUsers;
    role_counts: Record<string, RoleCount>;
    filters: {
        search?: string;
        role?: string;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: users.create().url,
    },
];

const page = usePage<any>();

// Reactive search input
const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');

// Get total users count
const totalUsers = computed(() => {
    return Object.values(props.role_counts).reduce((sum, role) => sum + role.count, 0);
});

// Get all role values from the enum (for TypeScript)
const roleValues = computed(() => {
    return Object.keys(props.role_counts).map(key => ({
        value: key,
        label: props.role_counts[key].label
    }));
});

// Debounced search function
const performSearch = debounce(() => {
    router.get(users.index().url, {
        search: search.value,
        role: selectedRole.value
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

// Watch search and role changes
watch([search, selectedRole], () => {
    performSearch();
});

// Function to filter by role
const filterByRole = (role: string) => {
    selectedRole.value = selectedRole.value === role ? '' : role;
};

// Function to clear all filters
const clearFilters = () => {
    search.value = '';
    selectedRole.value = '';
};
</script>

<template>
    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Toast v-if="page.props.flash?.message" 
                   :message="page.props.flash.message" 
                   :type="page.props.flash.type" 
                   :duration="5000" />

            <!-- Header with Stats and Actions -->
            <div class="header mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl font-bold">Users</h1>

                    <div class="search-filter-bar">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <!-- Search Input -->
                            <div class="flex-1">
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search users by name or email..."
                                    class="w-full"
                                />
                            </div>

                            <!-- Active Filters Display -->
                            <div class="flex flex-wrap gap-2 items-center">
                                <span v-if="search || selectedRole" class="text-sm text-gray-600">
                                    Filters:
                                </span>
                                <span v-if="search" 
                                    class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full flex items-center gap-1">
                                    Search: "{{ search }}"
                                    <button @click="search = ''" class="text-blue-600 hover:text-blue-800">×</button>
                                </span>
                                <span v-if="selectedRole" 
                                    class="px-3 py-1 bg-green-100 text-green-800 text-sm rounded-full flex items-center gap-1">
                                    Role: {{ role_counts[selectedRole]?.label || selectedRole }}
                                    <button @click="selectedRole = ''" class="text-green-600 hover:text-green-800">×</button>
                                </span>
                                <!-- <Button v-if="search || selectedRole" 
                                        @click="clearFilters" 
                                        variant="outline" 
                                        size="sm">
                                    Clear All
                                </Button> -->
                            </div>
                        </div>
                    </div>

                    <Link :href="users.create().url">
                        <Button>Create User</Button>
                    </Link>
                </div>

                <!-- Role Statistics -->
                <div class="role-stats mb-4 py-2 px-4 bg-gray-50 rounded-lg">
                    <div class="flex flex-wrap gap-8 text-sm">
                        <div class="stat-item cursor-pointer" 
                             @click="filterByRole('')"
                             :class="{ 'font-bold text-blue-600': !selectedRole }">
                            <span class="font-semibold">{{ totalUsers }}</span> Users
                        </div>
                        <div v-for="(roleCount, roleValue) in role_counts" 
                             :key="roleValue"
                             class="stat-item cursor-pointer"
                             @click="filterByRole(roleValue)"
                             :class="{ 'font-bold text-blue-600': selectedRole === roleValue }">
                            <span class="font-semibold">{{ roleCount.count }}</span> {{ roleCount.label }}{{ roleCount.count !== 1 ? 's' : '' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="users_table">
                <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-gray-50">
                                <TableHead class="w-[100px]">#</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Role</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="(user, index) in props.users.data" 
                                     :key="user.id"
                                     class="hover:bg-gray-50">
                                <TableCell class="font-medium">
                                    {{ (props.users.current_page - 1) * props.users.per_page + index + 1 }}
                                </TableCell>
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                          :class="{
                                            'bg-purple-100 text-purple-800': user.role_label === 'Super Admin',
                                            'bg-blue-100 text-blue-800': user.role_label === 'Admin',
                                            'bg-green-100 text-green-800': user.role_label === 'Cashier',
                                            'bg-yellow-100 text-yellow-800': user.role_label === 'Supplier',
                                            'bg-gray-100 text-gray-800': user.role_label === 'Customer'
                                          }">
                                        {{ user.role_label }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center space-x-2">
                                        <Link :href="users.edit(user.id).url" 
                                              class="text-blue-600 hover:text-blue-800 hover:underline">
                                            Edit
                                        </Link>
                                        <span class="text-gray-300">|</span>
                                        <DeleteConfirmationDialog 
                                            :url="users.destroy(user.id).url" 
                                            title="Delete User?" 
                                            description="This user will be deleted permanently!" 
                                            confirm-text="Delete User">
                                            <template #trigger>
                                                <button class="text-red-600 hover:text-red-800 hover:underline">
                                                    Delete
                                                </button>
                                            </template>
                                        </DeleteConfirmationDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.users.data.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-gray-500">
                                    No users found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div v-if="props.users.links.length > 1" class="mt-6 flex justify-center gap-1">
                    <Link v-for="(link, index) in props.users.links" 
                         :key="index" 
                         :href="link.url ?? ''" 
                         v-html="link.label" 
                         class="px-3 py-1 border rounded text-sm transition-colors"
                         :class="{
                            'bg-gray-200 text-gray-500 cursor-not-allowed': !link.url,
                            'bg-blue-600 text-white border-blue-600': link.active,
                            'hover:bg-gray-100 border-gray-300': link.url && !link.active,
                         }"
                         :disabled="!link.url"
                         preserve-scroll />
                </div>

                <!-- Results summary -->
                <div class="mt-4 text-gray-600 text-sm flex justify-center items-center gap-4">
                    <div>
                        Showing {{ (props.users.current_page - 1) * props.users.per_page + 1 }} 
                        to {{ Math.min((props.users.current_page - 1) * props.users.per_page + props.users.data.length, props.users.total) }} 
                        of {{ props.users.total }} users
                    </div>
                    <div v-if="search || selectedRole" class="text-blue-600">
                        Filtered results
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.stat-item {
    transition: all 0.2s ease;
}

.stat-item:hover {
    transform: translateY(-1px);
}

.role-stats {
    border: 1px solid #e5e7eb;
}
</style>