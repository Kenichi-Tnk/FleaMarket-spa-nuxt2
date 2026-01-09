// 認証が必要なページへのアクセスを制御するミドルウェア
export default function ({ store, redirect, route }) {
  // 認証が必要なページで、ユーザーがログインしていない場合
  if (!store.state.auth?.user) {
    // ログインページにリダイレクト（元のURLをクエリパラメータとして保存）
    return redirect(`/login?redirect=${route.fullPath}`)
  }
}
