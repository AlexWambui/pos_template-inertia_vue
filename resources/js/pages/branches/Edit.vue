<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import branches from '@/routes/branches';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import Button from '@/components/ui/button/Button.vue';
import FormError from '@/components/ui/form-error/FormError.vue';

const props = defineProps<{
    branch: {
        id: number;
        name: string;
        code: string;
        phone: string;
        email: string,
        address: string;
        city: string;
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Branches', href: branches.index.url() },
    { title: 'Edit Branch', href: branches.edit.url(props.branch.id) }
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
  name: props.branch.name,
  code: props.branch.code,
  phone: props.branch.phone,
  email: props.branch.email,
  address: props.branch.address,
  city: props.branch.city,
});

const handleSubmit = () => {
    form.put(branches.update.url(props.branch.id));
};
</script>

<template>
    <Head title="Edit Branch: ${branch.name}" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="branches_edit_form w-6/12">
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
                        {{ form.processing ? 'Updating Branch...' : 'Update Branch' }}
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>