import { defineStore } from 'pinia'
import type { BookingItem, BookingSlot } from '~/types/service'

export const useBookingStore = defineStore('booking', {
  state: () => ({
    items: [] as BookingItem[],
    slot: null as BookingSlot | null,
    isOpen: false,
  }),

  getters: {
    total: (state) => state.items.reduce((sum, i) => sum + i.price, 0),
    totalDuration: (state) => state.items.reduce((sum, i) => sum + i.duration, 0),
    itemCount: (state) => state.items.length,
    isEmpty: (state) => state.items.length === 0,
  },

  actions: {
    addService(item: BookingItem) {
      if (this.hasService(item.serviceId)) return
      this.items.push(item)
      this.isOpen = true
    },

    removeService(serviceId: string) {
      this.items = this.items.filter((i) => i.serviceId !== serviceId)
    },

    hasService(serviceId: string): boolean {
      return this.items.some((i) => i.serviceId === serviceId)
    },

    setSlot(slot: BookingSlot) {
      this.slot = slot
    },

    clearBooking() {
      this.items = []
      this.slot = null
    },

    toggleDrawer() {
      this.isOpen = !this.isOpen
    },

    openDrawer() {
      this.isOpen = true
    },

    closeDrawer() {
      this.isOpen = false
    },
  },

  persist: true,
})
