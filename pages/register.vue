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
  head() {
    return {
      link: [
        {
          rel: 'stylesheet',
          href: '/css/pages/register.css',
        },
      ],
    }
  },
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
