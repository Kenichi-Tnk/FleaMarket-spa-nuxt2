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

<style scoped>
.index-page {
  min-height: calc(100vh - 200px);
  background-color: #f5f5f5;
}

.border {
  background: white;
  border-bottom: 2px solid #eee;
}

.border__list {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  list-style: none;
  padding: 0;
}

.border__list li {
  margin-right: 2rem;
}

.border__list li a {
  display: block;
  padding: 1rem 0.5rem;
  text-decoration: none;
  color: #666;
  font-weight: 600;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
}

.border__list li.active a {
  color: #000;
  border-bottom-color: #ff333f;
}

.border__list li a:hover {
  color: #000;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.loading-container {
  display: flex;
  justify-content: center;
  padding: 4rem 0;
}

.loading {
  display: inline-block;
  width: 40px;
  height: 40px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-radius: 50%;
  border-top-color: #ff333f;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.no-items {
  text-align: center;
  padding: 4rem 0;
  color: #666;
  font-size: 1.1rem;
}

.items {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 2rem;
}

.item {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.item:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.item a {
  text-decoration: none;
  color: inherit;
}

.item__img--container {
  position: relative;
  width: 100%;
  padding-bottom: 100%; /* 正方形 */
  background-color: #f8f8f8;
  overflow: hidden;
}

.item__img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.item__img--container.sold::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
}

.sold-label {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background-color: #ff333f;
  color: white;
  padding: 0.5rem 2rem;
  font-size: 1.2rem;
  font-weight: bold;
  border-radius: 4px;
  z-index: 10;
}

.item__name {
  padding: 1rem;
  font-size: 0.95rem;
  color: #333;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* レスポンシブ */
@media (max-width: 1024px) {
  .items {
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
  }
}

@media (max-width: 768px) {
  .items {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }

  .border__list li {
    margin-right: 1rem;
  }

  .container {
    padding: 1.5rem 1rem;
  }
}

@media (max-width: 480px) {
  .items {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.75rem;
  }

  .item__name {
    font-size: 0.85rem;
    padding: 0.75rem;
  }
}
</style>
