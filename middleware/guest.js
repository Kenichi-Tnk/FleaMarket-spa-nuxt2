// ログイン済みユーザーがアクセスできないページ（ログイン、会員登録など）を制御
export default function ({ store, redirect }) {
  // ユーザーがログイン済みの場合、ホームにリダイレクト
  if (store.state.auth?.user) {
    return redirect('/')
  }
}
