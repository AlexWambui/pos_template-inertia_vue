<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import FormError from '@/components/custom/FormError.vue';
import products from '@/routes/products';

const page = usePage();

interface Category {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    sku: string | null;
    barcode: string | null;
    buying_price: number | null;
    selling_price: number;
    current_stock: number;
    weight_value: number | null;
    weight_unit: string;
    product_category_id: number | null;
    is_active: boolean;
}

interface Props {
    product: Product;
    categories: Category[];
}

const props = defineProps<Props>();

page.props.breadcrumbs = [
    { title: 'Products', href: products.index().url },
    { title: 'Edit Product', href: products.edit(props.product.id).url },
];

const weightUnits = [
    { value: 'kg', label: 'Kilograms (kg)' },
    { value: 'g', label: 'Grams (g)' },
    { value: 'lbs', label: 'Pounds (lbs)' },
    { value: 'pcs', label: 'Pieces (pcs)' },
    { value: 'oz', label: 'Ounces (oz)' },
];

const form = useForm({
    name: props.product.name,
    sku: props.product.sku || '',
    barcode: props.product.barcode || '',
    buying_price: props.product.buying_price,
    selling_price: props.product.selling_price,
    current_stock: props.product.current_stock,
    weight_value: props.product.weight_value,
    weight_unit: props.product.weight_unit || '',
    product_category_id: props.product.product_category_id,
    is_active: props.product.is_active,
    _method: 'put', // This will be used for method spoofing
});

const handleSubmit = () => {
    form.put(products.update(props.product.id).url);
};
</script>

<template>
    <Head :title="`Edit Product: ${product.name}`" />

    <div class="AppContainer">
        <div class="product_edit_form">
            <form @submit.prevent="handleSubmit">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow mb-6">
                    <div class="inputs_group_wrapper_3">
                        <div class="inputs_group">
                            <Label for="name" class="required">Name</Label>
                            <Input v-model="form.name" type="text" placeholder="Enter product name" />
                            <FormError :error="form.errors.name" />
                        </div>

                        <div class="inputs_group">
                            <Label for="sku">SKU</Label>
                            <Input v-model="form.sku" type="text" placeholder="Product SKU" />
                            <FormError :error="form.errors.sku" />
                        </div>

                        <div class="inputs_group">
                            <Label for="barcode">Barcode</Label>
                            <Input v-model="form.barcode" type="text" placeholder="Product Barcode" />
                            <FormError :error="form.errors.barcode" />
                        </div>
                    </div>
                    
                    <div class="inputs_group_wrapper_3">
                        <div class="inputs_group">
                            <Label for="category">Category</Label>
                            <Select v-model="form.product_category_id">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select category" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem 
                                            v-for="category in props.categories" 
                                            :key="category.id" 
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <FormError :error="form.errors.product_category_id" />
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
                            <FormError :error="form.errors.current_stock" />
                        </div>
                    </div>

                    <div class="inputs_group_wrapper_3">
                        <div class="inputs_group">
                            <Label for="buying_price">Buying Price</Label>
                            <Input 
                                v-model="form.buying_price"
                                type="number" 
                                step="0.01"
                                min="0" 
                                placeholder="Buying Price" />
                            <FormError :error="form.errors.buying_price" />
                        </div>

                        <div class="inputs_group">
                            <Label for="selling_price" class="required">Selling Price</Label>
                            <Input 
                                v-model="form.selling_price" 
                                type="number" 
                                step="0.01"
                                min="0"
                                placeholder="Selling Price" />
                            <FormError :error="form.errors.selling_price" />
                        </div>
                    </div>

                    <div class="inputs_group_wrapper_3">
                        <div class="inputs_group">
                            <Label for="weight_value">Weight Value</Label>
                            <Input 
                                v-model="form.weight_value" 
                                type="number" 
                                step="0.01"
                                min="0"
                                placeholder="Weight Value" />
                            <FormError :error="form.errors.weight_value" />
                        </div>

                        <div class="inputs_group">
                            <Label for="weight_unit">Weight/Size Unit</Label>
                            <Select v-model="form.weight_unit">
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Select unit" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectItem 
                                            v-for="unit in weightUnits" 
                                            :key="unit.value" 
                                            :value="unit.value"
                                        >
                                            {{ unit.label }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <FormError :error="form.errors.weight_unit" />
                        </div>
                    </div>

                    <div class="inputs_group mb-4">
                        <div class="flex items-center gap-3">
                            <Checkbox v-model="form.is_active" id="is_active" />
                            <Label for="is_active">Is Active?</Label>
                        </div>
                        <FormError :error="form.errors.is_active" />
                    </div>

                    <div class="flex justify-between items-center pt-4">
                        <div class="flex space-x-3">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                :loading="form.processing"
                            >
                                {{ form.processing ? 'Updating Product...' : 'Update Product' }}
                            </Button>

                            <Link :href="products.index().url">
                                <Button type="button" variant="outline" :disabled="form.processing">
                                    Cancel
                                </Button>
                            </Link>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>