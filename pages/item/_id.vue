<template>
    <div class="item-detail-page">
        <div class="container">
            <!-- ローディング -->
            <div v-if="loading" class="loading-container">
                <div class="loading"></div>
            </div>

            <!-- エラー -->
            <div v-else-if="error" class="error-message">
                {{ error }}
            </div>

            <!-- 商品詳細 -->
            <template v-else-if="item">
                <div class="item-detail">
                    <!-- 左側：画像 -->
                    <div class="item-image-section">
                        <div class="item-image-container" :class="{ sold: item.is_sold }">
                            <img
                                :src="getImageUrl(item.img_url)"
                                :alt="item.name"
                                class="item-image"
                            >
                            <div v-if="item.is_sold" class="sold-badge">
                                Sold
                            </div>
                        </div>
                    </div>

                    <!-- 右側：商品情報 -->
                    <div class="item-info-section">
                        <h1 class="item-name">{{ item.name }}</h1>
                        <div class="item-price">¥ {{ formatPrice(item.price) }}</div>

                        <!-- アクション -->
                        <div class="item-actions">
                            <button
                                class="btn btn-favorite"
                                :class="{ active: isFavorited }"
                                @click="toggleFavorite"
                                :disabled="!isAuthenticated"
                            >
                                <span class="favorite-icon">{{ isFavorited ? '♥' : '♡' }}</span>
                                <span class="like-count">{{ favoriteCount }}</span>
                            </button>
                            <div class="item-comment-count">
                                <span class="comment-icon">💬</span>
                                <span class="comment-count">{{ comments.length }}</span>
                            </div>
                        </div>

                        <!-- 購入ボタン -->
                        <template v-if="item.is_sold">
                            <button class="btn btn-purchase" disabled>
                                売り切れました
                            </button>
                        </template>
                        <template v-else-if="isOwnItem">
                            <button class="btn btn-purchase" disabled>
                                購入できません
                            </button>
                        </template>
                        <template v-else>
                            <button
                                class="btn btn-purchase"
                                @click="handlePurchase"
                                :disabled="!isAuthenticated"
                            >
                                購入手続きへ
                            </button>
                        </template>

                        <!-- 商品説明 -->
                        <div class="item-description">
                            <h2 class="description-title">商品説明</h2>
                            <p class="description-text">{{ item.description }}</p>
                        </div>

                        <!-- 商品の情報 -->
                        <div class="item-meta">
                            <h2 class="meta-title">商品の情報</h2>
                            <table class="item-table">
                                <tr class="meta-item">
                                    <th class="meta-label">ブランド</th>
                                    <td class="meta-value">{{ item.brand || '未入力' }}</td>
                                </tr>
                                <tr class="meta-item">
                                    <th class="meta-label">カテゴリー</th>
                                    <td class="meta-value">
                                        <ul v-if="item.categories && item.categories.length" class="category-list">
                                            <li v-for="category in item.categories" :key="category" class="category-item">
                                                {{ category }}
                                            </li>
                                        </ul>
                                        <span v-else>未設定</span>
                                    </td>
                                </tr>
                                <tr class="meta-item">
                                    <th class="meta-label">商品の状態</th>
                                    <td class="meta-value">{{ item.condition || '未設定' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- コメントセクション -->
                        <div class="comments-section">
                            <h2 class="comments-title">コメント({{ comments.length }})</h2>

                            <!-- コメント一覧 -->
                            <ul v-if="comments.length > 0" class="comments-list">
                                <li v-for="comment in comments" :key="comment.id" class="comment-item">
                                    <div class="comment-header">
                                        <div class="comment-avatar">
                                            <img
                                                v-if="comment.user?.avatar"
                                                :src="getImageUrl(comment.user.avatar)"
                                                :alt="comment.user.name"
                                            >
                                        </div>
                                        <span class="comment-user">{{ comment.user?.name || 'ユーザー' }}</span>
                                    </div>
                                    <p class="comment-text">{{ comment.content }}</p>
                                </li>
                            </ul>

                            <div v-else class="no-comments">
                                まだコメントがありません
                            </div>

                            <!-- コメント投稿フォーム -->
                            <form v-if="isAuthenticated" @submit.prevent="handleCommentSubmit" class="comment-form">
                                <div class="form-group">
                                    <label for="comment" class="form-label">商品へのコメント</label>
                                    <textarea
                                        id="comment"
                                        v-model="commentText"
                                        class="form-control"
                                        placeholder="商品についてコメントする"
                                        rows="4"
                                        :disabled="commentSubmitting"
                                    ></textarea>
                                </div>
                                <button
                                    type="submit"
                                    class="btn btn-submit"
                                    :disabled="!commentText.trim() || commentSubmitting"
                                >
                                    {{ commentSubmitting ? '送信中...' : 'コメントを送信する' }}
                                </button>
                            </form>

                            <div v-else class="login-prompt">
                                コメントを投稿するには<nuxt-link to="/login">ログイン</nuxt-link>してください
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { getImageUrl, formatCurrency, formatDate } from '~/utils/helpers'

export default {
    name: 'ItemDetailPage',

    head() {
        return {
        title: this.item?.name || '商品詳細',
        link: [
            {
            rel: 'stylesheet',
            href: '/css/pages/item-detail.css',
            },
        ],
        }
    },

    data() {
        return {
        item: null,
        comments: [],
        loading: true,
        error: null,
        isFavorited: false,
        favoriteCount: 0,
        commentText: '',
        commentSubmitting: false,
        }
    },

    computed: {
        isAuthenticated() {
            return this.$store.getters['auth/isAuthenticated']
        },

        isOwnItem() {
            // 自分が出品した商品かどうか
            return this.item?.seller?.id === this.$store.state.auth?.user?.id
        },
    },

    async mounted() {
        await this.fetchItemDetail()
    },

    methods: {
        getImageUrl,
        formatCurrency,
        formatDate,

        formatPrice(price) {
            // 3桁ごとにカンマを追加（¥なし）
            return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        },

        async fetchItemDetail() {
            this.loading = true
            this.error = null

            try {
                const itemId = this.$route.params.id

                // 本番環境では実際のAPIを呼び出す
                // const response = await this.$axios.get(`/items/${itemId}`)
                // this.item = response.data.item
                // this.comments = response.data.comments || []
                // this.isFavorited = response.data.is_favorited || false
                // this.favoriteCount = response.data.favorite_count || 0

                // ダミーデータ
                await new Promise(resolve => setTimeout(resolve, 500))
                this.item = this.getDummyItem(itemId)
                this.comments = this.getDummyComments()
                this.isFavorited = false
                this.favoriteCount = Math.floor(Math.random() * 20)
            } catch (error) {
                console.error('商品詳細の取得に失敗しました:', error)
                this.error = '商品詳細の取得に失敗しました'
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
                    description: '状態の良いArmaniの腕時計です。\n数回使用しましたが、目立った傷や汚れはありません。\nビジネスシーンでもカジュアルでも使える洗練されたデザインです。\n\n付属品：箱、保証書あり',
                    img_url: 'images/items/Armani+Mens+Clock.jpg',
                    categories: ['メンズ', '時計'],
                    condition: '目立った傷や汚れなし',
                    is_sold: false,
                    seller: {
                        id: 2,
                        name: '田中太郎',
                        avatar: null,
                        items_count: 15,
                    },
                },
                {
                    id: 2,
                    name: 'HDD ハードディスク 2TB',
                    brand: null,
                    price: 8000,
                    description: '外付けHDD 2TBです。\n使用期間は約1年程度。\n動作確認済みで問題なく使用できます。\n\nデータ保存やバックアップにお使いください。',
                    img_url: 'images/items/HDD+Hard+Disk.jpg',
                    categories: ['家電', 'PC周辺機器'],
                    condition: '良好',
                    is_sold: false,
                    seller: {
                        id: 3,
                        name: '佐藤花子',
                        avatar: null,
                        items_count: 8,
                    },
                },
                {
                    id: 3,
                    name: '本革ビジネスシューズ',
                    brand: null,
                    price: 12000,
                    description: '本革を使用した高品質なビジネスシューズです。\nサイズ：26.0cm\n\n数回着用しましたが、サイズが合わなかったため出品します。\nまだまだ綺麗な状態です。',
                    img_url: 'images/items/Leather+Shoes+Product+Photo.jpg',
                    categories: ['メンズ', '靴'],
                    condition: '未使用に近い',
                    is_sold: false,
                    seller: {
                        id: 4,
                        name: '山田一郎',
                        avatar: null,
                        items_count: 3,
                    },
                },
            ]

            return items.find(item => item.id === parseInt(id)) || items[0]
        },

        getDummyComments() {
            return [
                {
                    id: 1,
                    content: '商品の状態について詳しく教えていただけますか？',
                    user: {
                        id: 5,
                        name: '鈴木美咲',
                        avatar: null,
                    },
                    created_at: '2026-01-10T10:30:00Z',
                },
                {
                    id: 2,
                    content: '状態は良好です。写真の通り、目立った傷はありません。',
                    user: {
                        id: 2,
                        name: '田中太郎',
                        avatar: null,
                    },
                    created_at: '2026-01-10T11:00:00Z',
                },
            ]
        },

        async toggleFavorite() {
            if (!this.isAuthenticated) {
                this.$router.push(`/login?redirect=${this.$route.fullPath}`)
                return
            }

            try {
                // 本番環境では実際のAPIを呼び出す
                // await this.$axios.post(`/items/${this.item.id}/favorite`)

                this.isFavorited = !this.isFavorited
                this.favoriteCount += this.isFavorited ? 1 : -1
            } catch (error) {
                console.error('お気に入り登録に失敗しました:', error)
            }
        },

        handlePurchase() {
            if (!this.isAuthenticated) {
                this.$router.push(`/login?redirect=${this.$route.fullPath}`)
                return
            }

            // 購入ページに遷移
            this.$router.push(`/purchase/${this.item.id}`)
        },

        async handleCommentSubmit() {
            if (!this.commentText.trim() || this.commentSubmitting) {
                return
            }

            this.commentSubmitting = true

            try {
                // 本番環境では実際のAPIを呼び出す
                // const response = await this.$axios.post(`/items/${this.item.id}/comments`, {
                //   content: this.commentText,
                // })
                // this.comments.unshift(response.data.comment)

                // ダミーデータ
                const newComment = {
                    id: Date.now(),
                    content: this.commentText,
                    user: {
                        id: this.$store.state.auth?.user?.id,
                        name: this.$store.state.auth?.user?.name || 'あなた',
                        avatar: this.$store.state.auth?.user?.avatar,
                    },
                    created_at: new Date().toISOString(),
                }
                this.comments.unshift(newComment)
                this.commentText = ''
            } catch (error) {
                console.error('コメントの投稿に失敗しました:', error)
                alert('コメントの投稿に失敗しました')
            } finally {
                this.commentSubmitting = false
            }
        },
    },
}
</script>
