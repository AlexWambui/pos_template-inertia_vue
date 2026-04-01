<script setup lang="ts">
import { Head, usePage, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';
import product_categories from '@/routes/product-categories';
import products from '@/routes/products';
import PageHeader from '@/components/custom/PageHeader.vue';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmation.vue';
import Toast from '@/components/custom/ToastNotification/Index.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Switch } from '@/components/ui/switch'

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Categories', href: product_categories.index().url },
            { title: 'Products', href: products.index().url },
        ],
    },
});

const page = usePage<any>();

interface Product {
    id: number;
    name: string;
    sku: string;
    buying_price: number;
    selling_price: number;
    current_stock: number;
    is_active: boolean;
}

interface Props {
    products: Product[];
    total: number;
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

const performSearch = debounce(() => {
    router.get(products.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

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

    <div class="AppContainer">
        <Toast v-if="page.props.flash?.message" 
            :message="page.props.flash.message" 
            :type="page.props.flash.type" 
            :duration="5000" 
        />

        <PageHeader 
            title="Products"
            v-model:search="search"
            :create-href="products.create().url"
            create-button-text="Create Product"
            search-placeholder="Search products by name..."
        />

        <div class="products_table">
            <div class="bg-white dark:bg-gray-900 rounded-lg border shadow-sm overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 dark:bg-gray-900">
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
                        <TableRow v-for="(product, index) in props.products" 
                                 :key="product.id"
                                 class="hover:bg-gray-50">
                            <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                            <TableCell :class="{ 'text-red-500' : product.is_active === false, '' : product.is_active === true }">{{ product.name }}</TableCell>
                            <TableCell>{{ product.sku ?? '-' }}</TableCell>
                            <TableCell>{{ product.buying_price ?? '-' }}</TableCell>
                            <TableCell>{{ product.selling_price }}</TableCell>
                            <TableCell>{{ product.current_stock ?? '-' }}</TableCell>
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
                        <TableRow v-if="props.products.length === 0">
                            <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                No products found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="mt-4 text-gray-600 text-sm flex justify-center items-center gap-4">
                <div>Showing {{ props.total }} products</div>
                <div v-if="search" class="text-blue-600">
                    Filtered results
                </div>
            </div>
        </div>
    </div>
</template>