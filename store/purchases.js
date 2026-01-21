// 購入管理

export const state = () => ({
  purchases: [],
  purchase: null,
  pagination: {
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 20,
  },
})

export const mutations = {
  setPurchases(state, data) {
    state.purchases = data.data
    state.pagination = {
      currentPage: data.current_page,
      lastPage: data.last_page,
      total: data.total,
      perPage: data.per_page,
    }
  },
  setPurchase(state, purchase) {
    state.purchase = purchase
  },
}

export const actions = {
  // 購入履歴取得
  async fetchPurchases({ commit }, params = {}) {
    try {
      const response = await this.$axios.get('/purchases', { params })
      commit('setPurchases', response.data)
      return { success: true }
    } catch (error) {
      console.error('Fetch purchases error:', error)
      return { success: false, message: '購入履歴の取得に失敗しました' }
    }
  },

  // 購入詳細取得
  async fetchPurchase({ commit }, purchaseId) {
    try {
      const response = await this.$axios.get(`/purchases/${purchaseId}`)
      commit('setPurchase', response.data.purchase)
      return { success: true, purchase: response.data.purchase }
    } catch (error) {
      console.error('Fetch purchase error:', error)
      return { success: false, message: '購入情報の取得に失敗しました' }
    }
  },

  // 商品購入
  async createPurchase({ commit }, { itemId, paymentMethod }) {
    try {
      const response = await this.$axios.post(`/items/${itemId}/purchase`, {
        payment_method: paymentMethod,
      })
      return { success: true, purchase: response.data.purchase }
    } catch (error) {
      console.error('Create purchase error:', error)
      return {
        success: false,
        message: error.response?.data?.error || '購入処理に失敗しました',
      }
    }
  },
}

export const getters = {
  purchases(state) {
    return state.purchases
  },
  purchase(state) {
    return state.purchase
  },
  pagination(state) {
    return state.pagination
  },
}
