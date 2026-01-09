// Vuexストアのルート
// Nuxt.jsはクラシックモードではなくモジュールモードを使用するため、
// このファイルは空でも構いません。各ストアモジュールは独立して動作します。

export const actions = {
  // アプリケーション初期化時の処理
  async nuxtClientInit({ dispatch }) {
    // 認証状態の復元
    await dispatch('auth/checkAuth')
  },
}
