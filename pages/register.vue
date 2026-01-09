<template>
  <div class="register-page">
    <div class="register-container">
      <h1 class="page-title">会員登録</h1>

      <form @submit.prevent="handleRegister" class="register-form">
        <!-- 名前 -->
        <div class="form-group">
          <label for="name" class="form-label">お名前</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.name }"
            placeholder="例：山田太郎"
            required
          >
          <span v-if="errors.name" class="form-error">{{ errors.name }}</span>
        </div>

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
            placeholder="8文字以上の英数字"
            required
          >
          <span v-if="errors.password" class="form-error">{{ errors.password }}</span>
          <small class="form-text">8文字以上、英字と数字を含めてください</small>
        </div>

        <!-- パスワード確認 -->
        <div class="form-group">
          <label for="password_confirmation" class="form-label">パスワード確認</label>
          <input
            id="password_confirmation"
            v-model="form.password_confirmation"
            type="password"
            class="form-control"
            :class="{ 'is-invalid': errors.password_confirmation }"
            placeholder="パスワードを再入力"
            required
          >
          <span v-if="errors.password_confirmation" class="form-error">
            {{ errors.password_confirmation }}
          </span>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>

        <!-- 登録ボタン -->
        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="loading"></span>
          <span v-else>登録する</span>
        </button>
      </form>

      <!-- ログインリンク -->
      <div class="login-link">
        <p>すでにアカウントをお持ちの方</p>
        <nuxt-link to="/login" class="link">ログインはこちら</nuxt-link>
      </div>
    </div>
  </div>
</template>

<script>
import { isValidEmail, isValidPassword } from '@/utils/helpers'

export default {
  name: 'RegisterPage',
  middleware: 'guest', // ログイン済みユーザーはアクセス不可

  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      },
      errors: {},
      errorMessage: '',
      isLoading: false,
    }
  },

  methods: {
    validateForm() {
      this.errors = {}

      // 名前
      if (!this.form.name || this.form.name.trim().length === 0) {
        this.errors.name = 'お名前を入力してください'
      }

      // メールアドレス
      if (!this.form.email) {
        this.errors.email = 'メールアドレスを入力してください'
      } else if (!isValidEmail(this.form.email)) {
        this.errors.email = '正しいメールアドレスを入力してください'
      }

      // パスワード
      if (!this.form.password) {
        this.errors.password = 'パスワードを入力してください'
      } else if (!isValidPassword(this.form.password)) {
        this.errors.password = 'パスワードは8文字以上、英字と数字を含めてください'
      }

      // パスワード確認
      if (!this.form.password_confirmation) {
        this.errors.password_confirmation = 'パスワード確認を入力してください'
      } else if (this.form.password !== this.form.password_confirmation) {
        this.errors.password_confirmation = 'パスワードが一致しません'
      }

      return Object.keys(this.errors).length === 0
    },

    async handleRegister() {
      // バリデーション
      this.errorMessage = ''
      if (!this.validateForm()) {
        return
      }

      this.isLoading = true

      try {
        // 会員登録処理
        const result = await this.$store.dispatch('auth/register', {
          name: this.form.name,
          email: this.form.email,
          password: this.form.password,
          password_confirmation: this.form.password_confirmation,
        })

        if (result.success) {
          // 登録成功 - ホームにリダイレクト
          this.$router.push('/')
        } else {
          this.errorMessage = result.message || '会員登録に失敗しました'
          // サーバーからのバリデーションエラーがあれば表示
          if (result.errors) {
            this.errors = result.errors
          }
        }
      } catch (error) {
        console.error('Register error:', error)
        this.errorMessage = '会員登録処理中にエラーが発生しました'
      } finally {
        this.isLoading = false
      }
    },
  },
}
</script>

<style scoped>
.register-page {
  min-height: calc(100vh - 200px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  background-color: #f5f5f5;
}

.register-container {
  width: 100%;
  max-width: 500px;
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

.register-form {
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

.form-text {
  display: block;
  margin-top: 0.25rem;
  font-size: 14px;
  color: #666;
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

.login-link {
  text-align: center;
  padding-top: 1.5rem;
  border-top: 1px solid #eee;
}

.login-link p {
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
