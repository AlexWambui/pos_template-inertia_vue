<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ChevronRight, ChevronDown } from 'lucide-vue-next';
import DeleteConfirmationDialog from '@/components/ui/delete-dialog/DeleteConfirmation.vue';
import productCategories from '@/routes/product-categories';

interface Category {
    id: number;
    name: string;
    parent_id: number | null;
    is_active?: boolean;
    children?: Category[];
}

interface Props {
    categories: Category[];
    level?: number;
    parentNumber?: string; // The parent's number (e.g., "1", "2")
    index?: number; // The current index at this level
}

const props = defineProps<Props>();
const level = props.level || 0;
const parentNumber = props.parentNumber || '';
const startIndex = props.index || 1;

// Default expanded state
const expanded = ref<Record<number, boolean>>({});

// Initialize expanded to true for all categories with children
props.categories.forEach(category => {
    if (category.children && category.children.length > 0) {
        expanded.value[category.id] = true;
    }
});

const toggleExpand = (categoryId: number) => {
    expanded.value[categoryId] = !expanded.value[categoryId];
};

const hasChildren = (category: Category): boolean => {
    return !!(category.children && category.children.length > 0);
};

// Simple indentation
const indentStyle = () => {
    return { paddingLeft: `${level * 32}px` };
};

// Generate hierarchical number (1, 1.1, 1.2, 2, 2.1, etc.)
const getHierarchicalNumber = (index: number): string => {
    if (!parentNumber) {
        return index.toString();
    }
    return `${parentNumber}.${index}`;
};

// Generate Roman numerals for level 2+ (optional - use either this or the hierarchical numbers)
const getRomanNumeral = (num: number): string => {
    const roman = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x'];
    return roman[num - 1] || num.toString();
};
</script>

<template>
    <template v-for="(category, idx) in categories" :key="category.id">
        <tr class="hover:bg-gray-50 border-b group">
            <td class="py-3 px-4 font-medium text-gray-900 w-[80px] align-top">
                <!-- Hierarchical Numbering -->
                <span v-if="level === 0">
                    {{ startIndex + idx }}.
                </span>
                <span v-else-if="level === 1" class="text-gray-600">
                    {{ getRomanNumeral(idx + 1) }}.
                </span>
                <span v-else class="text-gray-500 text-xs">
                    {{ getHierarchicalNumber(idx + 1) }}
                </span>
            </td>
            <td class="py-3 px-4">
                <div class="flex items-center" :style="indentStyle()">
                    <!-- Expand/Collapse Button -->
                    <button
                        v-if="hasChildren(category)"
                        @click="toggleExpand(category.id)"
                        class="p-1 mr-1 hover:bg-gray-100 rounded transition-colors"
                        type="button"
                    >
                        <ChevronRight v-if="!expanded[category.id]" class="w-4 h-4 text-gray-500" />
                        <ChevronDown v-else class="w-4 h-4 text-gray-500" />
                    </button>
                    <span v-else class="w-6 mr-1"></span>
                    
                    <!-- Category Name -->
                    <span :class="[
                        'text-sm',
                        category.is_active ? 'text-gray-900' : 'text-gray-400',
                        hasChildren(category) ? 'font-medium' : ''
                    ]">
                        {{ category.name }}
                    </span>
                    
                    <!-- Inactive Badge -->
                    <span v-if="!category.is_active" 
                          class="ml-2 px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">
                        Inactive
                    </span>
                </div>
            </td>
            <td class="py-3 px-4 text-center">
                <div class="flex justify-center space-x-2">
                    <Link :href="productCategories.edit(category.id).url" 
                          class="text-blue-600 hover:text-blue-800 hover:underline text-sm">
                        Edit
                    </Link>
                    <span class="text-gray-300">|</span>
                    <DeleteConfirmationDialog 
                        :url="productCategories.destroy(category.id).url" 
                        title="Delete Category?" 
                        description="This product category will be deleted permanently!" 
                        confirm-text="Delete Category">
                        <template #trigger>
                            <button class="text-red-600 hover:text-red-800 hover:underline text-sm">
                                Delete
                            </button>
                        </template>
                    </DeleteConfirmationDialog>
                </div>
            </td>
        </tr>
        
        <!-- Children Rows -->
        <template v-if="expanded[category.id] && hasChildren(category)">
            <CategoryTree 
                :categories="category.children as Category[]" 
                :level="level + 1"
                :parent-number="getHierarchicalNumber(idx + 1)"
                :index="1"
            />
        </template>
    </template>
</template>