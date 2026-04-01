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

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Products', href: products.index().url },
            { title: 'Categories', href: product_categories.index().url },
        ],
    },
});

const page = usePage<any>();

interface ProductCategory {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    products_count: number;
}

interface Props {
    categories: ProductCategory[];
    total: number;
    filters: {
        search?: string;
    };
};

const props = defineProps<Props>();

const search = ref(props.filters.search || '');

const performSearch = debounce(() => {
    router.get(product_categories.index().url, {
        search: search.value,
    }, {
        preserveState: true,
        replace: true
    });
}, 300);

watch(search, () => {
    performSearch();
});
</script>

<template>
    <Head title="Product Categories" />

    <div class="AppContainer">
        <Toast v-if="page.props.flash?.message" 
            :message="page.props.flash.message" 
            :type="page.props.flash.type" 
            :duration="5000" 
        />

        <PageHeader 
            title="Product Categories"
            v-model:search="search"
            :create-href="product_categories.create().url"
            create-button-text="Create Category"
            search-placeholder="Search categories by name..."
        />

        <div class="product_categories_table">
            <div class="bg-white dark:bg-gray-900 rounded-lg border shadow-sm overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow class="bg-gray-50 dark:bg-gray-900">
                            <TableHead class="w-[50px]">#</TableHead>
                            <TableHead>Name</TableHead>
                            <TableHead>Slug</TableHead>
                            <TableHead>Products</TableHead>
                            <TableHead class="text-center">Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-for="(category, index) in props.categories" 
                                 :key="category.id"
                                 class="hover:bg-gray-50">
                            <TableCell class="font-medium">{{ index + 1 }}</TableCell>
                            <TableCell :class="{ 'text-red-500' : category.is_active === false, '' : category.is_active === true }">{{ category.name }}</TableCell>
                            <TableCell>{{ category.slug }}</TableCell>
                            <TableCell>{{ category.products_count }}</TableCell>
                            <TableCell class="text-center">
                                <div class="flex justify-center space-x-2">
                                    <Link :href="product_categories.edit(category.id).url" 
                                          class="text-blue-600 hover:text-blue-800 hover:underline">
                                        Edit
                                    </Link>
                                    <span class="text-gray-300">|</span>
                                    <DeleteConfirmationDialog 
                                        :url="product_categories.destroy(category.id).url" 
                                        title="Delete Product Category?" 
                                        description="This category will be deleted permanently!" 
                                        confirm-text="Delete Category">
                                        <template #trigger>
                                            <button class="text-red-600 hover:text-red-800 hover:underline">
                                                Delete
                                            </button>
                                        </template>
                                    </DeleteConfirmationDialog>
                                </div>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="props.categories.length === 0">
                            <TableCell colspan="8" class="text-center py-8 text-gray-500">
                                No categories found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="mt-4 text-gray-600 text-sm flex justify-center items-center gap-4">
                <div>Showing {{ props.total }} categories</div>
                <div v-if="search" class="text-blue-600">
                    Filtered results
                </div>
            </div>
        </div>
    </div>
</template>