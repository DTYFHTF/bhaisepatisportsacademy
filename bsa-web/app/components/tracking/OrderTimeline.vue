<script setup lang="ts">
import type { OrderStatusHistory, OrderStatus } from '~/types/order'
import { ORDER_STATUS_LABELS } from '~/types/order'
import { formatDistanceToNow, format } from 'date-fns'

interface Props {
  history: OrderStatusHistory[]
  currentStatus: OrderStatus
  courier?: string | null
  courierTrackingId?: string | null
  courierTrackingUrl?: string | null
}

const props = defineProps<Props>()

const courierLabel = computed(() => {
  const labels: Record<string, string> = {
    PATHAO: 'Pathao Parcel',
    NCM: 'Nepal Can Move',
    DHULO: 'Dhulo',
    OTHER: 'Other',
  }
  return props.courier ? (labels[props.courier] || props.courier) : null
})

const statusOrder: OrderStatus[] = [
  'CONFIRMED',
  'PAYMENT_CONFIRMED',
  'PACKED',
  'DISPATCHED',
  'OUT_FOR_DELIVERY',
  'DELIVERED',
]

function formatTime(dateString: string): string {
  const date = new Date(dateString)
  const now = new Date()
  const diffHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60)

  if (diffHours < 24) {
    return formatDistanceToNow(date, { addSuffix: true })
  }
  return format(date, 'd MMM · h:mm a')
}
</script>

<template>
  <div class="space-y-0">
    <div
      v-for="(step, index) in statusOrder"
      :key="step"
      class="flex gap-4"
    >
      <!-- Timeline indicator -->
      <div class="flex flex-col items-center">
        <div
          :class="[
            'h-3 w-3 rounded-full border-2',
            history.some((h) => h.status === step)
              ? step === currentStatus
                ? 'border-ink bg-ink animate-pulse'
                : 'border-ink bg-ink'
              : 'border-border bg-transparent',
          ]"
        />
        <div
          v-if="index < statusOrder.length - 1"
          :class="[
            'w-0.5 flex-1 min-h-[32px]',
            history.some((h) => h.status === step) ? 'bg-ink' : 'bg-border',
          ]"
        />
      </div>

      <!-- Content -->
      <div class="pb-6">
        <p
          :class="[
            'text-sm',
            history.some((h) => h.status === step) ? 'text-ink font-medium' : 'text-ink-faint',
          ]"
        >
          {{ ORDER_STATUS_LABELS[step] }}
        </p>
        <p
          v-if="history.find((h) => h.status === step)"
          class="text-xs text-ink-muted mt-0.5"
        >
          {{ formatTime(history.find((h) => h.status === step)!.changedAt) }}
        </p>
        <p
          v-if="history.find((h) => h.status === step)?.note"
          class="text-xs text-ink-muted mt-0.5"
        >
          {{ history.find((h) => h.status === step)!.note }}
        </p>

        <!-- Courier info shown at DISPATCHED step -->
        <div
          v-if="step === 'DISPATCHED' && history.some((h) => h.status === 'DISPATCHED') && courierLabel"
          class="mt-2 rounded-md border border-border bg-surface px-3 py-2 text-xs space-y-1"
        >
          <p class="font-medium text-ink">Shipped via {{ courierLabel }}</p>
          <p v-if="courierTrackingId" class="text-ink-muted">
            Tracking: <span class="font-mono">{{ courierTrackingId }}</span>
          </p>
          <a
            v-if="courierTrackingUrl"
            :href="courierTrackingUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-block text-accent underline hover:text-ink transition"
          >
            Track on courier site &rarr;
          </a>
        </div>
      </div>
    </div>
  </div>
</template>
