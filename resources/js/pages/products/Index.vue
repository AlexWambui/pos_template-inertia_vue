<script setup lang="ts">
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { debounce } from 'lodash-es';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import { Switch } from '@/components/ui/switch'
import products from '@/routes/products';
import Toast from '@/components/ui/ToastNotification/Index.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import DeleteConfirmationDialog from '@/components/ui/delete-dialog/DeleteConfirmation.vue';
import productCategories from '@/routes/product-categories';
import PageHeader from '@/components/PageHeader.vue';

interface Product {
    id: number
    name: string
    sku: string
    buying_price: number
    selling_price: number
    current_stock: number
    is_active: boolean
};

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
};

interface PaginatedProducts {
    data: Product[]
    current_page: number
    last_page: number
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

watch(search, () => {
    performSearch();
});

const togglingProductId = ref<number | null>(null);

const toggleActive = (product: Product) => {
    togglingProductId.value = product.id;

    router.put(products.toggleStatus(product.id).url, {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            togglingProductId.value = null;
        },
        onError: (errors) => {
            togglingProductId.value = null;
            console.error('Failed to update product status: ', errors);
        }
    });
}
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <Toast v-if="page.props.flash?.message" 
               :message="page.props.flash.message" 
               :type="page.props.flash.type" 
               :duration="5000" />

        <PageHeader 
            title="Products"
            v-model:search="search"
            :create-href="products.create().url"
            create-button-text="Create Products"
            search-placeholder="Search products by name, sku or barcode..."
        />

        <div class="products_table">
            <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50">
                            <TableHead class="w-[50px]">#</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>SKU</TableHead>
                            <TableHead>Buying Price</TableHead>
                            <TableHead>Selling Price</TableHead>
                            <TableHead>Inventory</TableHead>
                            <TableHead>Active</TableHead>
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
                            <TableCell  class="w-[100px] pr-[20px]">{{ product.name }}</TableCell>
                            <TableCell>{{ product.sku ?? '-' }}</TableCell>
                            <TableCell>{{ product.buying_price ?? '-' }}</TableCell>
                            <TableCell>{{ product.selling_price }}</TableCell>
                            <TableCell>{{ product.current_stock ?? 0 }}</TableCell>
                            <TableCell>
                                <Switch 
                                    v-model="product.is_active"
                                    :disabled="togglingProductId === product.id"
                                    @update:model-value="() => toggleActive(product)"
                                />
                            </TableCell>
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
                            <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                No products found
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="props.products.last_page > 1 && props.products.data.length > 0" class="mt-6 flex justify-center gap-1">
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
            <div v-if="props.products.total > 0" class="mt-4 text-gray-600 text-sm flex justify-center items-center gap-4">
                <div>
                    Showing {{ (props.products.current_page - 1) * props.products.per_page + 1 }} 
                    to {{ Math.min((props.products.current_page - 1) * props.products.per_page + props.products.data.length, props.products.total) }} 
                    of {{ props.products.total }} products
                </div>
                <div v-if="search" class="text-blue-600">
                    Filtered results
                </div>
            </div>
            <!-- Optional: Show a message when no results -->
            <div v-else class="mt-4 text-gray-600 text-sm text-center">
                No products to display
            </div>
        </div>
    </AppLayout>
</template>
