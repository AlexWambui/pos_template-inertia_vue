<template>
  <AuthenticatedLayout>
    <Head title="Close Shift" />
    
    <div class="py-12">
      <div class="max-w-md mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <h2 class="text-2xl font-bold mb-6">Close Shift</h2>
            
            <div class="mb-6 space-y-2">
              <p><span class="font-semibold">Opened At:</span> {{ formatDate(shift.opened_at) }}</p>
              <p><span class="font-semibold">Opening Cash:</span> {{ formatCurrency(shift.opening_cash) }}</p>
              <p><span class="font-semibold">Expected Cash:</span> {{ formatCurrency(expectedCash) }}</p>
            </div>
            
            <form @submit.prevent="submit">
              <div class="mb-6">
                <InputLabel for="closing_cash" value="Actual Closing Cash *" />
                <TextInput
                  id="closing_cash"
                  type="number"
                  class="mt-1 block w-full"
                  v-model="form.closing_cash"
                  required
                  step="0.01"
                  min="0"
                  autofocus
                  placeholder="0.00"
                />
                <InputError class="mt-2" :message="form.errors.closing_cash" />
              </div>
              
              <div class="mb-6">
                <InputLabel for="notes" value="Notes (Optional)" />
                <textarea
                  id="notes"
                  class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                  v-model="form.notes"
                  rows="3"
                  placeholder="Any notes about the shift..."
                ></textarea>
                <InputError class="mt-2" :message="form.errors.notes" />
              </div>
              
              <div class="flex items-center justify-between">
                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Close Shift
                </PrimaryButton>
                
                <Link 
                  :href="route('pos.index')" 
                  class="text-sm text-gray-600 hover:text-gray-900"
                >
                  Back to POS
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
  shift: Object,
  expectedCash: Number,
});

const form = useForm({
  closing_cash: props.expectedCash?.toFixed(2) || '0.00',
  notes: '',
});

const submit = () => {
  form.put(route('shifts.update'));
};

const formatDate = (date) => {
  return new Date(date).toLocaleString();
};
</script>