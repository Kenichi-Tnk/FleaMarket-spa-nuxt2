<template>
    <div class="mypage">
        <div class="mypage-container">
            <!-- ローディング -->
            <div v-if="loading" class="loading-container">
                <div class="loading"></div>
                <span class="loading-text">読み込み中...</span>
            </div>

            <!-- ユーザー情報と商品一覧 -->
            <template v-else>
                <!-- ユーザー情報 -->
                <div class="user">
                    <div class="user__info">
                        <div class="user__img">
                            <img
                                v-if="user.profile && user.profile.img_url"
                                :src="getImageUrl(user.profile.img_url)"
                                alt="プロフィール画像"
                                class="user__icon"
                            >
                            <img
                                v-else
                                src="/images/icon.png"
                                alt="デフォルトアイコン"
                                class="user__icon"
                            >
                        </div>
                        <p class="user__name">{{ user.name }}</p>
                    </div>
                    <div class="mypage__user--btn">
                        <nuxt-link to="/profile/setup" class="btn2">
                        プロフィールを編集
                        </nuxt-link>
                    </div>
                </div>

                <!-- タブ -->
                <div class="border">
                    <ul class="border__list">
                        <li>
                            <nuxt-link
                                to="/mypage?page=sell"
                                :class="{ active: currentTab === 'sell' }"
                            >
                                出品した商品
                            </nuxt-link>
                        </li>
                        <li>
                            <nuxt-link
                                to="/mypage?page=buy"
                                :class="{ active: currentTab === 'buy' }"
                            >
                                購入した商品
                            </nuxt-link>
                        </li>
                    </ul>
                </div>

                <!-- 商品一覧 -->
                <div v-if="items.length > 0" class="items">
                    <div v-for="item in items" :key="item.id" class="item">
                        <nuxt-link :to="`/item/${item.id}`">
                        <div class="item__img--container":class="{ sold: item.is_sold }">
                            <img
                            :src="getImageUrl(item.img_url)"
                            :alt="item.name"
                            class="item__img"
                            >
                        </div>
                        <p class="item__name">{{ item.name }}</p>
                        </nuxt-link>
                    </div>
                </div>

                <!-- 空の状態 -->
                <div v-else class="empty-state">
                    <div class="empty-state-icon">📦</div>
                    <p class="empty-state-text">
                        {{ currentTab === 'sell' ? '出品した商品がありません' : '購入した商品がありません' }}
                    </p>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import { getImageUrl } from '~/utils/helpers'

export default {
    name: 'MypagePage',
    // middleware: 'auth', // 後で有効化

    head() {
        return {
            title: 'マイページ',
            link: [
                {
                    rel: 'stylesheet',
                    href: '/css/pages/mypage.css',
                },
            ],
        }
    },

    data() {
        return {
            user: {
                id: null,
                name: '',
                profile: {
                    img_url: null,
                },
            },
            items: [],
            loading: true,
            currentTab: 'sell',
        }
    },

    watch: {
        '$route.query.page': {
            handler(newPage) {
                this.currentTab = newPage || 'sell'
                this.fetchItems()
            },
            immediate: true,
        },
    },

    async mounted() {
        await this.fetchUserAndItems()
    },

    methods: {
        getImageUrl,

        async fetchUserAndItems() {
            this.loading = true

            try {
                // 本番環境では実際のAPIを呼び出す
                // const [userResponse, itemsResponse] = await Promise.all([
                //   this.$axios.get('/user'),
                //   this.$axios.get(`/mypage/items?type=${this.currentTab}`),
                // ])
                // this.user = userResponse.data.user
                // this.items = itemsResponse.data.items

                // ダミーデータ
                await new Promise(resolve => setTimeout(resolve, 500))
                this.user = this.getDummyUser()
                this.items = this.getDummyItems()
            } catch (error) {
                console.error('データ取得エラー:', error)
            } finally {
                this.loading = false
            }
        },

        async fetchItems() {
            try {
                // 本番環境では実際のAPIを呼び出す
                // const response = await this.$axios.get(`/mypage/items?type=${this.currentTab}`)
                // this.items = response.data.items

                // ダミーデータ
                await new Promise(resolve => setTimeout(resolve, 300))
                this.items = this.getDummyItems()
            } catch (error) {
                console.error('商品取得エラー:', error)
            }
        },

        getDummyUser() {
            return {
                id: 1,
                name: '山田太郎',
                profile: {
                    img_url: null, // またはプロフィール画像のパス
                },
            }
        },

        getDummyItems() {
            if (this.currentTab === 'sell') {
                // 出品した商品
                return [
                {
                    id: 1,
                    name: 'Armani 高級腕時計',
                    img_url: 'images/items/Armani+Mens+Clock.jpg',
                    is_sold: false,
                },
                {
                    id: 2,
                    name: 'HDD ハードディスク 2TB',
                    img_url: 'images/items/HDD+Hard+Disk.jpg',
                    is_sold: true,
                },
                {
                    id: 3,
                    name: '本革ビジネスシューズ',
                    img_url: 'images/items/Leather+Shoes+Product+Photo.jpg',
                    is_sold: false,
                },
                {
                    id: 4,
                    name: 'プロフェッショナルカメラ',
                    img_url: 'images/items/Living+Room+Laptop.jpg',
                    is_sold: false,
                },
                ]
            } else {
                // 購入した商品
                return [
                    {
                        id: 5,
                        name: 'MacBook Pro 13インチ',
                        img_url: 'images/items/Macbook+Pro.jpg',
                        is_sold: true,
                    },
                    {
                        id: 6,
                        name: 'ワイヤレスヘッドフォン',
                        img_url: 'images/items/Microphone+Black.jpg',
                        is_sold: true,
                    },
                ]
            }
        },
    },
}
</script>
