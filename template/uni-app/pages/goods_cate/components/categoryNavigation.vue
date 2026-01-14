<template>
  <view class="category-navigation" v-if="config && config.items && config.items.length">
    <!-- 标题栏 -->
    <view class="nav-header" v-if="config.title">
      <text class="nav-title">{{ config.title }}</text>
    </view>

    <!-- 导航项列表 -->
    <view class="nav-list">
      <view
        v-for="(item, index) in visibleItems"
        :key="index"
        class="nav-item"
        @click="handleNavClick(item)"
        hover-class="nav-item-hover"
      >
        <view class="nav-item-content">
          <text class="nav-text">{{ item.title }}</text>
        </view>
        <text class="nav-arrow iconfont icon-jiantou"></text>
      </view>
    </view>
  </view>
</template>

<script>
export default {
  name: 'categoryNavigation',
  props: {
    config: {
      type: Object,
      default: () => ({
        enabled: false,
        title: '',
        items: []
      })
    }
  },
  computed: {
    // 过滤出显示的导航项
    visibleItems() {
      if (!this.config || !this.config.items) return [];
      return this.config.items
        .filter(item => item.is_show === '1' || item.is_show === 1 || item.is_show === true)
        .sort((a, b) => (a.sort || 0) - (b.sort || 0));
    }
  },
  methods: {
    handleNavClick(item) {
      if (!item.url) return;

      // 使用 uni 的跳转方法
      const url = item.url;

      // 判断是否是 tabBar 页面
      const tabBarPages = [
        '/pages/index/index',
        '/pages/goods_cate/goods_cate',
        '/pages/order_addcart/order_addcart',
        '/pages/user/index'
      ];

      const isTabBar = tabBarPages.some(page => url.indexOf(page) === 0);

      if (isTabBar) {
        uni.switchTab({
          url: url
        });
      } else if (url.indexOf('http') === 0) {
        // 外部链接
        // #ifdef H5
        window.location.href = url;
        // #endif
        // #ifndef H5
        uni.navigateTo({
          url: `/pages/annex/web_view/index?url=${encodeURIComponent(url)}`
        });
        // #endif
      } else {
        // 普通页面跳转
        uni.navigateTo({
          url: url,
          fail: () => {
            // 如果navigateTo失败，尝试redirectTo
            uni.redirectTo({
              url: url
            });
          }
        });
      }
    }
  }
};
</script>

<style lang="scss" scoped>
.category-navigation {
  background-color: #f5f5f5;
  margin-top: 20rpx;
  padding: 20rpx 0;

  .nav-header {
    padding: 20rpx 32rpx;

    .nav-title {
      font-size: 28rpx;
      font-weight: 600;
      color: #333;
    }
  }

  .nav-list {
    .nav-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 30rpx 32rpx;
      background-color: #fff;
      border-bottom: 1rpx solid #eee;

      &:last-child {
        border-bottom: none;
      }

      .nav-item-content {
        flex: 1;

        .nav-text {
          font-size: 28rpx;
          color: #333;
        }
      }

      .nav-arrow {
        color: #999;
        font-size: 24rpx;
      }
    }

    .nav-item-hover {
      background-color: #f8f8f8;
    }
  }
}
</style>
