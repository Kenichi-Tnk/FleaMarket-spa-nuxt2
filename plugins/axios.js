// Axiosのグローバル設定とインターセプター
export default function ({ $axios, store, redirect }) {
  // リクエストインターセプター
  $axios.onRequest((config) => {
    // 認証トークンがある場合、ヘッダーに追加
    const token = store.state.auth?.token
    if (token) {
      config.headers.common.Authorization = `Bearer ${token}`
    }
    return config
  })

  // レスポンスインターセプター
  $axios.onResponse((response) => {
    return response
  })

  // エラーインターセプター
  $axios.onError((error) => {
    const code = parseInt(error.response && error.response.status)

    // 401: 認証エラー
    if (code === 401) {
      // トークンをクリアしてログインページにリダイレクト
      store.commit('auth/clearAuth')
      redirect('/login')
    }

    // 403: 権限エラー
    if (code === 403) {
      // エラーページにリダイレクト
      redirect('/error')
    }

    // 500: サーバーエラー
    if (code === 500) {
      console.error('Server error:', error)
    }

    return Promise.reject(error)
  })
}
