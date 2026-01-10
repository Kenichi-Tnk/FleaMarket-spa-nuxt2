<template>
  <div class="profile-setup-page">
    <div class="profile-container">
      <h1 class="page-title">プロフィール設定</h1>

      <form @submit.prevent="handleSubmit" class="profile-form">
        <!-- プロフィール画像 -->
        <div class="form-group profile-image-section">
          <div class="image-preview">
            <img
              v-if="previewImage"
              :src="previewImage"
              alt="プロフィール画像"
              class="preview-img"
            >
            <div v-else class="no-image">
              <span>画像を選択してください</span>
            </div>
          </div>
          <label for="profile_image" class="btn btn-secondary">
            画像を選択
          </label>
          <input
            id="profile_image"
            type="file"
            accept="image/*"
            class="file-input"
            @change="handleImageSelect"
          >
          <span v-if="errors.profile_image" class="form-error">
            {{ errors.profile_image }}
          </span>
        </div>

        <!-- ユーザー名 -->
        <div class="form-group">
          <label for="name" class="form-label">ユーザー名</label>
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

        <!-- 郵便番号 -->
        <div class="form-group">
          <label for="postal_code" class="form-label">郵便番号</label>
          <input
            id="postal_code"
            v-model="form.postal_code"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.postal_code }"
            placeholder="例：123-4567"
            maxlength="8"
            @input="formatPostalCode"
          >
          <span v-if="errors.postal_code" class="form-error">
            {{ errors.postal_code }}
          </span>
        </div>

        <!-- 住所 -->
        <div class="form-group">
          <label for="address" class="form-label">住所</label>
          <input
            id="address"
            v-model="form.address"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.address }"
            placeholder="例：東京都渋谷区1-2-3"
            required
          >
          <span v-if="errors.address" class="form-error">
            {{ errors.address }}
          </span>
        </div>

        <!-- 建物名 -->
        <div class="form-group">
          <label for="building" class="form-label">建物名</label>
          <input
            id="building"
            v-model="form.building"
            type="text"
            class="form-control"
            :class="{ 'is-invalid': errors.building }"
            placeholder="例：サンプルビル101号室"
          >
          <span v-if="errors.building" class="form-error">
            {{ errors.building }}
          </span>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>

        <!-- 成功メッセージ -->
        <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
        </div>

        <!-- 更新ボタン -->
        <button
          type="submit"
          class="btn btn-primary btn-block"
          :disabled="isLoading"
        >
          <span v-if="isLoading" class="loading"></span>
          <span v-else>更新</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ProfileSetupPage',
  middleware: 'auth', // ログインユーザーのみアクセス可

  data() {
    return {
      form: {
        name: '',
        postal_code: '',
        address: '',
        building: '',
        profile_image: null,
      },
      previewImage: null,
      errors: {},
      errorMessage: '',
      successMessage: '',
      isLoading: false,
    }
  },

  mounted() {
    // ログインユーザーの情報を取得
    const user = this.$store.state.auth?.user
    if (user) {
      this.form.name = user.name || ''
      this.form.postal_code = user.postal_code || ''
      this.form.address = user.address || ''
      this.form.building = user.building || ''
      if (user.profile_image) {
        this.previewImage = user.profile_image
      }
    }
  },

  methods: {
    // 画像選択ハンドラ
    handleImageSelect(event) {
      const file = event.target.files[0]
      if (!file) return

      // ファイルサイズチェック（10MB）
      const maxSize = 10 * 1024 * 1024
      if (file.size > maxSize) {
        this.errors.profile_image = 'ファイルサイズは10MB以下にしてください'
        return
      }

      // 画像タイプチェック
      const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']
      if (!allowedTypes.includes(file.type)) {
        this.errors.profile_image = '画像ファイル（JPG、PNG、GIF、WebP）を選択してください'
        return
      }

      this.errors.profile_image = ''
      this.form.profile_image = file

      // プレビュー表示
      const reader = new FileReader()
      reader.onload = (e) => {
        this.previewImage = e.target.result
      }
      reader.readAsDataURL(file)
    },

    // 郵便番号フォーマット（自動でハイフン挿入）
    formatPostalCode(event) {
      let value = event.target.value.replace(/[^0-9]/g, '')
      if (value.length > 3) {
        value = value.slice(0, 3) + '-' + value.slice(3, 7)
      }
      this.form.postal_code = value
    },

    // バリデーション
    validateForm() {
      this.errors = {}

      if (!this.form.name || this.form.name.trim().length === 0) {
        this.errors.name = 'ユーザー名を入力してください'
      }

      if (this.form.postal_code && !/^\d{3}-\d{4}$/.test(this.form.postal_code)) {
        this.errors.postal_code = '郵便番号は「123-4567」の形式で入力してください'
      }

      if (!this.form.address || this.form.address.trim().length === 0) {
        this.errors.address = '住所を入力してください'
      }

      return Object.keys(this.errors).length === 0
    },

    // 送信処理
    async handleSubmit() {
      this.errorMessage = ''
      this.successMessage = ''

      if (!this.validateForm()) {
        return
      }

      this.isLoading = true

      try {
        // FormDataを作成（画像アップロード用）
        const formData = new FormData()
        formData.append('name', this.form.name)
        formData.append('postal_code', this.form.postal_code)
        formData.append('address', this.form.address)
        formData.append('building', this.form.building || '')
        
        if (this.form.profile_image) {
          formData.append('profile_image', this.form.profile_image)
        }

        // プロフィール更新API呼び出し
        const response = await this.$axios.post('/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        })

        // ユーザー情報を更新
        this.$store.commit('auth/setUser', response.data.user)

        this.successMessage = 'プロフィールを更新しました'

        // 成功後、少し待ってからマイページにリダイレクト
        setTimeout(() => {
          this.$router.push('/mypage')
        }, 1500)
      } catch (error) {
        console.error('Profile update error:', error)
        this.errorMessage = error.response?.data?.message || 'プロフィール更新に失敗しました'
        
        if (error.response?.data?.errors) {
          this.errors = error.response.data.errors
        }
      } finally {
        this.isLoading = false
      }
    },
  },
}
</script>

<style scoped>
.profile-setup-page {
  min-height: calc(100vh - 200px);
  padding: 2rem 1rem;
  background-color: #f5f5f5;
}

.profile-container {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
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

.profile-form {
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.profile-image-section {
  text-align: center;
  margin-bottom: 2rem;
}

.image-preview {
  width: 150px;
  height: 150px;
  margin: 0 auto 1rem;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #ddd;
  background-color: #f8f8f8;
  display: flex;
  align-items: center;
  justify-content: center;
}

.preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  color: #999;
  font-size: 14px;
  padding: 1rem;
  text-align: center;
}

.file-input {
  display: none;
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

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s;
  display: inline-block;
}

.btn-block {
  width: 100%;
  padding: 12px;
}

.btn-primary {
  background-color: #ff333f;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-primary:hover:not(:disabled) {
  background-color: #e62e39;
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background-color: #95a5a6;
  color: white;
}

.btn-secondary:hover {
  background-color: #7f8c8d;
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

.alert-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
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

@media (max-width: 768px) {
  .profile-container {
    padding: 1.5rem;
  }

  .page-title {
    font-size: 1.5rem;
  }
}
</style>
