// 商品管理

export const state = () => ({
  items: [],
  item: null,
  categories: [],
  conditions: [],
  pagination: {
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 20,
  },
})

export const mutations = {
  setItems(state, data) {
    state.items = data.data
    state.pagination = {
      currentPage: data.current_page,
      lastPage: data.last_page,
      total: data.total,
      perPage: data.per_page,
    }
  },
  setItem(state, item) {
    state.item = item
  },
  setCategories(state, categories) {
    state.categories = categories
  },
  setConditions(state, conditions) {
    state.conditions = conditions
  },
  clearItem(state) {
    state.item = null
  },
}

export const actions = {
  // 商品一覧取得
  async fetchItems({ commit }, params = {}) {
    try {
      const response = await this.$axios.get('/items', { params })
      commit('setItems', response.data)
      return { success: true }
    } catch (error) {
      console.error('Fetch items error:', error)
      return { success: false, message: '商品一覧の取得に失敗しました' }
    }
  },

  // 商品詳細取得
  async fetchItem({ commit }, itemId) {
    try {
      const response = await this.$axios.get(`/items/${itemId}`)
      commit('setItem', response.data.item)
      return { success: true, item: response.data.item }
    } catch (error) {
      console.error('Fetch item error:', error)
      return { success: false, message: '商品の取得に失敗しました' }
    }
  },

  // 商品登録
  async createItem({ commit }, itemData) {
    try {
      const response = await this.$axios.post('/items', itemData)
      return { success: true, item: response.data.item }
    } catch (error) {
      console.error('Create item error:', error)
      return {
        success: false,
        message: error.response?.data?.message || '商品の登録に失敗しました',
        errors: error.response?.data?.errors || {},
      }
    }
  },

  // 商品更新
  async updateItem({ commit }, { itemId, itemData }) {
    try {
      const response = await this.$axios.put(`/items/${itemId}`, itemData)
      commit('setItem', response.data.item)
      return { success: true, item: response.data.item }
    } catch (error) {
      console.error('Update item error:', error)
      return {
        success: false,
        message: error.response?.data?.message || '商品の更新に失敗しました',
        errors: error.response?.data?.errors || {},
      }
    }
  },

  // 商品削除
  async deleteItem({ commit }, itemId) {
    try {
      await this.$axios.delete(`/items/${itemId}`)
      return { success: true }
    } catch (error) {
      console.error('Delete item error:', error)
      return {
        success: false,
        message: error.response?.data?.message || '商品の削除に失敗しました',
      }
    }
  },

  // カテゴリ一覧取得
  async fetchCategories({ commit }) {
    try {
      const response = await this.$axios.get('/categories')
      commit('setCategories', response.data.categories)
      return { success: true }
    } catch (error) {
      console.error('Fetch categories error:', error)
      return { success: false }
    }
  },

  // 商品状態一覧取得
  async fetchConditions({ commit }) {
    try {
      const response = await this.$axios.get('/conditions')
      commit('setConditions', response.data.conditions)
      return { success: true }
    } catch (error) {
      console.error('Fetch conditions error:', error)
      return { success: false }
    }
  },
}

export const getters = {
  items(state) {
    return state.items
  },
  item(state) {
    return state.item
  },
  categories(state) {
    return state.categories
  },
  conditions(state) {
    return state.conditions
  },
  pagination(state) {
    return state.pagination
  },
}
