<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import products from '@/routes/products';

const props = defineProps<{
    product: {
        id: number
        name: string
        price: number
        description: string | null
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Edit Product',
        href: products.edit(props.product.id).url,
    },
];

const form = useForm({
    name: props.product.name,
    price: props.product.price,
    description: props.product.description ?? '',
})

const handleSubmit = () => {
     form.patch(products.update(props.product.id).url);
};


</script>

<template>
    <Head title="Edit a Product" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="header">
                <form @submit.prevent="handleSubmit" class="w-8/12">
                    <div class="inputs_group">
                        <Label for="name">Name</Label>
                        <Input v-model="form.name" type="text" placeholder="Enter product name" />
                        <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>

                    <div class="inputs_group">
                        <Label for="price">Price</Label>
                        <Input v-model="form.price" type="number" placeholder="Price" />
                        <div class="text-sm text-red-600" v-if="form.errors.price">{{ form.errors.price }}</div>
                    </div>

                    <div class="inputs_group">
                        <Label for="description">Description</Label>
                        <Input v-model="form.description" type="text" placeholder="Describe the product" />
                        <div class="text-sm text-red-600" v-if="form.errors.description">{{ form.errors.description }}</div>
                    </div>

                    <Button type="submit" :disabled="form.processing">Edit Product</Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
