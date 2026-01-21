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
                            <p class="description-text" style="white-space: pre-line;">{{ item.description }}</p>
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
                                            <li v-for="category in item.categories" :key="category.id" class="category-item">
                                                {{ category.name }}
                                            </li>
                                        </ul>
                                        <span v-else>未設定</span>
                                    </td>
                                </tr>
                                <tr class="meta-item">
                                    <th class="meta-label">商品の状態</th>
                                    <td class="meta-value">{{ item.condition ? item.condition.name : '未設定' }}</td>
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
        // 認証状態の確認を待ってから商品詳細を取得
        await this.$store.dispatch('auth/checkAuth')
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

                // 商品詳細を取得
                const itemResult = await this.$store.dispatch('items/fetchItem', itemId)
                if (!itemResult.success) {
                    throw new Error(itemResult.message)
                }
                this.item = itemResult.item

                // コメントを取得
                const commentsResponse = await this.$axios.get(`/items/${itemId}/comments`)
                this.comments = commentsResponse.data.comments || []

                // お気に入り情報（認証済みの場合のみ）
                if (this.isAuthenticated) {
                    this.isFavorited = this.item.is_favorited || false
                }
                this.favoriteCount = this.item.favorites_count || 0
            } catch (error) {
                console.error('商品詳細の取得に失敗しました:', error)
                this.error = '商品詳細の取得に失敗しました'
            } finally {
                this.loading = false
            }
        },

        async toggleFavorite() {
            if (!this.isAuthenticated) {
                this.$router.push(`/login?redirect=${this.$route.fullPath}`)
                return
            }

            try {
                const result = await this.$store.dispatch('favorites/toggleFavorite', this.item.id)
                if (result.success) {
                    this.isFavorited = result.isFavorited
                    this.favoriteCount += result.isFavorited ? 1 : -1
                }
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
                const response = await this.$axios.post(`/items/${this.item.id}/comments`, {
                    content: this.commentText,
                })
                
                if (response.data.comment) {
                    this.comments.unshift(response.data.comment)
                    this.commentText = ''
                }
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
