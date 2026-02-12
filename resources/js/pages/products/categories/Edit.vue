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

interface ProductCategory {
    id: number;
    name: string;
    parent_id: number | null;
    is_active: boolean;
    sort_order: number;
}

interface ParentCategory {
    id: number;
    name: string;
}

interface Props {
    category: ProductCategory;
    parentCategories: ParentCategory[];
    hasChildren: boolean;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Product Categories', href: '/product-categories' },
    { title: 'Edit Category', href: `/product-categories/${props.category.id}/edit` },
];

const form = useForm({
    name: props.category.name,
    parent_id: props.category.parent_id,
    is_active: props.category.is_active,
    sort_order: props.category.sort_order,
});

const handleSubmit = () => {
    form.put(`/product-categories/${props.category.id}`);
};

// Helper to convert null to undefined for select
const selectedParentId = form.parent_id === null ? undefined : String(form.parent_id);

const handleParentChange = (value: string) => {
    form.parent_id = value === 'null' ? null : Number(value);
};
</script>

<template>
    <Head :title="`Edit ${category.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 max-w-2xl mx-auto">
            <div class="bg-white rounded-lg border shadow-sm p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold">Edit Category: {{ category.name }}</h2>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Category Name -->
                    <div class="space-y-2">
                        <Label for="name" class="required">Category Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Enter category name"
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
                        <Select 
                            :model-value="selectedParentId"
                            @update:model-value="handleParentChange"
                        >
                            <SelectTrigger id="parent_id">
                                <SelectValue :placeholder="form.parent_id === null ? '— No Parent (Root Category) —' : undefined" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectLabel>Parent Categories</SelectLabel>
                                    <SelectItem value="null">
                                        — No Parent (Root Category) —
                                    </SelectItem>
                                    <SelectItem 
                                        v-for="cat in props.parentCategories" 
                                        :key="cat.id" 
                                        :value="String(cat.id)"
                                        :disabled="cat.id === category.id"
                                    >
                                        {{ cat.name }}
                                        <span v-if="cat.id === category.id" class="ml-2 text-xs text-gray-500">
                                            (cannot be its own parent)
                                        </span>
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
                            :checked="form.is_active"
                            @update:checked="form.is_active = $event"
                        />
                        <Label for="is_active" class="!mb-0">
                            Active (visible in POS and available for products)
                        </Label>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <Link href="/product-categories">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Updating...' : 'Update Category' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.required:after {
    content: " *";
    color: red;
}
</style>