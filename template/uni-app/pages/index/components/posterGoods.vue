<template>
  <view :style="[boxStyle]" class="poster-goods-container">
    <!-- Multiple poster groups -->
    <view
      class="poster-group"
      v-for="(poster, pIndex) in posterList"
      :key="pIndex"
      :style="{ marginBottom: pIndex === posterList.length - 1 ? 0 : spacing + 'rpx', borderRadius: posterRadius + 'rpx' }"
    >
      <!-- Poster image -->
      <view
        class="poster-image"
        :style="{ borderRadius: posterRadius + 'rpx ' + posterRadius + 'rpx 0 0' }"
        @tap="goLink(poster.posterLink)"
      >
        <image
          v-if="poster.posterImg"
          :src="poster.posterImg"
          mode="widthFix"
          class="poster-img"
        />
        <view v-else class="empty-poster">
          <text class="iconfont icon-tupian"></text>
        </view>
      </view>

      <!-- Associated goods (3 items) -->
      <view class="goods-row">
        <view
          class="goods-item"
          v-for="(goods, gIndex) in getGoodsList(poster)"
          :key="gIndex"
          @tap="goDetail(goods)"
        >
          <view class="goods-img-box">
            <image
              v-if="goods.image"
              :src="goods.image"
              mode="aspectFill"
              class="goods-img"
              :style="{ borderRadius: goodsRadius + 'rpx' }"
            />
            <view v-else class="empty-goods" :style="{ borderRadius: goodsRadius + 'rpx' }">
              <text class="iconfont icon-shangpin"></text>
            </view>
          </view>
          <view class="goods-info">
            <view class="goods-name line1" :style="{ color: nameColor }">
              {{ goods.store_name || '' }}
            </view>
            <view class="goods-price" :style="{ color: priceColor }">
              <text class="symbol">¥</text>{{ goods.price || '0.00' }}
            </view>
          </view>
        </view>
      </view>
    </view>

    <!-- Empty state -->
    <view v-if="!posterList || !posterList.length" class="empty-state">
      <text class="iconfont icon-tupian"></text>
      <text class="empty-text">No poster configured</text>
    </view>
  </view>
</template>

<script>
import { getProductslist } from '@/api/api.js';

export default {
  name: 'posterGoods',
  props: {
    dataConfig: {
      type: Object,
      default: () => ({}),
    },
  },
  data() {
    return {
      posterList: [],
      posterRadius: 16,
      goodsRadius: 8,
      spacing: 20,
      nameColor: '#333333',
      priceColor: '#E93323',
      bgColor: '#f5f5f5',
      topPadding: 20,
      bottomPadding: 20,
      lrPadding: 20,
      topMargin: 0,
      bottomMargin: 0,
    };
  },
  computed: {
    boxStyle() {
      return {
        background: this.bgColor,
        paddingTop: this.topPadding + 'rpx',
        paddingBottom: this.bottomPadding + 'rpx',
        paddingLeft: this.lrPadding + 'rpx',
        paddingRight: this.lrPadding + 'rpx',
        marginTop: this.topMargin + 'rpx',
        marginBottom: this.bottomMargin + 'rpx',
      };
    },
  },
  watch: {
    dataConfig: {
      handler(newVal) {
        if (newVal) {
          this.initData();
        }
      },
      immediate: true,
      deep: true,
    },
  },
  methods: {
    initData() {
      const config = this.dataConfig;
      if (!config) return;

      // Get poster list
      this.posterList = config.posterList?.list || [];

      // Get style config
      this.posterRadius = (config.posterRadius?.val || 8) * 2;
      this.goodsRadius = (config.goodsRadius?.val || 8) * 2;
      this.spacing = (config.spacing?.val || 20) * 2;

      // Get color config
      const toneConfig = config.toneConfig?.tabVal || 0;
      if (toneConfig) {
        this.nameColor = config.nameColor?.color[0]?.item || '#333333';
        this.priceColor = config.priceColor?.color[0]?.item || '#E93323';
      } else {
        this.nameColor = '#333333';
        this.priceColor = 'var(--view-theme)';
      }

      this.bgColor = config.bgColor?.color[0]?.item || '#f5f5f5';

      // Get padding config
      this.topPadding = (config.topConfig?.val || 10) * 2;
      this.bottomPadding = (config.bottomConfig?.val || 10) * 2;
      this.lrPadding = (config.prConfig?.val || 10) * 2;
      this.topMargin = (config.mbConfig?.val || 0) * 2;
      this.bottomMargin = (config.mbBottomConfig?.val || 0) * 2;

      // Load goods data if needed
      this.loadGoodsData();
    },

    // Load goods data for each poster
    async loadGoodsData() {
      for (let i = 0; i < this.posterList.length; i++) {
        const poster = this.posterList[i];
        // If goods list is empty but we have IDs, fetch the data
        if ((!poster.goodsList || !poster.goodsList.length) && poster.goodsIds && poster.goodsIds.length) {
          try {
            const res = await getProductslist({
              ids: poster.goodsIds.join(','),
              limit: 3,
            });
            if (res.data) {
              this.$set(this.posterList[i], 'goodsList', res.data.slice(0, 3));
            }
          } catch (e) {
            console.error('Failed to load goods:', e);
          }
        }
      }
    },

    // Get goods list for a poster (max 3 items)
    getGoodsList(poster) {
      const list = poster.goodsList || [];
      return list.slice(0, 3);
    },

    // Navigate to poster link
    goLink(link) {
      if (!link) return;
      // Handle different link types
      if (link.startsWith('http')) {
        // External link
        // #ifdef H5
        window.location.href = link;
        // #endif
        // #ifndef H5
        uni.navigateTo({
          url: `/pages/annex/web_view/index?url=${encodeURIComponent(link)}`,
        });
        // #endif
      } else {
        // Internal link
        uni.navigateTo({
          url: link,
          fail: () => {
            uni.switchTab({
              url: link,
            });
          },
        });
      }
    },

    // Navigate to goods detail
    goDetail(goods) {
      if (!goods || !goods.id) return;
      uni.navigateTo({
        url: `/pages/goods_details/index?id=${goods.id}`,
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.poster-goods-container {
  min-height: 100rpx;
}

.poster-group {
  background: #fff;
  overflow: hidden;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
}

.poster-image {
  width: 100%;
  overflow: hidden;

  .poster-img {
    width: 100%;
    display: block;
  }

  .empty-poster {
    width: 100%;
    height: 300rpx;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;

    .iconfont {
      font-size: 80rpx;
      color: #ddd;
    }
  }
}

.goods-row {
  display: flex;
  padding: 20rpx;
  gap: 16rpx;
}

.goods-item {
  flex: 1;
  overflow: hidden;

  .goods-img-box {
    width: 100%;
    height: 200rpx;
    overflow: hidden;

    .goods-img {
      width: 100%;
      height: 100%;
    }

    .empty-goods {
      width: 100%;
      height: 100%;
      background: #f5f5f5;
      display: flex;
      align-items: center;
      justify-content: center;

      .iconfont {
        font-size: 50rpx;
        color: #ddd;
      }
    }
  }

  .goods-info {
    padding: 12rpx 0;

    .goods-name {
      font-size: 24rpx;
      color: #333;
      line-height: 1.4;
      height: 34rpx;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .goods-price {
      font-size: 28rpx;
      font-weight: bold;
      color: #e93323;
      margin-top: 8rpx;

      .symbol {
        font-size: 20rpx;
      }
    }
  }
}

.empty-state {
  height: 300rpx;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #fff;
  border-radius: 16rpx;

  .iconfont {
    font-size: 80rpx;
    color: #ddd;
    margin-bottom: 20rpx;
  }

  .empty-text {
    font-size: 26rpx;
    color: #999;
  }
}

.line1 {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>
