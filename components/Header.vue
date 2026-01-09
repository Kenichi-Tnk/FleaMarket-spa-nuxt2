<template>
  <header class="site-header">
    <div class="header-container">
      <nuxt-link to="/" class="logo">
        <img src="/images/logo.svg" alt="COACHTECH" class="logo-image">
      </nuxt-link>

      <!-- TODO: 検索ボックスを後で追加 -->
      <div class="search-container">
        <!-- <input type="text" placeholder="なにをお探しですか？" class="search-input"> -->
      </div>

      <nav class="nav">
        <nuxt-link 
          v-if="!isAuthenticated" 
          to="/login" 
          class="nav-link"
        >
          ログイン
        </nuxt-link>
        <nuxt-link 
          v-else
          to="/mypage" 
          class="nav-link"
        >
          マイページ
        </nuxt-link>
        <nuxt-link to="/sell" class="nav-link">出品</nuxt-link>
      </nav>
    </div>
  </header>
</template>

<script>
export default {
  name: 'Header',
  computed: {
    isAuthenticated() {
      // 認証状態を管理するVuexストアと連携
      return this.$store.state.auth?.user !== null
    },
  },
}
</script>

<style scoped>
.site-header {
  background-color: #000;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-container {
  max-width: 100%;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
}

.logo {
  text-decoration: none;
  color: #fff;
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.logo-image {
  height: 40px;
  width: auto;
}

.logo h1 {
  font-size: 1.5rem;
  margin: 0;
  color: #fff;
}

.search-container {
  flex: 1;
  max-width: 500px;
  /* 検索ボックス用のスペース確保 */
}

.search-input {
  width: 100%;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
}

.nav {
  display: flex;
  align-items: center;
  gap: 2rem;
  flex-shrink: 0;
}

.nav-link {
  text-decoration: none;
  color: #fff;
  font-weight: 500;
  transition: color 0.3s;
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1rem;
  white-space: nowrap;
}

.nav-link:hover {
  color: #ccc;
}

@media (max-width: 768px) {
  .header-container {
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem;
  }

  .logo-image {
    height: 30px;
  }

  .search-container {
    order: 3;
    width: 100%;
    max-width: 100%;
  }

  .nav {
    gap: 1rem;
  }
  
  .nav-link {
    font-size: 0.9rem;
  }
}
</style>
