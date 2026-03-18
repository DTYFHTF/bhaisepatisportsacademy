<script setup lang="ts">
const emit = defineEmits<{
  submit: [data: { reason: string; newSize?: string; note?: string }]
}>()

const reasons = [
  'Wrong size',
  'Defective product',
  'Changed my mind',
  'Other',
]

const form = reactive({
  reason: '',
  newSize: '',
  note: '',
})

const showSizeSelect = computed(() => form.reason === 'Wrong size')

function handleSubmit() {
  if (!form.reason) return
  emit('submit', {
    reason: form.reason,
    newSize: showSizeSelect.value ? form.newSize : undefined,
    note: form.note || undefined,
  })
}
</script>

<template>
  <div class="space-y-4">
    <h3 class="text-heading-md">Request Exchange</h3>

    <div>
      <span class="text-label text-ink-muted mb-2 block">Reason</span>
      <select
        v-model="form.reason"
        class="w-full border border-border bg-transparent px-4 py-3 text-sm focus:outline-none focus:border-ink"
      >
        <option value="" disabled>Select a reason</option>
        <option v-for="reason in reasons" :key="reason" :value="reason">
          {{ reason }}
        </option>
      </select>
    </div>

    <div v-if="showSizeSelect">
      <UiAppInput
        v-model="form.newSize"
        label="New Size"
        placeholder="M"
      />
    </div>

    <UiAppInput
      v-model="form.note"
      label="Additional Note (optional)"
      placeholder="Any details that might help"
    />

    <UiAppButton
      :disabled="!form.reason"
      @click="handleSubmit"
    >
      Submit Request
    </UiAppButton>
  </div>
</template>
