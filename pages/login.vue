<template>
  <div class="login-page">
    <div class="login-container">
      <h1 class="page-title">ログイン</h1>

      <form @submit.prevent="handleLogin" class="login-form">
        <!-- メールアドレス -->
        <div class="form-group">
          <label for="email" class="form-label">メールアドレス</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-control"
            :class="{ 'is-invalid': errors.email }"
            placeholder="例：test@example.com"
            required
          >
          <span v-if="errors.email" class="form-error">{{ errors.email }}</span>
        </div>

        <!-- パスワード -->
        <div class="form-group">
          <label for="password" class="form-label">パスワード</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-control"
            :class="{ 'is-invalid': errors.password }"
            placeholder="パスワードを入力"
            required
          >
          <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>

        <!-- ログインボタン -->
        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="loading"></span>
          <span v-else>ログイン</span>
        </button>
      </form>

      <!-- 新規登録リンク -->
      <div class="register-link">
        <p>アカウントをお持ちでない方</p>
        <nuxt-link to="/register" class="link">新規登録はこちら</nuxt-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginPage',
  middleware: 'guest', // ログイン済みユーザーはアクセス不可

  data() {
    return {
      form: {
        email: '',
        password: '',
      },
      errors: {},
      errorMessage: '',
      isLoading: false,
    }
  },

  methods: {
    async handleLogin() {
      // バリデーションリセット
      this.errors = {}
      this.errorMessage = ''

      // 簡易バリデーション
      if (!this.form.email) {
        this.errors.email = 'メールアドレスを入力してください'
        return
      }
      if (!this.form.password) {
        this.errors.password = 'パスワードを入力してください'
        return
      }

      this.isLoading = true

      try {
        // ログイン処理
        const result = await this.$store.dispatch('auth/login', {
          email: this.form.email,
          password: this.form.password,
        })

        if (result.success) {
          // リダイレクト先を取得（元のページまたはホーム）
          const redirect = this.$route.query.redirect || '/'
          this.$router.push(redirect)
        } else {
          this.errorMessage = result.message || 'ログインに失敗しました'
        }
      } catch (error) {
        console.error('Login error:', error)
        this.errorMessage = 'ログイン処理中にエラーが発生しました'
      } finally {
        this.isLoading = false
      }
    },
  },
}
</script>

<style scoped>
.login-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background-color: #f5f5f5;
}

.login-container {
  width: 100%;
  max-width: 400px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 2rem;
}

.page-title {
  text-align: center;
  margin-bottom: 2rem;
  color: #2c3e50;
  font-size: 1.8rem;
}

.login-form {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #333;
}

.form-control {
  width: 100%;
  padding: 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  transition: border-color 0.3s;
}

.form-control:focus {
  outline: none;
  border-color: #3498db;
}

.form-control.is-invalid {
  border-color: #e74c3c;
}

.form-error {
  color: #e74c3c;
  font-size: 14px;
  margin-top: 0.25rem;
  display: block;
}

.btn-block {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  font-weight: 600;
}

.btn-primary {
  background-color: #ff333f;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover:not(:disabled) {
  background-color: #ff333f;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.alert {
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.alert-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

.register-link {
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
}

.register-link p {
  margin-bottom: 0.5rem;
  color: #666;
}

.link {
  color: #3498db;
  text-decoration: none;
  font-weight: 600;
}

.link:hover {
  text-decoration: underline;
}

.loading {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 3px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
