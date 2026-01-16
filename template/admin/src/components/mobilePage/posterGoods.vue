<template>
  <div>
    <div
      class="mobile-page poster-goods-wrapper"
      :style="{
        background: bgColor,
        marginTop: mbConfig + 'px',
        marginBottom: mbBottomConfig + 'px',
        paddingTop: topConfig + 'px',
        paddingBottom: bottomConfig + 'px',
        paddingLeft: prConfig + 'px',
        paddingRight: prConfig + 'px',
      }"
    >
      <!-- Multiple poster groups -->
      <div
        class="poster-group"
        v-for="(poster, pIndex) in posterList"
        :key="pIndex"
        :style="{ marginBottom: pIndex === posterList.length - 1 ? 0 : spacing + 'px' }"
      >
        <!-- Poster image -->
        <div class="poster-image" :style="{ borderRadius: posterRadius + 'px' }">
          <img
            v-if="poster.posterImg"
            :src="poster.posterImg"
            class="poster-img"
            :style="{ borderRadius: posterRadius + 'px' }"
          />
          <div v-else class="empty-poster" :style="{ borderRadius: posterRadius + 'px' }">
            <i class="el-icon-picture"></i>
            <span>请选择海报图片</span>
          </div>
        </div>

        <!-- Associated goods (3 items) -->
        <div class="goods-row">
          <div
            class="goods-item"
            v-for="(goods, gIndex) in getGoodsList(poster, 3)"
            :key="gIndex"
            :style="{ borderRadius: goodsRadius + 'px' }"
          >
            <div class="goods-img-box">
              <img
                v-if="goods.image"
                :src="goods.image"
                class="goods-img"
                :style="{ borderRadius: goodsRadius + 'px ' + goodsRadius + 'px 0 0' }"
              />
              <div
                v-else
                class="empty-goods"
                :style="{ borderRadius: goodsRadius + 'px ' + goodsRadius + 'px 0 0' }"
              >
                <i class="el-icon-goods"></i>
              </div>
            </div>
            <div class="goods-info">
              <div class="goods-name" :style="{ color: nameColor }">
                {{ goods.store_name || '商品名称' }}
              </div>
              <div class="goods-price" :style="{ color: priceColor }">
                <span class="symbol">¥</span>{{ goods.price || '0.00' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="!posterList.length" class="empty-state">
        <i class="el-icon-picture-outline"></i>
        <span>请添加海报并选择商品</span>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'posterGoods',
  cname: '海报商品组',
  configName: 'c_posterGoods',
  icon: '#iconzujian-tupianmofang',
  type: 1, // 1 = Marketing component
  defaultName: 'posterGoods',
  props: {
    index: {
      type: null,
    },
    num: {
      type: null,
    },
    colorStyle: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
  },
  watch: {
    pageData: {
      handler(nVal, oVal) {
        this.setConfig(nVal);
      },
      deep: true,
    },
    num: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        let data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  data() {
    return {
      // Default config - DO NOT MODIFY
      defaultConfig: {
        cname: '海报商品组',
        name: 'posterGoods',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '内容设置',
        titleRight: '样式设置',
        titleCurrency: '通用样式',
        // 海报列表
        posterList: {
          title: '海报列表',
          max: 10,
          list: [],
        },
        // 样式配置
        posterRadius: {
          title: '海报圆角',
          val: 8,
          min: 0,
          max: 50,
        },
        goodsRadius: {
          title: '商品圆角',
          val: 8,
          min: 0,
          max: 50,
        },
        spacing: {
          title: '组间距',
          val: 20,
          min: 0,
          max: 100,
        },
        // 颜色配置
        toneConfig: {
          title: '色调',
          tabVal: 0,
          tabList: [
            { name: '跟随主题' },
            { name: '自定义' },
          ],
        },
        nameColor: {
          title: '商品名称',
          name: 'nameColor',
          default: [{ item: '#333333' }],
          color: [{ item: '#333333' }],
        },
        priceColor: {
          title: '商品价格',
          name: 'priceColor',
          default: [{ item: '#E93323' }],
          color: [{ item: '#E93323' }],
        },
        bgColor: {
          title: '背景颜色',
          name: 'bgColor',
          default: [{ item: '#f5f5f5' }],
          color: [{ item: '#f5f5f5' }],
        },
        // 通用样式
        topConfig: {
          title: '上内边距',
          val: 10,
          min: 0,
          max: 100,
        },
        bottomConfig: {
          title: '下内边距',
          val: 10,
          min: 0,
          max: 100,
        },
        prConfig: {
          title: '左右边距',
          val: 10,
          min: 0,
          max: 100,
        },
        mbConfig: {
          title: '上外边距',
          val: 0,
          min: 0,
          max: 100,
        },
        mbBottomConfig: {
          title: '下外边距',
          val: 0,
          min: 0,
          max: 100,
        },
      },
      pageData: {},
      posterList: [],
      posterRadius: 8,
      goodsRadius: 8,
      spacing: 20,
      toneConfig: 0,
      nameColor: '#333333',
      priceColor: '#E93323',
      bgColor: '#f5f5f5',
      topConfig: 10,
      bottomConfig: 10,
      prConfig: 10,
      mbConfig: 0,
      mbBottomConfig: 0,
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.pageData = this.$store.state.mobildConfig.defaultArray[this.num];
      this.setConfig(this.pageData);
    });
  },
  methods: {
    setConfig(data) {
      if (!data) return;
      if (data.posterList) {
        this.posterList = data.posterList.list || [];
        this.posterRadius = data.posterRadius?.val || 8;
        this.goodsRadius = data.goodsRadius?.val || 8;
        this.spacing = data.spacing?.val || 20;
        this.toneConfig = data.toneConfig?.tabVal || 0;
        this.nameColor = this.toneConfig ? data.nameColor?.color[0]?.item : '#333333';
        this.priceColor = this.toneConfig
          ? data.priceColor?.color[0]?.item
          : (this.colorStyle?.theme || '#E93323');
        this.bgColor = data.bgColor?.color[0]?.item || '#f5f5f5';
        this.topConfig = data.topConfig?.val || 10;
        this.bottomConfig = data.bottomConfig?.val || 10;
        this.prConfig = data.prConfig?.val || 10;
        this.mbConfig = data.mbConfig?.val || 0;
        this.mbBottomConfig = data.mbBottomConfig?.val || 0;
      }
    },
    getGoodsList(poster, count) {
      const list = poster.goodsList || [];
      // Fill with empty objects if not enough
      const result = [...list];
      while (result.length < count) {
        result.push({});
      }
      return result.slice(0, count);
    },
  },
};
</script>

<style scoped lang="scss">
.poster-goods-wrapper {
  min-height: 100px;
}

.poster-group {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 20px;

  &:last-child {
    margin-bottom: 0;
  }
}

.poster-image {
  width: 100%;
  overflow: hidden;

  .poster-img {
    width: 100%;
    display: block;
    object-fit: cover;
  }

  .empty-poster {
    width: 100%;
    height: 150px;
    background: #f3f9ff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;

    i {
      font-size: 40px;
      margin-bottom: 10px;
    }

    span {
      font-size: 12px;
    }
  }
}

.goods-row {
  display: flex;
  padding: 10px;
  gap: 8px;
}

.goods-item {
  flex: 1;
  background: #fff;
  overflow: hidden;

  .goods-img-box {
    width: 100%;
    height: 100px;

    .goods-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .empty-goods {
      width: 100%;
      height: 100%;
      background: #f3f9ff;
      display: flex;
      align-items: center;
      justify-content: center;

      i {
        font-size: 30px;
        color: #ccc;
      }
    }
  }

  .goods-info {
    padding: 8px;

    .goods-name {
      font-size: 12px;
      color: #333;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      margin-bottom: 4px;
    }

    .goods-price {
      font-size: 14px;
      font-weight: bold;
      color: #e93323;

      .symbol {
        font-size: 10px;
      }
    }
  }
}

.empty-state {
  height: 200px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #999;
  background: #fff;
  border-radius: 8px;

  i {
    font-size: 50px;
    margin-bottom: 15px;
  }

  span {
    font-size: 14px;
  }
}
</style>
