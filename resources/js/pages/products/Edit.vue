<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import products from '@/routes/products';

interface Category {
    id: number;
    name: string;
    parent_id: number | null;
}

interface Props {
    product: {
        id: number;
        name: string;
        sku: string;
        barcode: string;
        is_active: boolean;
        buying_price: number | null;
        selling_price: number | null;
        unit_of_measurement: string;
        current_stock: number;
        categories: Category[];
    };
    categories?: Category[];
}

const props = defineProps<Props>();

// Update breadcrumbs for edit page
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Products', href: products.index().url },
    { title: `Edit: ${props.product.name}`, href: products.edit(props.product.id).url },
];

// Initialize form with existing product data
const form = useForm({
    name: props.product.name || '',
    sku: props.product.sku || '',
    barcode: props.product.barcode || '',
    is_active: props.product.is_active ?? true,
    buying_price: props.product.buying_price,
    selling_price: props.product.selling_price,
    unit_of_measurement: props.product.unit_of_measurement || '',
    current_stock: props.product.current_stock,
    categories: props.product.categories?.map(c => c.id) || [],
});

const handleSubmit = () => {
    form.put(products.update(props.product.id).url);
};
</script>

<template>
    <Head :title="`Edit Product: ${product.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="product_edit_form">
            <form @submit.prevent="handleSubmit" class="w-full">
                <div class="inputs_group_wrapper_3">
                    <div class="inputs_group">
                        <Label for="name" class="required">Name</Label>
                        <Input 
                            id="name"
                            v-model="form.name" 
                            type="text" 
                            placeholder="Enter product name" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.name">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="inputs_group">
                        <Label for="sku">SKU</Label>
                        <Input 
                            id="sku"
                            v-model="form.sku" 
                            type="text" 
                            placeholder="Product SKU" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.sku">
                            {{ form.errors.sku }}
                        </div>
                    </div>

                    <div class="inputs_group">
                        <Label for="barcode">Barcode</Label>
                        <Input 
                            id="barcode"
                            v-model="form.barcode" 
                            type="text" 
                            placeholder="Product Barcode" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.barcode">
                            {{ form.errors.barcode }}
                        </div>
                    </div>
                </div>

                <div class="inputs_group_wrapper_3">
                    <div class="inputs_group">
                        <Label for="buying_price">Buying Price</Label>
                        <Input 
                            id="buying_price"
                            v-model="form.buying_price"
                            type="number" 
                            min="0" 
                            step="0.01"
                            placeholder="Buying Price" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.buying_price">
                            {{ form.errors.buying_price }}
                        </div>
                    </div>

                    <div class="inputs_group">
                        <Label for="selling_price" class="required">Selling Price</Label>
                        <Input 
                            id="selling_price"
                            v-model="form.selling_price" 
                            type="number" 
                            min="0"
                            step="0.01"
                            placeholder="Selling Price" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.selling_price">
                            {{ form.errors.selling_price }}
                        </div>
                    </div>
                </div>

                <div class="inputs_group_wrapper_3">
                    <div class="inputs_group">
                        <Label for="unit_of_measurement">Unit of Measurement</Label>
                        <Input 
                            id="unit_of_measurement"
                            v-model="form.unit_of_measurement" 
                            type="text" 
                            placeholder="Unit of Measurement (eg. kg, g, l, pcs)" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.unit_of_measurement">
                            {{ form.errors.unit_of_measurement }}
                        </div>
                    </div>

                    <div class="inputs_group">
                        <Label for="current_stock">Current Stock</Label>
                        <Input 
                            id="current_stock"
                            v-model="form.current_stock" 
                            type="number" 
                            min="0"
                            step="1"
                            placeholder="Current Stock Quantity" 
                        />
                        <div class="text-sm text-red-600" v-if="form.errors.current_stock">
                            {{ form.errors.current_stock }}
                        </div>
                    </div>
                </div>

                <div class="inputs_group">
                    <div class="flex items-center gap-3">
                        <Checkbox 
                            v-model="form.is_active"
                            id="is_active"
                        />
                        <Label for="is_active">Is Active?</Label>
                    </div>
                    <div class="text-sm text-red-600" v-if="form.errors.is_active">
                        {{ form.errors.is_active }}
                    </div>
                </div>

                <div class="inputs_group">
                    <Label for="categories">Categories (Optional)</Label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                        <div
                            v-for="category in categories"
                            :key="category.id"
                            class="flex items-center space-x-2"
                        >
                            <input
                                type="checkbox"
                                :id="`category-${category.id}`"
                                :value="category.id"
                                v-model="form.categories"
                                class="rounded border-gray-300"
                            />
                            <Label :for="`category-${category.id}`" class="text-sm cursor-pointer">
                                {{ category.name }}
                            </Label>
                        </div>
                    </div>
                    <div class="text-sm text-red-600" v-if="form.errors.categories">
                        {{ form.errors.categories }}
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Updating Product...' : 'Update Product' }}
                    </Button>
                    
                    <Button 
                        type="button" 
                        variant="outline" 
                        @click="$inertia.visit(products.index().url)"
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>