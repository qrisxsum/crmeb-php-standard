<template>
  <div class="page-bottom-config">
    <el-card :bordered="false" shadow="never" class="ivu-mt">
      <div slot="header">
        <span>底部预留模块配置</span>
      </div>

      <el-form label-width="120px" size="small">
        <!-- 公告配置 -->
        <el-divider content-position="left">公告配置</el-divider>
        <el-form-item label="启用公告">
          <el-switch v-model="config.notice.enabled" />
        </el-form-item>

        <template v-if="config.notice.enabled">
          <el-form-item label="公告列表">
            <div class="notice-list">
              <div
                v-for="(item, index) in config.notice.list"
                :key="index"
                class="notice-item"
              >
                <el-form-item label="标题" label-width="60px">
                  <el-input v-model="item.title" placeholder="公告标题" style="width: 300px" />
                </el-form-item>
                <el-form-item label="内容" label-width="60px">
                  <el-input v-model="item.content" type="textarea" :rows="2" placeholder="公告内容" style="width: 300px" />
                </el-form-item>
                <el-form-item label="日期" label-width="60px">
                  <el-input v-model="item.date" placeholder="日期" style="width: 200px" />
                </el-form-item>
                <el-form-item label="链接" label-width="60px">
                  <el-input v-model="item.link" placeholder="跳转链接" style="width: 300px">
                    <template slot="append">
                      <el-button icon="el-icon-link" @click="openLinkDialog('notice', index)" />
                    </template>
                  </el-input>
                </el-form-item>
                <el-button type="danger" icon="el-icon-delete" circle size="mini" @click="removeNotice(index)" />
              </div>
              <el-button type="primary" icon="el-icon-plus" size="small" @click="addNotice">添加公告</el-button>
            </div>
          </el-form-item>
        </template>

        <!-- 导航链接配置 -->
        <el-divider content-position="left">导航链接配置</el-divider>
        <el-form-item label="启用导航链接">
          <el-switch v-model="config.navLinks.enabled" />
        </el-form-item>

        <template v-if="config.navLinks.enabled">
          <el-form-item label="链接列表">
            <div class="nav-list">
              <draggable v-model="config.navLinks.list" handle=".move-icon" animation="200">
                <div
                  v-for="(item, index) in config.navLinks.list"
                  :key="index"
                  class="nav-item"
                >
                  <div class="move-icon">
                    <i class="el-icon-rank"></i>
                  </div>
                  <el-input v-model="item.name" placeholder="名称" style="width: 150px" />
                  <el-select v-model="item.icon" placeholder="选择图标" style="width: 150px">
                    <el-option label="首页" value="home" />
                    <el-option label="分类" value="category" />
                    <el-option label="购物车" value="cart" />
                    <el-option label="我的" value="user" />
                    <el-option label="指南" value="guide" />
                    <el-option label="联系" value="contact" />
                    <el-option label="法律" value="law" />
                  </el-select>
                  <el-input v-model="item.link" placeholder="跳转链接" style="width: 280px">
                    <template slot="append">
                      <el-button icon="el-icon-link" @click="openLinkDialog('nav', index)" />
                    </template>
                  </el-input>
                  <el-button type="danger" icon="el-icon-delete" circle size="mini" @click="removeNavLink(index)" />
                </div>
              </draggable>
              <el-button type="primary" icon="el-icon-plus" size="small" @click="addNavLink">添加链接</el-button>
            </div>
          </el-form-item>
        </template>

        <!-- 版权配置 -->
        <el-divider content-position="left">版权配置</el-divider>
        <el-form-item label="启用版权信息">
          <el-switch v-model="config.copyright.enabled" />
        </el-form-item>

        <template v-if="config.copyright.enabled">
          <el-form-item label="版权文本">
            <el-input
              v-model="config.copyright.text"
              type="textarea"
              :rows="3"
              placeholder="请输入版权文本"
              style="width: 500px"
            />
          </el-form-item>
        </template>

        <!-- 返回顶部配置 -->
        <el-divider content-position="left">返回顶部配置</el-divider>
        <el-form-item label="启用返回顶部">
          <el-switch v-model="config.backTop.enabled" />
        </el-form-item>
      </el-form>
    </el-card>

    <!-- 底部保存按钮 -->
    <el-card :bordered="false" shadow="never" class="fixed-card" :style="{ left: fixBottomWidth }">
      <div class="acea-row row-center">
        <el-button type="primary" v-db-click @click="save" :loading="loading">保存配置</el-button>
      </div>
    </el-card>

    <!-- 链接选择组件 -->
    <linkaddress ref="linkaddress" @linkUrl="onLinkSelected" />
  </div>
</template>

<script>
import { getPageBottomConfig, savePageBottomConfig } from '@/api/diy';
import linkaddress from '@/components/linkaddress/index.vue';
import draggable from 'vuedraggable';

export default {
  name: 'pageBottomConfig',
  components: {
    linkaddress,
    draggable,
  },
  data() {
    return {
      loading: false,
      config: {
        notice: {
          enabled: false,
          list: [],
        },
        navLinks: {
          enabled: true,
          list: [],
        },
        copyright: {
          enabled: true,
          text: '',
        },
        backTop: {
          enabled: true,
        },
      },
      currentLinkType: '',
      currentLinkIndex: -1,
    };
  },
  computed: {
    fixBottomWidth() {
      let { layout, isCollapse } = this.$store.state.themeConfig.themeConfig;
      let w;
      if (['columns'].includes(layout)) {
        w = isCollapse ? '85px' : '265px';
      } else if (['classic'].includes(layout)) {
        w = isCollapse ? '85px' : '180px';
      } else if (['defaults'].includes(layout)) {
        w = isCollapse ? '64px' : '180px';
      } else {
        w = '0px';
      }
      return w;
    },
  },
  created() {
    this.getConfig();
  },
  methods: {
    getConfig() {
      getPageBottomConfig()
        .then((res) => {
          if (res.data) {
            this.config = res.data;
          }
        })
        .catch((err) => {
          this.$message.error(err.msg || '获取配置失败');
        });
    },

    addNotice() {
      this.config.notice.list.push({
        title: '',
        content: '',
        date: '',
        link: '',
      });
    },

    removeNotice(index) {
      this.config.notice.list.splice(index, 1);
    },

    addNavLink() {
      this.config.navLinks.list.push({
        name: '',
        icon: '',
        link: '',
      });
    },

    removeNavLink(index) {
      this.config.navLinks.list.splice(index, 1);
    },

    openLinkDialog(type, index) {
      this.currentLinkType = type;
      this.currentLinkIndex = index;
      this.$refs.linkaddress.modals = true;
    },

    onLinkSelected(url) {
      if (this.currentLinkType === 'notice' && this.currentLinkIndex >= 0) {
        this.config.notice.list[this.currentLinkIndex].link = url;
      } else if (this.currentLinkType === 'nav' && this.currentLinkIndex >= 0) {
        this.config.navLinks.list[this.currentLinkIndex].link = url;
      }
      this.currentLinkType = '';
      this.currentLinkIndex = -1;
    },

    save() {
      this.loading = true;
      savePageBottomConfig(this.config)
        .then((res) => {
          this.$message.success(res.msg || '保存成功');
        })
        .catch((err) => {
          this.$message.error(err.msg || '保存失败');
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.page-bottom-config {
  padding-bottom: 80px;

  .notice-list {
    .notice-item {
      padding: 15px;
      margin-bottom: 15px;
      background: #f9f9f9;
      border-radius: 8px;
      border: 1px solid #eee;
      position: relative;

      > .el-button {
        position: absolute;
        right: 10px;
        top: 10px;
      }
    }
  }

  .nav-list {
    .nav-item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px;
      margin-bottom: 10px;
      background: #f9f9f9;
      border-radius: 4px;
      border: 1px solid #eee;

      .move-icon {
        cursor: move;
        color: #999;
        font-size: 16px;
        &:hover {
          color: var(--prev-color-primary);
        }
      }
    }
  }

  .fixed-card {
    position: fixed;
    bottom: 0;
    right: 0;
    width: calc(100% - var(--left-width, 180px));
    z-index: 100;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
  }
}
</style>
