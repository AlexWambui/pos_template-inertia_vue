<template>
  <AuthenticatedLayout>
    <Head title="Open Shift" />
    
    <div class="py-12">
      <div class="max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Open Shift</h2>
            
            <div v-if="lastShift" class="mb-6 p-4 bg-gray-50 rounded-lg">
              <h3 class="font-semibold mb-2">Last Shift Summary</h3>
              <p>Closed at: {{ formatDate(lastShift.closed_at) }}</p>
              <p>Closing Cash: {{ formatCurrency(lastShift.closing_cash) }}</p>
            </div>
            
            <form @submit.prevent="submit">
              <div class="mb-6">
                <InputLabel for="opening_cash" value="Opening Cash *" />
                <TextInput
                  id="opening_cash"
                  type="number"
                  class="mt-1 block w-full"
                  v-model="form.opening_cash"
                  required
                  step="0.01"
                  min="0"
                  autofocus
                  placeholder="0.00"
                />
                <InputError class="mt-2" :message="form.errors.opening_cash" />
              </div>
              
              <div class="flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Open Shift
                </PrimaryButton>
                
                <Link 
                  :href="route('logout')" 
                  method="post"
                  class="text-sm text-gray-600 hover:text-gray-900"
                >
                  Logout
                </Link>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { formatCurrency } from '@/utils/formatters';

const props = defineProps({
  lastShift: Object,
});

const form = useForm({
  opening_cash: '',
});

const submit = () => {
  form.post(route('shifts.store'));
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};
</script>