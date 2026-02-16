<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import branches from '@/routes/branches';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import FormError from '@/components/ui/form-error/FormError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Create Branch', href: branches.create.url() }
];

interface BranchFormData {
    name: string;
    code: string;
    phone: string;
    email: string,
    address: string;
    city: string;
};

const form = useForm<BranchFormData>({
  name: '',
  code: '',
  phone: '',
  email: '',
  address: '',
  city: '',
});

const handleSubmit = () => {
    form.post(branches.store.url());
};
</script>

<template>
    <Head title="Create Branch" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="branches_create_form w-6/12">
                <form @submit.prevent="handleSubmit">
                    <div class="inputs_group">
                        <Label for="name" class="required">Name</Label>
                        <Input v-model="form.name" type="text" placeholder="Name of the branch" />
                        <FormError :error="form.errors.name" />
                    </div>

                    <div class="inputs_group">
                        <Label for="code" class="required">Branch Code</Label>
                        <Input v-model="form.code" type="text" placeholder="Branch Code (eg. BR-0002)" />
                        <FormError :error="form.errors.code" />
                    </div>

                    <div class="inputs_group">
                        <Label for="phone">Phone Number</Label>
                        <Input v-model="form.phone" type="text" placeholder="Phone Number" />
                        <FormError :error="form.errors.phone" />
                    </div>

                    <div class="inputs_group">
                        <Label for="email">Email Address</Label>
                        <Input v-model="form.email" type="text" placeholder="Email Address" />
                        <FormError :error="form.errors.email" />
                    </div>

                    <div class="inputs_group">
                        <Label for="address">Address</Label>
                        <Input v-model="form.address" type="text" placeholder="Address" />
                        <FormError :error="form.errors.address" />
                    </div>

                    <div class="inputs_group">
                        <Label for="city">City</Label>
                        <Input v-model="form.city" type="text" placeholder="City" />
                        <FormError :error="form.errors.city" />
                    </div>

                    <Button type="submit" :disabled="form.processing">
                        {{ form.processing ? 'Creating Branch...' : 'Create Branch' }}
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>