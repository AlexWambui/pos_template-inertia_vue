<!-- resources/js/Pages/products/categories/Index.vue -->
<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash-es';
import { FolderTree, Search, Plus } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import products from '@/routes/products';
import productCategories from '@/routes/product-categories';
import Toast from '@/components/ui/ToastNotification/Index.vue';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import DeleteConfirmationDialog from '@/components/ui/delete-dialog/DeleteConfirmation.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import CategoryTree from './CategoryTree.vue';

interface Category {
    id: number;
    name: string;
    parent_id: number | null;
    parent_name?: string;
    children?: Category[];
    is_active?: boolean;
    sort_order?: number;
    depth?: number;
    path?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCategories {
    data: Category[];
    current_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface Props {
    categories: PaginatedCategories | Category[];
    filters: {
        search?: string;
    };
    isSearching: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: products.index().url },
    { title: 'Categories', href: productCategories.index().url },
];

const page = usePage<any>();
const search = ref(props.filters.search || '');

const performSearch = debounce(() => {
    router.get(productCategories.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}, 300);

watch(search, () => {
    performSearch();
});

const clearFilters = () => {
    search.value = '';
    performSearch();
};

// Check if we're in tree view or search view
const isTreeView = computed(() => {
    return !props.isSearching && !search.value;
});

// Get categories for tree view
const treeCategories = computed((): Category[] => {
    // When not searching and isSearching is false, categories should be an array
    if (!props.isSearching && Array.isArray(props.categories)) {
        return props.categories;
    }
    return [];
});

// Get categories for search view - properly typed
const searchResults = computed((): PaginatedCategories | null => {
    // When searching, categories should be a paginated object
    if (props.isSearching && !Array.isArray(props.categories) && props.categories && 'data' in props.categories) {
        return props.categories as PaginatedCategories;
    }
    return null;
});

// Helper computed for pagination display
const paginationInfo = computed(() => {
    const results = searchResults.value;
    if (!results) return null;
    
    return {
        from: results.from || 0,
        to: results.to || 0,
        total: results.total || 0,
        current_page: results.current_page || 1,
        per_page: results.per_page || 20,
        links: results.links || []
    };
});

// Calculate row number for search results
const getRowNumber = (index: number): number => {
    const pagination = paginationInfo.value;
    if (!pagination) return index + 1;
    return (pagination.current_page - 1) * pagination.per_page + index + 1;
};
</script>

<template>
    <Head title="Product Categories" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <Toast 
                v-if="page.props.flash?.message" 
                :message="page.props.flash.message" 
                :type="page.props.flash.type" 
                :duration="5000" 
            />

            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-50 rounded-lg">
                        <FolderTree class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Product Categories</h1>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ isTreeView ? 'Organize your products with nested categories' : 'Search results' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full sm:w-auto">
                    <!-- Search -->
                    <div class="relative flex-1 sm:flex-initial">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <Search class="h-4 w-4 text-gray-400" />
                        </div>
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Search categories..."
                            class="pl-10 w-full sm:w-[300px]"
                        />
                    </div>

                    <!-- Create Button -->
                    <Link :href="productCategories.create().url">
                        <Button class="w-full sm:w-auto">
                            <Plus class="w-4 h-4 mr-2" />
                            New Category
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Active Filters -->
            <div v-if="search" class="flex items-center gap-2 mb-4">
                <span class="text-sm text-gray-600">Filtering by:</span>
                <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-50 text-blue-700 text-sm rounded-full">
                    <Search class="w-3 h-3" />
                    "{{ search }}"
                    <button @click="clearFilters" class="ml-1 hover:text-blue-900">×</button>
                </span>
            </div>

            <!-- Categories Table/Card -->
            <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader class="bg-gray-50">
                            <TableRow>
                                <TableHead class="w-[80px] font-semibold">ID</TableHead>
                                <TableHead class="font-semibold">Category Name</TableHead>
                                <TableHead class="w-[200px] text-center font-semibold">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        
                        <TableBody>
                            <!-- Tree View (Hierarchical) -->
                            <template v-if="isTreeView && treeCategories.length">
                                <CategoryTree :categories="treeCategories" />
                            </template>
                            
                            <!-- Search Results (Flat List) -->
                            <template v-else-if="searchResults && searchResults.data.length">
                                <TableRow 
                                    v-for="(category, index) in searchResults.data" 
                                    :key="category.id"
                                    class="hover:bg-gray-50"
                                >
                                    <TableCell class="font-medium">
                                        {{ getRowNumber(index) }}
                                    </TableCell>
                                    <TableCell>
                                        <div class="flex flex-col">
                                            <span :class="category.is_active ? 'text-gray-900' : 'text-gray-400'">
                                                {{ category.name }}
                                            </span>
                                            <span v-if="category.path" class="text-xs text-gray-500 mt-1">
                                                {{ category.path }}
                                            </span>
                                            <span v-if="!category.is_active" 
                                                  class="mt-1 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full w-fit">
                                                Inactive
                                            </span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="text-center">
                                        <div class="flex justify-center space-x-2">
                                            <Link 
                                                :href="productCategories.edit(category.id).url" 
                                                class="text-blue-600 hover:text-blue-800 hover:underline text-sm"
                                            >
                                                Edit
                                            </Link>
                                            <span class="text-gray-300">|</span>
                                            <DeleteConfirmationDialog 
                                                :url="productCategories.destroy(category.id).url" 
                                                title="Delete Category?" 
                                                description="This product category will be deleted permanently!" 
                                                confirm-text="Delete Category"
                                            >
                                                <template #trigger>
                                                    <button class="text-red-600 hover:text-red-800 hover:underline text-sm">
                                                        Delete
                                                    </button>
                                                </template>
                                            </DeleteConfirmationDialog>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                            
                            <!-- Empty State -->
                            <TableRow v-else>
                                <TableCell colspan="3" class="py-12">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <FolderTree class="w-12 h-12 text-gray-300 mb-3" />
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">
                                            {{ search ? 'No categories found' : 'No categories yet' }}
                                        </h3>
                                        <p class="text-sm text-gray-500 mb-4">
                                            {{ search ? 'Try adjusting your search terms' : 'Get started by creating your first category' }}
                                        </p>
                                        <Link v-if="!search" :href="productCategories.create().url">
                                            <Button>
                                                <Plus class="w-4 h-4 mr-2" />
                                                Create Category
                                            </Button>
                                        </Link>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>

            <!-- Pagination (Search Results Only) -->
            <div v-if="!isTreeView && paginationInfo && paginationInfo.links.length > 3" class="mt-6">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-600">
                        Showing {{ paginationInfo.from }} to {{ paginationInfo.to }} of {{ paginationInfo.total }} categories
                    </div>
                    
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, index) in paginationInfo.links"
                            :key="index"
                            :href="link.url || '#'"
                            class="px-3 py-1.5 text-sm border rounded-md transition-colors"
                            :class="{
                                'bg-blue-600 text-white border-blue-600': link.active,
                                'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active && link.url,
                                'bg-gray-50 text-gray-400 cursor-not-allowed': !link.url
                            }"
                            v-html="link.label"
                            :disabled="!link.url"
                        />
                    </div>
                </div>
            </div>

            <!-- Tree View Stats -->
            <div v-if="isTreeView && treeCategories.length" class="mt-4 text-sm text-gray-600">
                <span class="font-medium">{{ treeCategories.length }}</span> root categories
            </div>
        </div>
    </AppLayout>
</template>