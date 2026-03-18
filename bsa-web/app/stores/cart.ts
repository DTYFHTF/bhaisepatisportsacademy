import { defineStore } from 'pinia'
import type { CartItem } from '~/types/cart'

export const useCartStore = defineStore('cart', {
  state: () => ({
    items: [] as CartItem[],
    isOpen: false,
  }),

  getters: {
    total: (state) => state.items.reduce((sum, i) => sum + i.price * i.quantity, 0),
    itemCount: (state) => state.items.reduce((sum, i) => sum + i.quantity, 0),
    isEmpty: (state) => state.items.length === 0,
  },

  actions: {
    addItem(item: Omit<CartItem, 'quantity'>) {
      const existing = this.items.find((i) => i.variantId === item.variantId)
      if (existing) {
        existing.quantity++
      } else {
        this.items.push({ ...item, quantity: 1 })
      }
      this.isOpen = true
    },

    removeItem(variantId: string) {
      this.items = this.items.filter((i) => i.variantId !== variantId)
    },

    updateQuantity(variantId: string, quantity: number) {
      const item = this.items.find((i) => i.variantId === variantId)
      if (item) {
        if (quantity <= 0) {
          this.removeItem(variantId)
        } else {
          item.quantity = quantity
        }
      }
    },

    clearCart() {
      this.items = []
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
