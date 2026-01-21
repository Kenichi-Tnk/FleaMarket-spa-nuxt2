<template>
  <div class="purchase-page">
    <div class="purchase-container">
      <h1 class="page-title">購入手続き</h1>

      <!-- ローディング -->
      <div v-if="loading" class="loading-container">
        <div class="loading"></div>
        <span class="loading-text">読み込み中...</span>
      </div>

      <!-- エラー -->
      <div v-else-if="error" class="error-message">
        {{ error }}
      </div>

      <!-- 売り切れ -->
      <div v-else-if="item && item.is_sold" class="error-message">
        この商品は売り切れです
      </div>

      <!-- 購入内容 -->
      <template v-else-if="item">
        <div class="purchase-content">
          <!-- 左側：商品情報・支払い方法 -->
          <div class="item-section">
            <!-- 商品情報 -->
            <div class="item-info">
              <div class="item-image">
                <img :src="getImageUrl(item.img_url)" :alt="item.name">
              </div>
              <div class="item-details">
                <h2 class="item-name">{{ item.name }}</h2>
                <p v-if="item.brand" class="item-brand">{{ item.brand }}</p>
                <div class="item-price">¥ {{ formatPrice(item.price) }}</div>
              </div>
            </div>

            <!-- 支払い方法 -->
            <div class="payment-section">
              <h3 class="section-title">支払い方法</h3>
              <div class="payment-methods">
                <label
                  class="payment-method"
                  :class="{ selected: paymentMethod === 'convenience' }"
                >
                  <input
                    type="radio"
                    value="convenience"
                    v-model="paymentMethod"
                  >
                  <span class="payment-label">コンビニ払い</span>
                </label>
                <label
                  class="payment-method"
                  :class="{ selected: paymentMethod === 'card' }"
                >
                  <input
                    type="radio"
                    value="card"
                    v-model="paymentMethod"
                  >
                  <span class="payment-label">カード払い</span>
                </label>
              </div>
            </div>
          </div>

          <!-- 右側：配送先・購入 -->
          <div class="order-section">
            <!-- 配送先 -->
            <div class="address-section">
              <h3 class="section-title">配送先</h3>
              <div class="address-info">
                <div class="address-row">
                  <span class="address-label">郵便番号</span>
                  <span class="address-value">{{ address.postal_code || '未設定' }}</span>
                </div>
                <div class="address-row">
                  <span class="address-label">住所</span>
                  <span class="address-value">{{ address.address || '未設定' }}</span>
                </div>
                <div v-if="address.building" class="address-row">
                  <span class="address-label">建物名</span>
                  <span class="address-value">{{ address.building }}</span>
                </div>
              </div>
              <button class="btn-change-address" @click="changeAddress">
                配送先を変更する
              </button>
            </div>

            <!-- 購入内容確認 -->
            <div class="summary-section">
              <h3 class="section-title">購入内容</h3>
              <div class="summary-row">
                <span class="summary-label">商品代金</span>
                <span class="summary-value">¥ {{ formatPrice(item.price) }}</span>
              </div>
              <div class="summary-row">
                <span class="summary-label">支払い方法</span>
                <span class="summary-value">{{ paymentMethodLabel }}</span>
              </div>
              <div class="summary-row total">
                <span class="summary-label">合計</span>
                <span class="summary-value">¥ {{ formatPrice(item.price) }}</span>
              </div>

              <!-- エラーメッセージ -->
              <div v-if="errorMessage" class="alert alert-danger">
                {{ errorMessage }}
              </div>

              <!-- 購入ボタン -->
              <button
                class="btn-purchase"
                @click="handlePurchase"
                :disabled="isPurchasing || !canPurchase || item.is_sold"
              >
                <span v-if="isPurchasing" class="loading"></span>
                {{ isPurchasing ? '処理中...' : item.is_sold ? '売り切れ' : '購入する' }}
              </button>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
import { getImageUrl } from '~/utils/helpers'

export default {
  name: 'PurchasePage',
  // middleware: 'auth', // 後で有効化

  head() {
    return {
      title: '購入手続き',
      link: [
        {
          rel: 'stylesheet',
          href: '/css/pages/purchase.css',
        },
      ],
    }
  },

  data() {
    return {
      item: null,
      loading: true,
      error: null,
      paymentMethod: 'convenience',
      address: {
        postal_code: '',
        address: '',
        building: '',
      },
      errorMessage: '',
      isPurchasing: false,
    }
  },

  computed: {
    paymentMethodLabel() {
      return this.paymentMethod === 'convenience' ? 'コンビニ払い' : 'カード払い'
    },

    canPurchase() {
      return this.address.postal_code && this.address.address && this.paymentMethod
    },
  },

  async mounted() {
    await this.fetchItemAndAddress()
  },

  methods: {
    getImageUrl,

    formatPrice(price) {
      return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
    },

    async fetchItemAndAddress() {
      this.loading = true
      this.error = null

      try {
        const itemId = this.$route.params.id

        // 商品情報を取得
        const itemResult = await this.$store.dispatch('items/fetchItem', itemId)
        if (!itemResult.success) {
          throw new Error(itemResult.message)
        }
        this.item = itemResult.item

        // 認証されている場合、ユーザー情報を取得
        const user = this.$store.state.auth.user
        if (user && user.profile) {
          this.address = {
            postal_code: user.profile.postal_code || '',
            address: user.profile.address || '',
            building: user.profile.building || '',
          }
        }
      } catch (error) {
        console.error('データ取得エラー:', error)
        this.error = 'データの取得に失敗しました'
      } finally {
        this.loading = false
      }
    },

    changeAddress() {
      // プロフィール設定ページに遷移
      this.$router.push('/profile/setup')
    },

    async handlePurchase() {
      if (this.item.is_sold) {
        this.errorMessage = 'この商品は売り切れです'
        return
      }

      if (!this.canPurchase) {
        this.errorMessage = '配送先住所を設定してください'
        return
      }

      this.isPurchasing = true
      this.errorMessage = ''

      try {
        // 購入APIを呼び出す
        const result = await this.$store.dispatch('purchases/createPurchase', {
          itemId: this.item.id,
          paymentMethod: this.paymentMethod,
        })

        if (result.success) {
          // 購入完了後、マイページまたはホームに遷移
          alert('購入が完了しました')
          this.$router.push('/mypage')
        } else {
          this.errorMessage = result.message || '購入処理に失敗しました'
        }
      } catch (error) {
        console.error('購入エラー:', error)
        this.errorMessage = error.response?.data?.error || '購入処理に失敗しました'
      } finally {
        this.isPurchasing = false
      }
    },
  },
}
</script>
