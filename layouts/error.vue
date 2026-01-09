<template>
  <div class="error-page">
    <div class="error-container">
      <h1 v-if="error.statusCode === 404">404</h1>
      <h1 v-else>エラーが発生しました</h1>
      <p v-if="error.statusCode === 404">
        お探しのページが見つかりませんでした
      </p>
      <p v-else>
        {{ error.message }}
      </p>
      <nuxt-link to="/" class="btn-home">
        ホームに戻る
      </nuxt-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ErrorLayout',
  props: {
    error: {
      type: Object,
      default: null,
    },
  },
}
</script>

<style scoped>
.error-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f5f5f5;
}

.error-container {
  text-align: center;
  padding: 40px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.error-container h1 {
  font-size: 72px;
  color: #e74c3c;
  margin-bottom: 20px;
}

.error-container p {
  font-size: 18px;
  color: #666;
  margin-bottom: 30px;
}

.btn-home {
  display: inline-block;
  padding: 12px 30px;
  background-color: #3498db;
  color: white;
  text-decoration: none;
  border-radius: 4px;
  transition: background-color 0.3s;
}

.btn-home:hover {
  background-color: #2980b9;
}
</style>
