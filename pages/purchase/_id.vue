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

        // 本番環境では実際のAPIを呼び出す
        // const [itemResponse, addressResponse] = await Promise.all([
        //   this.$axios.get(`/items/${itemId}`),
        //   this.$axios.get('/user/address'),
        // ])
        // this.item = itemResponse.data.item
        // this.address = addressResponse.data.address

        // ダミーデータ
        await new Promise(resolve => setTimeout(resolve, 500))
        this.item = this.getDummyItem(itemId)
        this.address = this.getDummyAddress()
      } catch (error) {
        console.error('データ取得エラー:', error)
        this.error = 'データの取得に失敗しました'
      } finally {
        this.loading = false
      }
    },

    getDummyItem(id) {
      const items = [
        {
          id: 1,
          name: 'Armani 高級腕時計',
          brand: 'Armani',
          price: 15000,
          img_url: 'images/items/Armani+Mens+Clock.jpg',
        },
        {
          id: 2,
          name: 'HDD ハードディスク 2TB',
          brand: null,
          price: 8000,
          img_url: 'images/items/HDD+Hard+Disk.jpg',
        },
        {
          id: 3,
          name: '本革ビジネスシューズ',
          brand: null,
          price: 12000,
          img_url: 'images/items/Leather+Shoes+Product+Photo.jpg',
        },
      ]

      return items.find(item => item.id === parseInt(id)) || items[0]
    },

    getDummyAddress() {
      return {
        postal_code: '123-4567',
        address: '東京都渋谷区神宮前1-2-3',
        building: 'サンプルマンション101',
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
        // 本番環境では実際のAPIを呼び出す
        // const response = await this.$axios.post(`/purchase/${this.item.id}`, {
        //   payment_method: this.paymentMethod,
        // })

        // カード払いの場合はStripe決済処理
        // if (this.paymentMethod === 'card') {
        //   // Stripe決済処理
        // }

        // ダミー処理
        await new Promise(resolve => setTimeout(resolve, 1500))
        console.log('購入処理:', {
          item_id: this.item.id,
          payment_method: this.paymentMethod,
          address: this.address,
        })

        // 購入完了後、完了ページまたはマイページに遷移
        alert('購入が完了しました')
        this.$router.push('/')
      } catch (error) {
        console.error('購入エラー:', error)
        this.errorMessage = error.response?.data?.message || '購入処理に失敗しました'
      } finally {
        this.isPurchasing = false
      }
    },
  },
}
</script>
