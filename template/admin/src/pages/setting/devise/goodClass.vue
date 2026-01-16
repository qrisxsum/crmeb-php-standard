<template>
  <div class="goodClass">
    <el-card class="h100" :bordered="false" shadow="never">
      <!-- 样式选择区域 -->
      <div class="list acea-row row-top">
        <div
          class="item"
          :class="activeStyle == index ? 'on' : ''"
          v-for="(item, index) in classList"
          :key="index"
          v-db-click
          @click="selectTap(index)"
        >
          <div class="pictrue"><img :src="item.image" /></div>
          <div class="name">{{ item.name }}</div>
        </div>
      </div>

      <!-- 导航配置区域（仅样式4显示） -->
      <div v-if="activeStyle === 3" class="navigation-config">
        <el-divider content-position="left">站内导航配置</el-divider>

        <el-form label-width="100px" size="small">
          <el-form-item label="启用导航">
            <el-switch v-model="navigationConfig.enabled" />
          </el-form-item>

          <template v-if="navigationConfig.enabled">
            <el-form-item label="导航标题">
              <el-input
                v-model="navigationConfig.title"
                placeholder="请输入导航模块标题"
                style="width: 300px"
              />
            </el-form-item>

            <el-form-item label="导航项">
              <div class="nav-items-container">
                <draggable
                  v-model="navigationConfig.items"
                  handle=".move-icon"
                  animation="200"
                >
                  <div
                    v-for="(item, index) in navigationConfig.items"
                    :key="index"
                    class="nav-item-row"
                  >
                    <div class="move-icon">
                      <i class="el-icon-rank"></i>
                    </div>
                    <el-input
                      v-model="item.title"
                      placeholder="导航标题"
                      style="width: 150px"
                    />
                    <el-input
                      v-model="item.url"
                      placeholder="跳转链接"
                      style="width: 280px"
                    >
                      <template slot="append">
                        <el-button
                          icon="el-icon-link"
                          @click="openLinkDialog(index)"
                        />
                      </template>
                    </el-input>
                    <el-switch
                      v-model="item.is_show"
                      active-value="1"
                      inactive-value="0"
                      active-text="显示"
                    />
                    <el-button
                      type="danger"
                      icon="el-icon-delete"
                      circle
                      size="mini"
                      @click="removeNavItem(index)"
                    />
                  </div>
                </draggable>

                <el-button
                  type="primary"
                  icon="el-icon-plus"
                  size="small"
                  @click="addNavItem"
                  style="margin-top: 10px"
                >
                  添加导航项
                </el-button>
              </div>
            </el-form-item>
          </template>
        </el-form>
      </div>
    </el-card>

    <!-- 链接选择组件 -->
    <linkaddress ref="linkaddress" @linkUrl="onLinkSelected" />
  </div>
</template>

<script>
import { getCategoryConfig, saveCategoryConfig } from '@/api/diy';
import linkaddress from '@/components/linkaddress/index.vue';
import draggable from 'vuedraggable';

export default {
  name: 'goodClass',
  components: {
    linkaddress,
    draggable,
  },
  props: {},
  data() {
    return {
      classList: [
        { image: require('@/assets/images/sort01.jpg'), name: '样式1' },
        { image: require('@/assets/images/sort02.jpg'), name: '样式2' },
        { image: require('@/assets/images/sort03.png'), name: '样式3' },
        { image: require('@/assets/images/sort04.jpg'), name: '样式4' },
      ],
      activeStyle: '-1',
      navigationConfig: {
        enabled: false,
        title: '相关配套',
        items: [],
      },
      currentEditIndex: -1, // 当前正在编辑链接的导航项索引
    };
  },
  created() {
    this.getInfo();
  },
  methods: {
    // 获取配置信息
    getInfo() {
      getCategoryConfig()
        .then((res) => {
          this.activeStyle = res.data.status ? res.data.status - 1 : 0;
          // 加载导航配置
          if (res.data.navigation) {
            this.navigationConfig = {
              enabled: res.data.navigation.enabled || false,
              title: res.data.navigation.title || '相关配套',
              items: res.data.navigation.items || [],
            };
          }
        })
        .catch(() => {
          this.activeStyle = 0;
        });
    },

    // 选择样式
    selectTap(index) {
      this.activeStyle = index;
    },

    // 添加导航项
    addNavItem() {
      this.navigationConfig.items.push({
        id: Date.now(),
        title: '',
        url: '',
        sort: this.navigationConfig.items.length + 1,
        is_show: '1',
      });
    },

    // 删除导航项
    removeNavItem(index) {
      this.navigationConfig.items.splice(index, 1);
    },

    // 打开链接选择弹窗
    openLinkDialog(index) {
      this.currentEditIndex = index;
      this.$refs.linkaddress.modals = true;
    },

    // 链接选择回调
    onLinkSelected(url) {
      if (this.currentEditIndex >= 0 && this.currentEditIndex < this.navigationConfig.items.length) {
        this.navigationConfig.items[this.currentEditIndex].url = url;
      }
      this.currentEditIndex = -1;
    },

    // 提交配置
    onSubmit(num) {
      this.$emit('parentFun', true);
      const status = num == 1 ? 1 : this.activeStyle + 1;

      // 构建提交数据
      const submitData = {
        status: status,
      };

      // 样式4时附加导航配置
      if (status === 4) {
        // 验证导航项
        if (this.navigationConfig.enabled && this.navigationConfig.items.length > 0) {
          for (const item of this.navigationConfig.items) {
            if (!item.title) {
              this.$message.error('请填写导航标题');
              this.$emit('parentFun', false);
              return;
            }
          }
        }
        submitData.navigation = this.navigationConfig;
      }

      saveCategoryConfig(submitData)
        .then((res) => {
          this.$emit('parentFun', false);
          this.$message.success(res.msg);
        })
        .catch((err) => {
          this.$message.error(err.msg || '保存失败');
          this.$emit('parentFun', false);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.goodClass {
  .title {
    font-size: 14px;
    color: rgba(0, 0, 0, 0.85);
    position: relative;
    padding-left: 11px;
    font-weight: bold;
    &:after {
      position: absolute;
      content: ' ';
      width: 2px;
      height: 14px;
      background-color: var(--prev-color-primary);
      left: 0;
      top: 3px;
    }
  }
  .list {
    .item {
      width: 264px;
      margin: 0px 30px 0 0;
      cursor: pointer;
      .pictrue {
        width: 100%;
        height: 496px;
        border: 1px solid #eeeeee;
        border-radius: 10px;
        img {
          width: 100%;
          height: 100%;
          border-radius: 10px;
        }
      }
      .name {
        font-size: 13px;
        color: rgba(0, 0, 0, 0.85);
        margin-top: 16px;
        text-align: center;
      }
      &.on {
        .pictrue {
          border: 2px solid var(--prev-color-primary);
        }
        .name {
          color: var(--prev-color-primary);
        }
      }
    }
  }

  .navigation-config {
    margin-top: 30px;
    padding: 20px;
    background: #fafafa;
    border-radius: 8px;

    .nav-items-container {
      .nav-item-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        padding: 10px;
        background: #fff;
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
  }
}
</style>
