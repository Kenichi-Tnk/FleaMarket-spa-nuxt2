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

  head() {
    return {
      link: [
        {
          rel: 'stylesheet',
          href: '/css/pages/login.css',
        },
      ],
    }
  },

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
