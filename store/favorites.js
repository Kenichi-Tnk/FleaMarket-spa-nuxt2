// お気に入り管理

export const state = () => ({
  favorites: [],
  pagination: {
    currentPage: 1,
    lastPage: 1,
    total: 0,
    perPage: 20,
  },
})

export const mutations = {
  setFavorites(state, data) {
    state.favorites = data.data
    state.pagination = {
      currentPage: data.current_page,
      lastPage: data.last_page,
      total: data.total,
      perPage: data.per_page,
    }
  },
  toggleFavorite(state, { itemId, isFavorited }) {
    // 商品リストのお気に入り状態を更新
    const item = state.favorites.find((fav) => fav.id === itemId)
    if (item) {
      item.is_favorited = isFavorited
    }
  },
}

export const actions = {
  // お気に入り一覧取得
  async fetchFavorites({ commit }, params = {}) {
    try {
      const response = await this.$axios.get('/favorites', { params })
      commit('setFavorites', response.data)
      return { success: true }
    } catch (error) {
      console.error('Fetch favorites error:', error)
      return { success: false, message: 'お気に入りの取得に失敗しました' }
    }
  },

  // お気に入り切り替え
  async toggleFavorite({ commit }, itemId) {
    try {
      const response = await this.$axios.post(`/items/${itemId}/favorite`)
      commit('toggleFavorite', {
        itemId,
        isFavorited: response.data.is_favorited,
      })
      return {
        success: true,
        isFavorited: response.data.is_favorited,
        message: response.data.message,
      }
    } catch (error) {
      console.error('Toggle favorite error:', error)
      return {
        success: false,
        message:
          error.response?.data?.message || 'お気に入りの更新に失敗しました',
      }
    }
  },
}

export const getters = {
  favorites(state) {
    return state.favorites
  },
  pagination(state) {
    return state.pagination
  },
}
