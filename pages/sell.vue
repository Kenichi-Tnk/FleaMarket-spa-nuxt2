<template>
    <div class="sell-page">
        <div class="sell-container">
            <h1 class="page-title">商品の出品</h1>

            <form @submit.prevent="handleSubmit" class="sell-form">
                <!-- 商品画像 -->
                <div class="form-section">
                    <h2 class="section-title">商品画像</h2>
                    <div class="form-group image-upload-section">
                        <div class="image-preview-container">
                            <div class="image-preview">
                                <img
                                v-if="previewImage"
                                :src="previewImage"
                                alt="商品画像プレビュー"
                                class="preview-img"
                                >
                                <div v-else class="no-image">
                                    <p>画像を選択してください</p>
                                    <p style="font-size: 12px; margin-top: 0.5rem;">推奨: 正方形の画像</p>
                                </div>
                            </div>
                        </div>
                        <label for="item_image" class="btn-select-image">
                            画像を選択
                        </label>
                        <input
                        id="item_image"
                        type="file"
                        accept="image/*"
                        class="file-input"
                        @change="handleImageSelect"
                        >
                        <span v-if="errors.image" class="form-error">
                            {{ errors.image }}
                        </span>
                        <span class="form-help">
                            ファイルサイズ: 最大10MB / 形式: JPG, PNG, GIF
                        </span>
                    </div>
                </div>

                <!-- 商品の詳細 -->
                <div class="form-section">
                    <h2 class="section-title">商品の詳細</h2>

          <!-- カテゴリー -->
          <div class="form-group">
            <label class="form-label">
              カテゴリー<span class="required">*</span>
            </label>
            <div class="category-checkboxes">
              <div
                v-for="category in categories"
                :key="category.id"
                class="category-item"
              >
                <input
                  type="checkbox"
                  :id="'category-' + category.id"
                  :value="category.id"
                  v-model="form.category_ids"
                  class="category-checkbox"
                >
                <label
                  :for="'category-' + category.id"
                  class="category-label"
                >
                  {{ category.name }}
                </label>
              </div>
            </div>
            <span v-if="errors.category_ids" class="form-error">{{ errors.category_ids }}</span>
          </div>

          <!-- 商品の状態 -->
          <div class="form-group">
            <label for="condition" class="form-label">
              商品の状態<span class="required">*</span>
            </label>
            <select
              id="condition"
              v-model="form.condition_id"
              class="form-control form-select"
              :class="{ 'is-invalid': errors.condition_id }"
            >
              <option value="" disabled selected>選択してください</option>
              <option
                v-for="condition in conditions"
                :key="condition.id"
                :value="condition.id"
              >
                {{ condition.name }}
              </option>
            </select>
            <span v-if="errors.condition_id" class="form-error">{{ errors.condition_id }}</span>
          </div>

          <!-- ブランド名 -->
          <div class="form-group">
            <label for="brand" class="form-label">ブランド</label>
            <input
              id="brand"
              v-model="form.brand"
              type="text"
              class="form-control"
              placeholder="例: Armani"
              maxlength="50"
            >
          </div>
        </div>

        <!-- 商品名と説明 -->
        <div class="form-section">
          <h2 class="section-title">商品名と説明</h2>

          <!-- 商品名 -->
          <div class="form-group">
            <label for="name" class="form-label">
              商品名<span class="required">*</span>
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="form-control"
              :class="{ 'is-invalid': errors.name }"
              placeholder="例: Armani 高級腕時計"
              maxlength="100"
            >
            <span v-if="errors.name" class="form-error">{{ errors.name }}</span>
          </div>

          <!-- 説明 -->
          <div class="form-group">
            <label for="description" class="form-label">
              説明<span class="required">*</span>
            </label>
            <textarea
              id="description"
              v-model="form.description"
              class="form-control"
              :class="{ 'is-invalid': errors.description }"
              placeholder="商品の状態や特徴を詳しく記載してください"
              rows="8"
            ></textarea>
            <span v-if="errors.description" class="form-error">{{ errors.description }}</span>
          </div>
        </div>

        <!-- 販売価格 -->
        <div class="form-section">
          <h2 class="section-title">販売価格</h2>
          <div class="form-group">
            <label for="price" class="form-label">
              価格<span class="required">*</span>
            </label>
            <div class="price-input-group">
              <span class="price-prefix">¥</span>
              <input
                id="price"
                v-model.number="form.price"
                type="number"
                class="form-control price-input"
                :class="{ 'is-invalid': errors.price }"
                placeholder="0"
                min="1"
              >
            </div>
            <span v-if="errors.price" class="form-error">{{ errors.price }}</span>
            <span class="form-help">1円以上で設定してください</span>
          </div>
        </div>

        <!-- エラーメッセージ -->
        <div v-if="errorMessage" class="alert alert-danger">
          {{ errorMessage }}
        </div>

        <!-- 成功メッセージ -->
        <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
        </div>

        <!-- ボタン -->
        <div class="form-actions">
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="isSubmitting"
          >
            <span v-if="isSubmitting" class="loading"></span>
            {{ isSubmitting ? '出品中...' : '出品する' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
    name: 'SellPage',
    // middleware: 'auth', // 後で有効化

    head() {
        return {
        title: '商品の出品',
        link: [
            {
            rel: 'stylesheet',
            href: '/css/pages/sell.css',
            },
        ],
        }
    },

    data() {
        return {
        form: {
            name: '',
            brand: '',
            description: '',
            category_ids: [],
            condition_id: '',
            price: null,
            image: null,
        },
        previewImage: null,
        errors: {},
        errorMessage: '',
        successMessage: '',
        isSubmitting: false,
        }
    },

    computed: {
        categories() {
            return this.$store.getters['items/categories']
        },
        conditions() {
            return this.$store.getters['items/conditions']
        },
    },

    async mounted() {
        // カテゴリと状態を取得
        await Promise.all([
            this.$store.dispatch('items/fetchCategories'),
            this.$store.dispatch('items/fetchConditions'),
        ])
    },

    methods: {
        handleImageSelect(event) {
        const file = event.target.files[0]
        this.errors.image = ''

        if (!file) {
            return
        }

        // ファイルサイズチェック（10MB）
        if (file.size > 10 * 1024 * 1024) {
            this.errors.image = 'ファイルサイズは10MB以下にしてください'
            return
        }

        // ファイル形式チェック
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif']
        if (!allowedTypes.includes(file.type)) {
            this.errors.image = 'JPG、PNG、GIF形式の画像を選択してください'
            return
        }

        this.form.image = file

        // プレビュー表示
        const reader = new FileReader()
        reader.onload = (e) => {
            this.previewImage = e.target.result
        }
        reader.readAsDataURL(file)
        },

        validateForm() {
        this.errors = {}

        if (!this.form.image) {
            this.errors.image = '商品画像を選択してください'
        }

        if (!this.form.name || this.form.name.trim().length === 0) {
            this.errors.name = '商品名を入力してください'
        }

        if (!this.form.description || this.form.description.trim().length === 0) {
            this.errors.description = '商品の説明を入力してください'
        }

        if (this.form.category_ids.length === 0) {
            this.errors.category_ids = 'カテゴリーを1つ以上選択してください'
        }

        if (!this.form.condition_id) {
            this.errors.condition_id = '商品の状態を選択してください'
        }

        if (!this.form.price || this.form.price < 1) {
            this.errors.price = '価格は1円以上で入力してください'
        }

        return Object.keys(this.errors).length === 0
        },

        async handleSubmit() {
        this.errorMessage = ''
        this.successMessage = ''

        if (!this.validateForm()) {
            this.errorMessage = '入力内容に誤りがあります'
            return
        }

        this.isSubmitting = true

        try {
            // FormDataを作成
            const formData = new FormData()
            formData.append('name', this.form.name)
            formData.append('brand', this.form.brand || '')
            formData.append('description', this.form.description)
            formData.append('price', this.form.price)
            formData.append('condition_id', this.form.condition_id)
            formData.append('image', this.form.image)
            
            // カテゴリIDを個別に追加
            this.form.category_ids.forEach((categoryId, index) => {
                formData.append(`category_ids[${index}]`, categoryId)
            })

            // API呼び出し（multipart/form-dataで送信）
            const response = await this.$axios.post('/items', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            })

            this.successMessage = '商品を出品しました'
            // 成功後、少し待ってから商品詳細ページにリダイレクト
            setTimeout(() => {
                this.$router.push(`/item/${response.data.item.id}`)
            }, 1500)
        } catch (error) {
            console.error('商品出品エラー:', error)
            this.errorMessage = error.response?.data?.message || '商品の出品に失敗しました'

            if (error.response?.data?.errors) {
                this.errors = error.response.data.errors
            }
        } finally {
            this.isSubmitting = false
        }
        },
    },
}
</script>
