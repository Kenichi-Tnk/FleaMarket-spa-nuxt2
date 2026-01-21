<template>
  <div class="index-page">
    <!-- タブ切り替え -->
    <div class="border">
      <ul class="border__list">
        <li :class="{ active: currentTab === 'recommend' }">
          <nuxt-link :to="{ path: '/', query: { tab: 'recommend' } }">
            おすすめ
          </nuxt-link>
        </li>
        <li v-if="isAuthenticated" :class="{ active: currentTab === 'mylist' }">
          <nuxt-link :to="{ path: '/', query: { tab: 'mylist' } }">
            マイリスト
          </nuxt-link>
        </li>
      </ul>
    </div>

    <!-- 商品一覧 -->
    <div class="container">
      <div v-if="isLoading" class="loading-container">
        <div class="loading"></div>
      </div>

      <div v-else-if="items.length === 0" class="no-items">
        <p v-if="currentTab === 'mylist'">マイリストに商品がありません</p>
        <p v-else>商品がありません</p>
      </div>

      <div v-else class="items">
        <div v-for="item in items" :key="item.id" class="item">
          <nuxt-link :to="`/item/${item.id}`">
            <div class="item__img--container" :class="{ sold: item.is_sold }">
              <img
                :src="getImageUrl(item.img_url)"
                class="item__img"
                alt="商品画像"
              >
              <div v-if="item.is_sold" class="sold-label">Sold</div>
            </div>
            <p class="item__name">{{ item.name }}</p>
          </nuxt-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getImageUrl } from '~/utils/helpers'

export default {
  name: 'IndexPage',

  head() {
    return {
      link: [
        {
          rel: 'stylesheet',
          href: '/css/pages/index.css',
        },
      ],
    }
  },

  data() {
    return {
      currentTab: 'recommend',
      isLoading: false,
    }
  },

  computed: {
    isAuthenticated() {
      return this.$store.getters['auth/isAuthenticated']
    },
    items() {
      if (this.currentTab === 'mylist') {
        return this.$store.getters['favorites/favorites']
      }
      return this.$store.getters['items/items']
    },
  },

  watch: {
    '$route.query'() {
      this.fetchItems()
    },
    isAuthenticated(newVal, oldVal) {
      // 認証状態が変更されたらマイリストタブの場合は再取得
      if (newVal !== oldVal && this.currentTab === 'mylist') {
        this.fetchItems()
      }
    },
  },

  async mounted() {
    // 認証状態の確認を待ってから商品を取得
    await this.$store.dispatch('auth/checkAuth')
    await this.fetchItems()
  },

  methods: {
    async fetchItems() {
      this.currentTab = this.$route.query.tab || 'recommend'
      this.isLoading = true

      try {
        const params = {}
        
        // 検索クエリがあれば追加
        if (this.$route.query.search) {
          params.keyword = this.$route.query.search
        }

        if (this.currentTab === 'mylist') {
          // マイリスト（お気に入り）を取得
          await this.$store.dispatch('favorites/fetchFavorites', params)
        } else {
          // おすすめ（全商品）を取得
          await this.$store.dispatch('items/fetchItems', params)
        }
      } catch (error) {
        console.error('Failed to fetch items:', error)
      } finally {
        this.isLoading = false
      }
    },

    getImageUrl(imgUrl) {
      return getImageUrl(imgUrl, this.$config.apiBaseUrl.replace('/api', ''))
    },
  },
}
</script>
