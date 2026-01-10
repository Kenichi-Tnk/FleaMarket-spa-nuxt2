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
      items: [],
      currentTab: 'recommend',
      isLoading: false,
    }
  },

  computed: {
    isAuthenticated() {
      return this.$store.getters['auth/isAuthenticated']
    },
  },

  watch: {
    '$route.query'() {
      this.fetchItems()
    },
  },

  mounted() {
    this.fetchItems()
  },

  methods: {
    async fetchItems() {
      this.currentTab = this.$route.query.tab || 'recommend'
      this.isLoading = true

      try {
        let endpoint = '/items'
        
        if (this.currentTab === 'mylist') {
          endpoint = '/items/mylist'
        }

        // クエリパラメータがあれば追加
        const params = {}
        if (this.$route.query.search) {
          params.search = this.$route.query.search
        }

        const response = await this.$axios.get(endpoint, { params })
        this.items = response.data.items || response.data
      } catch (error) {
        console.error('Failed to fetch items:', error)
        // エラー時はダミーデータを表示（開発用）
        this.items = this.getDummyItems()
      } finally {
        this.isLoading = false
      }
    },

    getImageUrl(imgUrl) {
      if (!imgUrl) return '/images/no-image.png'
      if (imgUrl.startsWith('http')) return imgUrl
      // static配下の画像を参照
      return `/${imgUrl}`
    },

    // 開発用ダミーデータ
    getDummyItems() {
      return [
        {
          id: 1,
          name: '腕時計',
          img_url: 'images/items/Armani+Mens+Clock.jpg',
          is_sold: false,
        },
        {
          id: 2,
          name: 'HDD',
          img_url: 'images/items/HDD+Hard+Disk.jpg',
          is_sold: true,
        },
        {
          id: 3,
          name: '玉ねぎ3束',
          img_url: 'images/items/iLoveIMG+d.jpg',
          is_sold: false,
        },
        {
          id: 4,
          name: '革靴',
          img_url: 'images/items/Leather+Shoes+Product+Photo.jpg',
          is_sold: false,
        },
        {
          id: 5,
          name: 'ノートPC',
          img_url: 'images/items/Living+Room+Laptop.jpg',
          is_sold: false,
        },
        {
          id: 6,
          name: 'マイク',
          img_url: 'images/items/Music+Mic+4632231.jpg',
          is_sold: false,
        },
        {
          id: 7,
          name: 'ショルダーバッグ',
          img_url: 'images/items/Purse+fashion+pocket.jpg',
          is_sold: false,
        },
        {
          id: 8,
          name: 'タンブラー',
          img_url: 'images/items/Tumbler+souvenir.jpg',
          is_sold: false,
        },
        {
          id: 9,
          name: 'コーヒーミル',
          img_url: 'images/items/Waitress+with+Coffee+Grinder.jpg',
          is_sold: false,
        },
        {
          id: 10,
          name: 'メイクアップセット',
          img_url: 'images/items/外出メイクアップセット.jpg',
          is_sold: false,
        },
      ]
    },
  },
}
</script>
