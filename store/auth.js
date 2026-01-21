// 認証状態管理

export const state = () => ({
  user: null,
  token: null,
})

export const mutations = {
  setUser(state, user) {
    state.user = user
  },
  setToken(state, token) {
    state.token = token
    // ローカルストレージに保存
    if (process.client) {
      if (token) {
        localStorage.setItem('auth_token', token)
      } else {
        localStorage.removeItem('auth_token')
      }
    }
  },
  clearAuth(state) {
    state.user = null
    state.token = null
    if (process.client) {
      localStorage.removeItem('auth_token')
    }
  },
}

export const actions = {
  // ログイン
  async login({ commit }, credentials) {
    try {
      const response = await this.$axios.post('/login', credentials)
      const { access_token, user } = response.data
      
      commit('setToken', access_token)
      commit('setUser', user)
      
      return { success: true, user }
    } catch (error) {
      console.error('Login error:', error)
      return { 
        success: false, 
        message: error.response?.data?.error || 'ログインに失敗しました' 
      }
    }
  },

  // 会員登録
  async register({ commit }, userData) {
    try {
      const response = await this.$axios.post('/register', userData)
      const { access_token, user } = response.data
      
      commit('setToken', access_token)
      commit('setUser', user)
      
      return { success: true, user }
    } catch (error) {
      console.error('Register error:', error)
      return { 
        success: false, 
        message: error.response?.data?.error || '会員登録に失敗しました',
        errors: error.response?.data?.errors || {}
      }
    }
  },

  // ログアウト
  async logout({ commit }) {
    try {
      await this.$axios.post('/logout')
    } catch (error) {
      console.error('Logout error:', error)
    } finally {
      commit('clearAuth')
    }
  },

  // 認証チェック（ページ読み込み時にトークンから復元）
  async checkAuth({ commit, state }) {
    if (process.client) {
      const token = localStorage.getItem('auth_token')
      if (token) {
        commit('setToken', token)
        // ユーザー情報を必ず取得
        try {
          const response = await this.$axios.get('/user')
          commit('setUser', response.data.user)
          return { success: true, user: response.data.user }
        } catch (error) {
          console.error('Check auth error:', error)
          commit('clearAuth')
          return { success: false }
        }
      }
    }
    return { success: false }
  },
}

export const getters = {
  isAuthenticated(state) {
    return !!state.token && !!state.user
  },
  user(state) {
    return state.user
  },
}
