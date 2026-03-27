<script setup lang="ts">
import { BRAND } from '~/utils/constants'

useHead({ title: 'Terms & Conditions | Bhaisepati Sports Academy' })
</script>

<template>
  <div>
    <section class="border-b border-border">
      <div class="section-container py-14">
        <h1 class="font-display text-4xl uppercase tracking-tight text-ink">Terms &amp; Conditions</h1>
        <p class="text-sm text-ink-faint mt-2">Last Updated: January 2025</p>
      </div>
    </section>

    <div class="mx-auto max-w-3xl px-4 py-12">
      <div class="space-y-10 text-ink-muted leading-relaxed text-[0.9375rem]">
        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">1. Introduction</h2>
          <p>
            Welcome to <strong>Bhaisepati Sports Academy</strong> ("BSA", "we", "us"). BSA is a sports training facility
            located in Bhaisepati, Lalitpur, Nepal, offering badminton courts, gym access, sauna facilities, and training programs.
            By using our facilities or website, you agree to these Terms &amp; Conditions.
          </p>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">2. Membership &amp; Enrollment</h2>
          <ul class="list-disc pl-5 space-y-1.5">
            <li>Memberships and program enrollments are monthly unless otherwise specified.</li>
            <li>All prices are in Nepalese Rupees (NPR) and are subject to change with prior notice.</li>
            <li>Enrollment is confirmed upon payment. Trial sessions may be offered at BSA's discretion.</li>
            <li>Members must present valid identification at the time of registration.</li>
          </ul>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">3. Court Bookings</h2>
          <ul class="list-disc pl-5 space-y-1.5">
            <li>Court bookings are subject to availability and confirmed via WhatsApp or on-site.</li>
            <li>Cancellations must be made at least 4 hours before the booked time.</li>
            <li>No-shows may result in booking privileges being restricted.</li>
            <li>BSA reserves the right to reassign courts for maintenance or events.</li>
          </ul>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">4. Facility Rules</h2>
          <ul class="list-disc pl-5 space-y-1.5">
            <li>Proper sports attire and non-marking shoes are required on court.</li>
            <li>Users must follow safety guidelines and instructions from staff.</li>
            <li>BSA is not responsible for personal belongings left on premises.</li>
            <li>Any damage to equipment or facilities caused by negligence may result in charges.</li>
          </ul>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">5. Payment</h2>
          <ul class="list-disc pl-5 space-y-1.5">
            <li>We accept payment via <strong>eSewa</strong>, <strong>Khalti</strong>, and <strong>Cash</strong>.</li>
            <li>Monthly subscriptions must be renewed before expiry to maintain access.</li>
            <li>Refunds are not available for used sessions or partial months.</li>
          </ul>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">6. Liability</h2>
          <p>
            BSA is not liable for injuries sustained during use of facilities. Members and visitors use all equipment and
            facilities at their own risk. We recommend consulting a physician before starting any training program.
          </p>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">7. Privacy</h2>
          <p>
            Personal information collected during registration is used solely for operational purposes and is not shared
            with third parties. Phone numbers are hashed for secure storage.
          </p>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">8. Changes</h2>
          <p>
            BSA reserves the right to update these terms. Changes will be posted on this page with an updated date.
            Continued use of our facilities constitutes acceptance.
          </p>
        </section>

        <section>
          <h2 class="font-display text-lg uppercase tracking-wider text-ink mb-3">9. Contact</h2>
          <p>
            Questions? Reach us at
            <a :href="`mailto:${BRAND.email}`" class="underline underline-offset-2 text-ink hover:text-accent transition-colors">{{ BRAND.email }}</a>
            or call <a :href="`tel:+977${BRAND.phone}`" class="underline underline-offset-2 text-ink hover:text-accent transition-colors">+977 {{ BRAND.phone }}</a>.
          </p>
        </section>
      </div>
    </div>
  </div>
</template>
