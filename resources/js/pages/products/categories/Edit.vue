<script setup lang="ts">
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import Checkbox from '@/components/ui/checkbox/Checkbox.vue';
import FormError from '@/components/custom/FormError.vue';
import product_categories from '@/routes/product-categories';

const page = usePage();

interface Props {
    category: {
        id: number;
        name: string;
        is_active: boolean;
    }
};

const props = defineProps<Props>();

page.props.breadcrumbs = [
    { title: 'Categories', href: product_categories.index().url },
    { title: 'Edit Category', href: product_categories.edit(props.category.id).url },
];

const form = useForm({
    name: props.category.name,
    is_active: props.category.is_active,
});

const handleSubmit = () => {
    form.put(product_categories.update(props.category.id).url);
};
</script>

<template>
    <Head title="Edit Category" />

    <div class="AppContainer">
        <div class="category_edit_form w-6/12">
            <form @submit.prevent="handleSubmit">
                <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow mb-6">
                    <div class="inputs_group">
                        <Label for="name" class="required">Name</Label>
                        <Input v-model="form.name" type="text" placeholder="Category Name" />
                        <FormError :error="form.errors.name" />
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
                            {{ form.processing ? 'Updating Category...' : 'Update Category' }}
                        </Button>

                        <Link :href="product_categories.index().url">
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