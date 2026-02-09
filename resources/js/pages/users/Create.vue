<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Button from '@/components/ui/button/Button.vue';
import Label from '@/components/ui/label/Label.vue';
import Input from '@/components/ui/input/Input.vue';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import users from '@/routes/users';
import { ref, watch } from 'vue';

interface RoleOption {
  value: number;
  label: string;
}

interface BranchOption {
  id: number;
  name: string;
}

const props = defineProps<{
  branches: BranchOption[];
  role_options: RoleOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Create User',
    href: users.create().url,
  },
];

const showRoleSpecificFields = ref(false);

// Define a proper type for the form
interface UserFormData {
  name: string;
  email: string;
  password: string;
  role: string;
  branch_id: string;
  position: string;
  credit_limit: string;
  loyalty_points: string;
  company_name: string;
  payment_terms: string;
}

const form = useForm<UserFormData>({
  name: '',
  email: '',
  password: '',
  role: '',
  branch_id: '',
  position: '',
  credit_limit: '',
  loyalty_points: '',
  company_name: '',
  payment_terms: '',
});

const handleSubmit = () => {
  // Prepare the data according to what the controller expects
  const submitData: Record<string, any> = {
    name: form.name,
    email: form.email,
    password: form.password,
    role: form.role ? parseInt(form.role) : '',
  };

  // Add role-specific fields based on selected role
  const roleValue = parseInt(form.role);
  
  switch (roleValue) {
    case 2: // CASHIER
      submitData.branch_id = form.branch_id ? parseInt(form.branch_id) : null;
      submitData.position = form.position;
      break;
      
    case 4: // CUSTOMER
      // Convert string inputs to numbers (null if empty)
      submitData.credit_limit = form.credit_limit ? parseFloat(form.credit_limit) : null;
      submitData.loyalty_points = form.loyalty_points ? parseInt(form.loyalty_points, 10) : null;
      break;
      
    case 3: // SUPPLIER
      submitData.company_name = form.company_name;
      submitData.payment_terms = form.payment_terms;
      break;
  }

  // Use transform to send the prepared data
  form.transform(() => submitData).post(users.store().url, {
    onSuccess: () => {
      form.reset();
    },
  });
};

// Watch role changes to show/hide role-specific fields
watch(() => form.role, (newRole) => {
  showRoleSpecificFields.value = !!newRole;
  
  // Clear role-specific fields when role changes
  form.branch_id = '';
  form.position = '';
  form.credit_limit = '';
  form.loyalty_points = '';
  form.company_name = '';
  form.payment_terms = '';
});

// Payment terms options for supplier
const paymentTermsOptions = [
  { value: 'net_30', label: 'Net 30' },
  { value: 'net_60', label: 'Net 60' },
  { value: 'prepaid', label: 'Prepaid' },
  { value: 'cod', label: 'Cash on Delivery' },
];
</script>

<template>
  <Head title="Create a User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <div class="header">
        <form @submit.prevent="handleSubmit" class="w-8/12 space-y-6">
          <!-- Basic Information -->
          <div class="space-y-4">
            <h2 class="text-lg font-semibold">Basic Information</h2>
            
            <div class="inputs_group">
              <Label for="name">Name</Label>
              <Input v-model="form.name" type="text" placeholder="Name of the user" />
              <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
            </div>

            <div class="inputs_group">
              <Label for="email">Email</Label>
              <Input v-model="form.email" type="email" placeholder="Email Address" />
              <div class="text-sm text-red-600" v-if="form.errors.email">{{ form.errors.email }}</div>
            </div>

            <div class="inputs_group">
              <Label for="password">Password</Label>
              <Input v-model="form.password" type="password" placeholder="Choose a strong password" />
              <div class="text-sm text-red-600" v-if="form.errors.password">{{ form.errors.password }}</div>
            </div>

            <div class="inputs_group">
              <Label for="role">Role</Label>
              <Select v-model="form.role">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select User's Role" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectLabel>Roles</SelectLabel>
                    <SelectItem 
                      v-for="role in role_options" 
                      :key="role.value" 
                      :value="role.value.toString()"
                    >
                      {{ role.label }}
                    </SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
              <div class="text-sm text-red-600" v-if="form.errors.role">{{ form.errors.role }}</div>
            </div>
          </div>

          <!-- Role-specific fields -->
          <div v-if="showRoleSpecificFields" class="space-y-6">
            <div v-if="form.role === '2'"> <!-- CASHIER -->
              <h3 class="text-md font-medium mb-4">Cashier Details</h3>
              
              <div class="inputs_group">
                <Label for="branch_id">Branch</Label>
                <Select v-model="form.branch_id">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select Branch" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Branches</SelectLabel>
                      <SelectItem 
                        v-for="branch in branches" 
                        :key="branch.id" 
                        :value="branch.id.toString()"
                      >
                        {{ branch.name }}
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <div class="text-sm text-red-600" v-if="form.errors.branch_id">{{ form.errors.branch_id }}</div>
              </div>

              <div class="inputs_group">
                <Label for="position">Position</Label>
                <Select v-model="form.position">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select Position" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Positions</SelectLabel>
                      <SelectItem value="Cashier">Cashier</SelectItem>
                      <SelectItem value="Manager">Manager</SelectItem>
                      <SelectItem value="Team Leader">Team Leader</SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <div class="text-sm text-red-600" v-if="form.errors.position">{{ form.errors.position }}</div>
              </div>
            </div>

            <div v-else-if="form.role === '4'"> <!-- CUSTOMER -->
              <h3 class="text-md font-medium mb-4">Customer Details</h3>
              
              <div class="inputs_group">
                <Label for="credit_limit">Credit Limit</Label>
                <Input 
                  :model-value="form.credit_limit"
                  @update:model-value="(value: string | number) => form.credit_limit = String(value)"
                  type="number" 
                  placeholder="0.00" 
                  step="0.01"
                  min="0"
                />
                <div class="text-sm text-red-600" v-if="form.errors.credit_limit">{{ form.errors.credit_limit }}</div>
              </div>

              <div class="inputs_group">
                <Label for="loyalty_points">Loyalty Points</Label>
                <Input 
                  :model-value="form.loyalty_points"
                  @update:model-value="(value: string | number) => form.loyalty_points = String(value)"
                  type="number" 
                  placeholder="0" 
                  min="0"
                />
                <div class="text-sm text-red-600" v-if="form.errors.loyalty_points">{{ form.errors.loyalty_points }}</div>
              </div>
            </div>

            <div v-else-if="form.role === '3'"> <!-- SUPPLIER -->
              <h3 class="text-md font-medium mb-4">Supplier Details</h3>
              
              <div class="inputs_group">
                <Label for="company_name">Company Name</Label>
                <Input v-model="form.company_name" type="text" placeholder="Company Name" />
                <div class="text-sm text-red-600" v-if="form.errors.company_name">{{ form.errors.company_name }}</div>
              </div>

              <div class="inputs_group">
                <Label for="payment_terms">Payment Terms</Label>
                <Select v-model="form.payment_terms">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select Payment Terms" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectGroup>
                      <SelectLabel>Payment Terms</SelectLabel>
                      <SelectItem 
                        v-for="term in paymentTermsOptions" 
                        :key="term.value" 
                        :value="term.value"
                      >
                        {{ term.label }}
                      </SelectItem>
                    </SelectGroup>
                  </SelectContent>
                </Select>
                <div class="text-sm text-red-600" v-if="form.errors.payment_terms">{{ form.errors.payment_terms }}</div>
              </div>
            </div>
          </div>

          <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Creating...' : 'Create User' }}
          </Button>
        </form>
      </div>
    </div>
  </AppLayout>
</template>