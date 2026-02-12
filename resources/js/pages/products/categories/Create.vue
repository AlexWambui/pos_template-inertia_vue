<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import productCategories from '@/routes/product-categories';

interface ParentCategory {
    id: number;
    name: string;
}

interface Props {
    parentCategories: ParentCategory[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Product Categories', href: productCategories.index().url },
    { title: 'Create Category', href: productCategories.create().url },
];

const form = useForm({
    name: '',
    parent_id: null as number | null,
    is_active: true,
    sort_order: 0,
});

const handleSubmit = () => {
    form.post(productCategories.store().url);
};
</script>

<template>
    <Head title="Create Product Category" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 max-w-2xl mx-auto">
            <div class="bg-white rounded-lg border shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-6">Create New Category</h2>

                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Category Name -->
                    <div class="space-y-2">
                        <Label for="name" class="required">Category Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Enter category name (e.g., Drinks, Snacks, Electronics)"
                            :error="form.errors.name"
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Parent Category (Optional) -->
                    <div class="space-y-2">
                        <Label for="parent_id">Parent Category (Optional)</Label>
                        <Select v-model="form.parent_id">
                            <SelectTrigger id="parent_id">
                                <SelectValue placeholder="Select a parent category" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Parent Categories</SelectLabel>
                                    <SelectItem :value="null">
                                        — No Parent (Root Category) —
                                    </SelectItem>
                                    <SelectItem 
                                        v-for="cat in props.parentCategories" 
                                        :key="cat.id" 
                                        :value="cat.id"
                                    >
                                        {{ cat.name }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <p class="text-sm text-gray-500">
                            Leave empty to make this a top-level category
                        </p>
                        <p v-if="form.errors.parent_id" class="text-sm text-red-600">
                            {{ form.errors.parent_id }}
                        </p>
                    </div>

                    <!-- Sort Order -->
                    <div class="space-y-2">
                        <Label for="sort_order">Sort Order</Label>
                        <Input
                            id="sort_order"
                            v-model="form.sort_order"
                            type="number"
                            min="0"
                            placeholder="0"
                            :error="form.errors.sort_order"
                        />
                        <p class="text-sm text-gray-500">
                            Lower numbers appear first
                        </p>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-center space-x-3">
                        <Checkbox
                            id="is_active"
                            v-model:checked="form.is_active"
                        />
                        <Label for="is_active" class="!mb-0">
                            Active (visible in POS and available for products)
                        </Label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <Link :href="productCategories.index().url">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            :loading="form.processing"
                        >
                            {{ form.processing ? 'Creating...' : 'Create Category' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>