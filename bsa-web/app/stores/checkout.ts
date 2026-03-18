import { defineStore } from 'pinia'
import type { PaymentMethod } from '~/types/order'

interface CheckoutState {
  step: 1 | 2 | 3
  phone: string
  email: string
  otpVerified: boolean
  token: string | null
  delivery: {
    fullName: string
    address: string
    city: string
    deliveryNote: string
    lat: number | null
    lng: number | null
    formattedAddress: string
    landmark: string
  }
  paymentMethod: PaymentMethod
}

export const useCheckoutStore = defineStore('checkout', {
  state: (): CheckoutState => ({
    step: 1,
    phone: '',
    email: '',
    otpVerified: false,
    token: null,
    delivery: {
      fullName: '',
      address: '',
      city: '',
      deliveryNote: '',
      lat: null,
      lng: null,
      formattedAddress: '',
      landmark: '',
    },
    paymentMethod: 'COD',
  }),

  actions: {
    setPhone(phone: string) {
      this.phone = phone
    },

    setEmail(email: string) {
      this.email = email
    },

    verifyOtp(token: string) {
      this.otpVerified = true
      this.token = token
      this.step = 2
    },

    skipOtp() {
      this.step = 2
    },

    setDelivery(data: CheckoutState['delivery']) {
      this.delivery = data
      this.step = 3
    },

    setPaymentMethod(method: PaymentMethod) {
      this.paymentMethod = method
    },

    goToStep(step: 1 | 2 | 3) {
      this.step = step
    },

    reset() {
      this.step = 1
      this.phone = ''
      this.email = ''
      this.otpVerified = false
      this.token = null
      this.delivery = { fullName: '', address: '', city: '', deliveryNote: '', lat: null, lng: null, formattedAddress: '', landmark: '' }
      this.paymentMethod = 'COD'
    },
  },
})
