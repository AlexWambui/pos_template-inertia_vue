<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash-es';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import products from '@/routes/products';
import Toast from '@/components/ui/ToastNotification/Index.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/ui/delete-dialog/DeleteConfirmation.vue';
import productCategories from '@/routes/product-categories';

interface Product {
    id: number
    name: string
    sku: string
    buying_price: number
    selling_price: number
    current_stock: number
};

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
};

interface PaginatedProducts {
    data: Product[]
    current_page: number
    per_page: number
    total: number
    links: PaginationLink[]
};

interface Props {
    products: PaginatedProducts;
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Product Categories', href: productCategories.index().url },
    { title: 'Products', href: products.index().url },
];

const page = usePage<any>();

// Reactive search input
const search = ref(props.filters.search || '');
// const selectedRole = ref(props.filters.role || '')

// Debounced search function
const performSearch = debounce(() => {
    router.get(products.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

// Function to clear all filters
const clearFilters = () => {
    search.value = '';
};
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <Toast v-if="page.props.flash?.message" 
                   :message="page.props.flash.message" 
                   :type="page.props.flash.type" 
                   :duration="5000" />

            <div class="header mb-6">
                <div class="flex justify-between items-center mb-4">
                    <h1 class="text-xl font-bold">Products</h1>

                    <div class="search-filter-bar">
                        <div class="flex flex-col md:flex-row md:items-center gap-4">
                            <div class="flex-1">
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search products by name, sku or barcode..."
                                    class="w-full"
                                />
                            </div>

                            <div class="flex flex-wrap gap-2 items-center">
                                <span v-if="search" 
                                    class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full flex items-center gap-1">
                                    Search: "{{ search }}"
                                    <button @click="search = ''" class="text-blue-600 hover:text-blue-800">×</button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <Link :href="products.create().url">
                        <Button>Create Product</Button>
                    </Link>
                </div>
            </div>

            <div class="products_table">
                <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                    <Table>
                        <TableHeader>
                            <TableRow class="bg-gray-50">
                                <TableHead class="w-[100px]">#</TableHead>
                                <TableHead>Name</TableHead>
                                <TableHead>SKU</TableHead>
                                <TableHead>Buying Price</TableHead>
                                <TableHead>Selling Price</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="(product, index) in props.products.data" 
                                     :key="product.id"
                                     class="hover:bg-gray-50">
                                <TableCell class="font-medium">
                                    {{ (props.products.current_page - 1) * props.products.per_page + index + 1 }}
                                </TableCell>
                                <TableCell>{{ product.name }}</TableCell>
                                <TableCell>{{ product.sku }}</TableCell>
                                <TableCell>{{ product.buying_price }}</TableCell>
                                <TableCell>{{ product.selling_price }}</TableCell>
                                <TableCell class="text-center">
                                    <div class="flex justify-center space-x-2">
                                        <Link :href="products.edit(product.id).url" 
                                              class="text-blue-600 hover:text-blue-800 hover:underline">
                                            Edit
                                        </Link>
                                        <span class="text-gray-300">|</span>
                                        <DeleteConfirmationDialog 
                                            :url="products.destroy(product.id).url" 
                                            title="Delete Product?" 
                                            description="This product will be deleted permanently!" 
                                            confirm-text="Delete Product">
                                            <template #trigger>
                                                <button class="text-red-600 hover:text-red-800 hover:underline">
                                                    Delete
                                                </button>
                                            </template>
                                        </DeleteConfirmationDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.products.data.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-gray-500">
                                    No products found.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Pagination -->
                <div v-if="props.products.links.length > 1" class="mt-6 flex justify-center gap-1">
                    <Link v-for="(link, index) in props.products.links" 
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
                        Showing {{ (props.products.current_page - 1) * props.products.per_page + 1 }} 
                        to {{ Math.min((props.products.current_page - 1) * props.products.per_page + props.products.data.length, props.products.total) }} 
                        of {{ props.products.total }} products
                    </div>
                    <div v-if="search" class="text-blue-600">
                        Filtered results
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
