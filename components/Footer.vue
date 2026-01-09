<template>
  <footer class="site-footer">
    <div class="footer-container">
      <p>&copy; {{ currentYear }} {{ appName }}. All rights reserved.</p>
      <div class="footer-links">
        <nuxt-link to="/about" class="footer-link">サービスについて</nuxt-link>
        <nuxt-link to="/terms" class="footer-link">利用規約</nuxt-link>
        <nuxt-link to="/privacy" class="footer-link">プライバシーポリシー</nuxt-link>
        <nuxt-link to="/contact" class="footer-link">お問い合わせ</nuxt-link>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer',
  data() {
    return {
      appName: this.$config.appName || 'FleaMarket',
      currentYear: new Date().getFullYear(),
    }
  },
}
</script>

<style scoped>
.site-footer {
  background-color: #2c3e50;
  color: #ecf0f1;
  padding: 2rem 0;
  margin-top: auto;
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  text-align: center;
}

.footer-container p {
  margin-bottom: 1rem;
}

.footer-links {
  display: flex;
  justify-content: center;
  gap: 2rem;
  flex-wrap: wrap;
}

.footer-link {
  color: #ecf0f1;
  text-decoration: none;
  transition: color 0.3s;
}

.footer-link:hover {
  color: #e74c3c;
}

@media (max-width: 768px) {
  .footer-links {
    flex-direction: column;
    gap: 0.5rem;
  }
}
</style>
