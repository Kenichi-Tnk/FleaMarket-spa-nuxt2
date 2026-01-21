// ユーティリティ関数

/**
 * 日付を指定フォーマットに変換
 * @param {String|Date} date - 変換する日付
 * @param {String} format - フォーマット (YYYY-MM-DD, YYYY/MM/DD等)
 * @returns {String} フォーマットされた日付文字列
 */
export function formatDate(date, format = 'YYYY-MM-DD') {
  if (!date) return ''
  
  const d = new Date(date)
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  const hours = String(d.getHours()).padStart(2, '0')
  const minutes = String(d.getMinutes()).padStart(2, '0')

  return format
    .replace('YYYY', year)
    .replace('MM', month)
    .replace('DD', day)
    .replace('HH', hours)
    .replace('mm', minutes)
}

/**
 * 金額を通貨フォーマットに変換
 * @param {Number} amount - 金額
 * @param {String} currency - 通貨記号
 * @returns {String} フォーマットされた金額
 */
export function formatCurrency(amount, currency = '¥') {
  if (amount === null || amount === undefined) return ''
  return `${currency}${Number(amount).toLocaleString()}`
}

/**
 * 文字列を指定文字数で切り詰める
 * @param {String} text - 対象文字列
 * @param {Number} length - 最大文字数
 * @param {String} suffix - 末尾に追加する文字列
 * @returns {String} 切り詰められた文字列
 */
export function truncate(text, length = 100, suffix = '...') {
  if (!text || text.length <= length) return text
  return text.substring(0, length) + suffix
}

/**
 * ファイルサイズを読みやすい形式に変換
 * @param {Number} bytes - バイト数
 * @returns {String} フォーマットされたファイルサイズ
 */
export function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

/**
 * バリデーション: メールアドレス
 * @param {String} email - メールアドレス
 * @returns {Boolean} 有効な場合true
 */
export function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

/**
 * バリデーション: パスワード（8文字以上、英数字含む）
 * @param {String} password - パスワード
 * @returns {Boolean} 有効な場合true
 */
export function isValidPassword(password) {
  // 8文字以上、英字と数字を含む
  const re = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/
  return re.test(password)
}

/**
 * 画像URLの生成（相対パスを絶対URLに変換）
 * @param {String} path - 画像のパス
 * @param {String} apiBaseUrl - APIのベースURL（オプション）
 * @returns {String} 完全なURL
 */
export function getImageUrl(path, apiBaseUrl = 'http://localhost:8000') {
  if (!path) return '/images/no-image.png'
  if (path.startsWith('http')) return path
  
  // storage/app/public/ からのパス（uploads/で始まる）の場合
  if (path.startsWith('uploads/')) {
    return `${apiBaseUrl}/storage/${path}`
  }
  
  // static/配下の画像を参照（先頭に/を追加）
  return path.startsWith('/') ? path : `/${path}`
}

/**
 * クエリパラメータをオブジェクトに変換
 * @param {String} query - クエリ文字列
 * @returns {Object} パラメータオブジェクト
 */
export function parseQuery(query) {
  if (!query) return {}
  return Object.fromEntries(new URLSearchParams(query))
}

/**
 * デバウンス関数
 * @param {Function} func - 実行する関数
 * @param {Number} wait - 待機時間（ミリ秒）
 * @returns {Function} デバウンスされた関数
 */
export function debounce(func, wait = 300) {
  let timeout
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}
