<script setup lang="ts">
useHead({ title: 'Privacy Policy — Bhaisepati Sports Academy' })
</script>

<template>
  <div class="mx-auto max-w-3xl px-4 py-16">
    <h1 class="text-heading-xl mb-2">Privacy Policy</h1>
    <p class="text-sm text-ink-faint mb-10">Last Updated: March 12, 2026</p>

    <div class="space-y-10 text-ink-muted leading-relaxed text-[0.9375rem]">
      <!-- 1 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">1. Introduction</h2>
        <p>
          At <strong>Bhaisepati Sports Academy</strong>, your privacy matters. This policy explains what information we collect,
          how we use it, and the choices you have. By using our Platform, you consent to the practices described here.
        </p>
      </section>

      <!-- 2 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">2. Information We Collect</h2>

        <h3 class="font-medium text-ink mt-4 mb-2">Information you provide</h3>
        <ul class="list-disc pl-5 space-y-1.5">
          <li><strong>Phone number</strong> — for OTP verification and order updates via SMS.</li>
          <li><strong>Name &amp; delivery address</strong> — to fulfill your order.</li>
          <li><strong>Location data</strong> — only when you choose to share it via the map feature, to improve delivery accuracy.</li>
        </ul>

        <h3 class="font-medium text-ink mt-4 mb-2">Information collected automatically</h3>
        <ul class="list-disc pl-5 space-y-1.5">
          <li>Basic device and browser information (screen size, browser type) for a better shopping experience.</li>
          <li>Pages visited and interactions, to understand how we can improve.</li>
        </ul>
      </section>

      <!-- 3 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">3. How We Use Your Information</h2>
        <ul class="list-disc pl-5 space-y-1.5">
          <li>Process and deliver your orders.</li>
          <li>Send order confirmation, shipping updates, and delivery notifications via SMS.</li>
          <li>Improve our products, website, and customer experience.</li>
          <li>Prevent fraud and maintain security.</li>
        </ul>
        <p class="mt-3">
          We do <strong>not</strong> use your data for targeted advertising, profiling, or automated decision-making.
          We have zero AI/ML data processing — all features use simple deterministic logic.
        </p>
      </section>

      <!-- 4 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">4. Phone Number Security</h2>
        <p>
          We take phone number privacy seriously. Your phone number is <strong>hashed using HMAC-SHA256</strong>
          before storage — we do not store your actual phone number in our database. This means even if our database
          were compromised, your phone number cannot be recovered.
        </p>
      </section>

      <!-- 5 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">5. Data Sharing</h2>
        <p>We do not sell, rent, or trade your personal data. We share information only with:</p>
        <ul class="list-disc pl-5 space-y-1.5 mt-2">
          <li><strong>Delivery partners</strong> — your name, address, and phone number to complete delivery.</li>
          <li><strong>Payment gateways</strong> (eSewa) — transaction details required to process your payment. We do not store your payment credentials.</li>
          <li><strong>SMS provider</strong> (Sparrow SMS) — your phone number to send OTP codes and order updates.</li>
        </ul>
      </section>

      <!-- 6 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">6. Cookies</h2>
        <p>
          We use essential cookies to keep your cart and session active. We do not use advertising cookies or
          third-party tracking cookies.
        </p>
      </section>

      <!-- 7 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">7. Data Retention</h2>
        <ul class="list-disc pl-5 space-y-1.5">
          <li>Order records are retained for 2 years for legal and accounting purposes.</li>
          <li>OTP codes expire within 10 minutes and are marked as used after verification.</li>
          <li>Tracking tokens expire after 30 days.</li>
        </ul>
      </section>

      <!-- 8 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">8. Your Rights</h2>
        <p>You have the right to:</p>
        <ul class="list-disc pl-5 space-y-1.5 mt-2">
          <li>Request information about the data we hold that relates to your orders.</li>
          <li>Request correction of inaccurate delivery information on pending orders.</li>
          <li>Request deletion of your data, subject to legal retention requirements.</li>
        </ul>
        <p class="mt-3">
          To exercise these rights, contact us at
          <a href="mailto:hello@bsa.example.com" class="underline underline-offset-2 text-ink hover:text-accent transition-colors">hello@bsa.example.com</a>.
        </p>
      </section>

      <!-- 9 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">9. Children's Privacy</h2>
        <p>
          Our Platform is not intended for children under 16. We do not knowingly collect information from minors.
        </p>
      </section>

      <!-- 10 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">10. Changes to This Policy</h2>
        <p>
          We may update this Privacy Policy from time to time. The updated version will be posted on this page with a
          revised "Last Updated" date.
        </p>
      </section>

      <!-- 11 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">11. Governing Law</h2>
        <p>
          This Privacy Policy is governed by the laws of Nepal, including the Electronic Transaction Act, 2063 (2008)
          and the Individual Privacy Act, 2075 (2018).
        </p>
      </section>

      <!-- 12 -->
      <section>
        <h2 class="text-heading-md text-ink mb-3">12. Contact</h2>
        <p>
          For privacy-related questions, reach us at
          <a href="mailto:hello@bsa.example.com" class="underline underline-offset-2 text-ink hover:text-accent transition-colors">hello@bsa.example.com</a>.
        </p>
      </section>
    </div>
  </div>
</template>
