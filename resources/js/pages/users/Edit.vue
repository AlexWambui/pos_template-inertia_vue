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
import { ref, watch, computed } from 'vue';

interface RoleOption {
  value: number;
  label: string;
}

interface BranchOption {
  id: number;
  name: string;
}

// Update interfaces based on your log output
interface CustomerProfile {
  id: number;
  customer_code: string;
  loyalty_points: number;
  credit_limit: string; // Note: string from database decimal
  user_id: number;
  created_at: string;
  updated_at: string;
}

interface User {
  id: number;
  name: string;
  email: string;
  role: number;
  role_label: string;
  staff_profile: null | any; // Based on your log
  customer_profile: CustomerProfile | null; // SINGULAR, not plural
  supplier_profile: null | any;
}

const props = defineProps<{
  user: User;
  branches: BranchOption[];
  role_options: RoleOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Users',
    href: users.index().url,
  },
  {
    title: 'Edit User',
    href: users.edit(props.user.id).url,
  },
];

const showRoleSpecificFields = ref(true);

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

// Debug: Log the actual data structure
console.log('User data received:', {
  user: props.user,
  customerProfile: props.user.customer_profile,
  creditLimitRaw: props.user.customer_profile?.credit_limit,
  loyaltyPointsRaw: props.user.customer_profile?.loyalty_points,
  creditLimitType: typeof props.user.customer_profile?.credit_limit,
  loyaltyPointsType: typeof props.user.customer_profile?.loyalty_points,
});

// Initialize form with user data - FIXED
const form = useForm<UserFormData>({
  name: props.user.name,
  email: props.user.email,
  password: '',
  role: props.user.role.toString(),
  // Initialize role-specific fields based on current role
  branch_id: props.user.staff_profile?.branch_id?.toString() || '',
  position: props.user.staff_profile?.position || '',
  // FIX: Use customer_profile (singular) and handle decimal string
  credit_limit: props.user.customer_profile?.credit_limit?.toString() || '0',
  loyalty_points: props.user.customer_profile?.loyalty_points?.toString() || '0',
  company_name: props.user.supplier_profile?.company_name || '',
  payment_terms: props.user.supplier_profile?.payment_terms || '',
});

const handleSubmit = () => {
  // Prepare the data according to what the controller expects
  const submitData: Record<string, any> = {
    name: form.name,
    email: form.email,
    role: form.role ? parseInt(form.role) : '',
    _method: 'PUT',
  };

  // Only include password if it's not empty
  if (form.password) {
    submitData.password = form.password;
  }

  // Add role-specific fields based on selected role
  const roleValue = parseInt(form.role);
  
  switch (roleValue) {
    case 2: // CASHIER
      submitData.branch_id = form.branch_id ? parseInt(form.branch_id) : null;
      submitData.position = form.position;
      break;
      
    case 4: // CUSTOMER
      // Convert string inputs to numbers
      submitData.credit_limit = form.credit_limit ? parseFloat(form.credit_limit) : 0;
      submitData.loyalty_points = form.loyalty_points ? parseInt(form.loyalty_points, 10) : 0;
      break;
      
    case 3: // SUPPLIER
      submitData.company_name = form.company_name;
      submitData.payment_terms = form.payment_terms;
      break;
  }

  // Debug what we're sending
  console.log('Submitting data:', submitData);

  form.transform(() => submitData).put(users.update(props.user.id).url, {
    onSuccess: () => {
      console.log('Update successful');
    },
    onError: (errors) => {
      console.log('Update errors:', errors);
    },
  });
};

// Watch role changes
watch(() => form.role, (newRole, oldRole) => {
  if (newRole !== oldRole) {
    form.branch_id = '';
    form.position = '';
    form.credit_limit = '0';
    form.loyalty_points = '0';
    form.company_name = '';
    form.payment_terms = '';
  }
});

// Payment terms options
const paymentTermsOptions = [
  { value: 'net_30', label: 'Net 30' },
  { value: 'net_60', label: 'Net 60' },
  { value: 'prepaid', label: 'Prepaid' },
  { value: 'cod', label: 'Cash on Delivery' },
];

// Computed property to check if user role has changed
const roleChanged = computed(() => {
  return form.role !== props.user.role.toString();
});

// Warning message for role change
const roleChangeWarning = computed(() => {
  if (roleChanged.value) {
    const oldRole = props.role_options.find(r => r.value.toString() === props.user.role.toString())?.label;
    const newRole = props.role_options.find(r => r.value.toString() === form.role)?.label;
    return `Warning: Changing role from ${oldRole} to ${newRole}. This will delete the existing profile and create a new one.`;
  }
  return '';
});
</script>

<template>
  <Head title="Edit User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <div class="header">
        <form @submit.prevent="handleSubmit" class="w-8/12 space-y-6">
          <!-- Role Change Warning -->
          <div v-if="roleChangeWarning" class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-yellow-700">
                  {{ roleChangeWarning }}
                </p>
              </div>
            </div>
          </div>

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
              <Label for="password">Password (Leave blank to keep current)</Label>
              <Input v-model="form.password" type="password" placeholder="Enter new password" />
              <div class="text-sm text-gray-500">Only fill if you want to change the password</div>
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

          <div class="flex space-x-4">
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Updating...' : 'Update User' }}
            </Button>
            <Button 
              type="button" 
              variant="outline" 
              :href="users.index().url"
              as="a"
            >
              Cancel
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>