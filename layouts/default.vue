<template>
  <div id="app">
    <Header />
    <main class="main-content">
      <Nuxt />
    </main>
    <Footer />
  </div>
</template>

<script>
export default {
  name: 'DefaultLayout',
  async created() {
    // アプリ起動時に一度だけ認証状態を復元
    if (process.client && !this.$store.state.auth.user) {
      await this.$store.dispatch('auth/checkAuth')
    }
  },
}
</script>

<style scoped>
#app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.main-content {
  flex: 1;
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}
</style>
