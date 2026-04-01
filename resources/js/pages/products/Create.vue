<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import FormError from '@/components/custom/FormError.vue';
import products from '@/routes/products';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Products', href: products.index().url },
            { title: 'Create Product', href: products.create().url },
        ],
    },
});

interface Category {
    id: number;
    name: string;
};

interface Props {
    categories: Category[];
};

const props = defineProps<Props>();

const weightUnits = [
    { value: 'kg', label: 'Kilograms (kg)' },
    { value: 'g', label: 'Grams (g)' },
    { value: 'lbs', label: 'Pounds (lbs)' },
    { value: 'pcs', label: 'Pieces (pcs)' },
    { value: 'oz', label: 'Ounces (oz)' },
]

const form = useForm({
    name: '',
    sku: '',
    barcode: '',
    buying_price: null as number | null,
    selling_price: null as number | null,
    current_stock: null as number | null,
    weight_value: null as number | null,
    weight_unit: '',
    product_category_id: null as number | null,
    is_active: true,
});

const handleSubmit = () => {
    form.post(products.store.url());
};
</script>

<template>
    <Head title="Create Product" />

    <div class="AppContainer">
        <div class="product_create_form">
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
                            <Label for="current_stock">Initial Stock</Label>
                            <Input 
                                id="current_stock"
                                v-model="form.current_stock" 
                                type="number" 
                                min="0"
                                step="1"
                                placeholder="Initial Stock Quantity" 
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
                                min="0" 
                                placeholder="Buying Price" />
                            <FormError :error="form.errors.buying_price" />
                        </div>

                        <div class="inputs_group">
                            <Label for="selling_price" class="required">Selling Price</Label>
                            <Input v-model="form.selling_price" type="number" placeholder="Selling Price" />
                            <FormError :error="form.errors.selling_price" />
                        </div>
                    </div>

                    <div class="inputs_group_wrapper_3">
                        <div class="inputs_group">
                            <Label for="weight_value">Weight Value</Label>
                            <Input v-model="form.weight_value" type="number" placeholder="Weight Value" />
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

                    <div class="inputs_group">
                        <div class="flex items-center gap-3">
                            <Checkbox v-model="form.is_active" id="is_active" />
                            <Label for="is_active">Is Active?</Label>
                        </div>
                        <FormError :error="form.errors.is_active" />
                    </div>

                    <div class="flex justify-start space-x-3 pt-4">
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            :loading="form.processing"
                        >
                            {{ form.processing ? 'Creating Product...' : 'Create Product' }}
                        </Button>

                        <Link :href="products.index().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>