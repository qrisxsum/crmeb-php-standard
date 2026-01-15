<template>
  <div class="social-contact-box" :class="positions ? '' : 'on'" :style="{ marginTop: mTop + '%' }">
    <div class="social-list">
      <template v-for="(item, index) in enabledLinks">
        <div class="social-item" :key="index" :style="itemStyle">
          <img :src="item.icon" v-if="item.icon" />
          <div class="empty-box" v-else>
            <img src="../../assets/images/shan.png" />
          </div>
        </div>
      </template>
      <div class="empty-tip" v-if="enabledLinks.length === 0">
        <span>请配置社交链接</span>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex';

export default {
  name: 'social_contact',
  cname: '联系我们',
  configName: 'c_social_contact',
  icon: '#iconzujian-gongzhonghao',
  type: 2, // 0 基础组件 1 营销组件 2工具组件
  defaultName: 'socialContact', // 外面匹配名称
  props: {
    index: {
      type: null,
      default: -1,
    },
    num: {
      type: null,
    },
  },
  computed: {
    ...mapState('mobildConfig', ['defaultArray']),
    enabledLinks() {
      if (!this.socialList) return [];
      return this.socialList.filter(item => item.enabled);
    },
    itemStyle() {
      return {
        width: this.iconSize + 'px',
        height: this.iconSize + 'px',
        marginBottom: this.iconSpacing + 'px',
      };
    },
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
        const data = this.$store.state.mobildConfig.defaultArray[nVal];
        this.setConfig(data);
      },
      deep: true,
    },
    defaultArray: {
      handler(nVal, oVal) {
        const data = this.$store.state.mobildConfig.defaultArray[this.num];
        this.setConfig(data);
      },
      deep: true,
    },
  },
  data() {
    return {
      defaultConfig: {
        cname: '联系我们',
        name: 'socialContact',
        timestamp: this.num,
        isHide: false,
        setUp: {
          tabVal: 0,
        },
        titleLeft: '链接设置',
        titleRight: '样式设置',
        // 社交链接列表
        socialList: [
          { platform: 'twitter', name: 'Twitter', icon: '/static/images/social/twitter.png', url: 'https://twitter.com', enabled: true },
          { platform: 'instagram', name: 'Instagram', icon: '/static/images/social/instagram.png', url: 'https://instagram.com', enabled: true },
          { platform: 'facebook', name: 'Facebook', icon: '/static/images/social/facebook.png', url: 'https://facebook.com', enabled: true },
          { platform: 'line', name: 'LINE', icon: '/static/images/social/line.png', url: 'https://line.me', enabled: true },
        ],
        // 位置配置
        locationConfig: {
          title: '展示位置',
          tabVal: 1, // 0左 1右
          tabList: [
            { name: '左' },
            { name: '右' },
          ],
        },
        // 上下偏移
        topConfig: {
          title: '上下偏移',
          val: 30,
          min: 10,
          max: 80,
        },
        // 图标大小
        iconSizeConfig: {
          title: '图标大小',
          val: 40,
          min: 30,
          max: 60,
        },
        // 图标间距
        iconSpacingConfig: {
          title: '图标间距',
          val: 8,
          min: 4,
          max: 20,
        },
      },
      pageData: {},
      mTop: 30,
      positions: 1, // 展示位置 0左 1右
      socialList: [],
      iconSize: 40,
      iconSpacing: 8,
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
      if (data.topConfig) {
        this.mTop = data.topConfig.val;
        this.positions = data.locationConfig.tabVal;
        this.socialList = data.socialList || [];
        this.iconSize = data.iconSizeConfig ? data.iconSizeConfig.val : 40;
        this.iconSpacing = data.iconSpacingConfig ? data.iconSpacingConfig.val : 8;
      }
    },
  },
};
</script>

<style scoped lang="scss">
.social-contact-box {
  width: 100%;
  display: flex;
  justify-content: flex-end;
  padding-right: 10px;
  &.on {
    justify-content: flex-start;
    padding-left: 10px;
  }
  .social-list {
    display: flex;
    flex-direction: column;
    align-items: center;

    .social-item {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;

      &:last-child {
        margin-bottom: 0 !important;
      }

      img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
      }

      .empty-box {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: #f3f9ff;
        display: flex;
        align-items: center;
        justify-content: center;

        img {
          width: 60%;
          height: 60%;
        }
      }
    }

    .empty-tip {
      padding: 10px;
      color: #999;
      font-size: 12px;
    }
  }
}
</style>
