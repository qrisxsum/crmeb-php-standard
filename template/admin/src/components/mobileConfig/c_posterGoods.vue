<template>
  <div class="mobile-config">
    <div v-for="(item, key) in rCom" :key="key">
      <component
        :is="item.components.name"
        :configObj="configObj"
        ref="childData"
        :configNme="item.configNme"
        :key="key"
        @getConfig="getConfig"
        :index="activeIndex"
        :number="num"
        :num="item.num"
      ></component>
    </div>

    <!-- 海报列表管理 -->
    <div class="poster-list-section" v-if="setUp === 0">
      <div class="section-title">
        <span>海报列表</span>
        <el-button type="primary" size="mini" @click="addPoster" :disabled="posterList.length >= 10">
          <i class="el-icon-plus"></i> 添加海报
        </el-button>
      </div>

      <div class="poster-items">
        <div
          class="poster-item"
          v-for="(poster, index) in posterList"
          :key="index"
        >
          <div class="poster-header">
            <span class="poster-index">海报 {{ index + 1 }}</span>
            <el-button type="text" size="mini" @click="removePoster(index)" class="delete-btn">
              <i class="el-icon-delete"></i>
            </el-button>
          </div>

          <!-- 海报图片上传 -->
          <div class="form-row">
            <span class="label">海报图片</span>
            <div class="upload-area">
              <div class="img-preview" v-if="poster.posterImg" @click="openUpload(index)">
                <img :src="poster.posterImg" />
                <div class="mask">
                  <i class="el-icon-edit"></i>
                </div>
              </div>
              <div class="add-btn" v-else @click="openUpload(index)">
                <i class="el-icon-plus"></i>
              </div>
            </div>
          </div>

          <!-- 跳转链接 -->
          <div class="form-row">
            <span class="label">跳转链接</span>
            <el-input
              v-model="poster.posterLink"
              placeholder="请输入或选择链接"
              size="small"
              @change="updateConfig"
            >
              <i slot="suffix" class="el-icon-link link-icon" @click="openLinkDialog(index)"></i>
            </el-input>
          </div>

          <!-- 选择商品（3个） -->
          <div class="form-row">
            <span class="label">选择商品（3个）</span>
            <div class="goods-select-area">
              <draggable class="goods-list" :list="poster.goodsList" group="goods">
                <div
                  class="goods-thumb"
                  v-for="(goods, gIndex) in poster.goodsList"
                  :key="gIndex"
                >
                  <img :src="goods.image" />
                  <span class="del-icon" @click.stop="removeGoods(index, gIndex)">
                    <i class="el-icon-close"></i>
                  </span>
                </div>
              </draggable>
              <div
                class="add-goods-btn"
                v-if="poster.goodsList.length < 3"
                @click="openGoodsModal(index)"
              >
                <i class="el-icon-plus"></i>
              </div>
            </div>
          </div>
        </div>

        <div class="empty-tip" v-if="!posterList.length">
          请点击"添加海报"按钮添加海报组
        </div>
      </div>
    </div>

    <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>

    <!-- 图片上传对话框 -->
    <el-dialog
      :visible.sync="uploadModal"
      title="选择图片"
      width="900px"
    >
      <uploadPictures
        :isChoice="'单选'"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="uploadModal"
      ></uploadPictures>
    </el-dialog>

    <!-- 商品选择对话框 -->
    <el-dialog
      :visible.sync="goodsModal"
      title="选择商品"
      class="paymentFooter"
      width="900px"
    >
      <goods-list
        ref="goodslist"
        :ischeckbox="true"
        :isdiy="true"
        isType
        @getProductId="getProductId"
        v-if="goodsModal"
      ></goods-list>
    </el-dialog>

    <!-- 链接选择弹窗 -->
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import vuedraggable from 'vuedraggable';
import toolCom from '@/components/mobileConfigRight/index.js';
import { mapState, mapMutations, mapActions } from 'vuex';
import rightBtn from '@/components/rightBtn/index.vue';
import goodsList from '@/components/goodsList';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';

export default {
  name: 'c_posterGoods',
  componentsName: 'posterGoods',
  cname: '海报商品组',
  props: {
    activeIndex: {
      type: null,
    },
    num: {
      type: null,
    },
    index: {
      type: null,
    },
  },
  components: {
    ...toolCom,
    rightBtn,
    goodsList,
    uploadPictures,
    draggable: vuedraggable,
    linkaddress,
  },
  data() {
    return {
      configObj: {},
      rCom: [
        {
          components: toolCom.c_set_up,
          configNme: 'setUp',
        },
      ],
      // 样式配置
      styleContent: [
        {
          components: toolCom.c_title,
          configNme: 'titleRight',
        },
        {
          components: toolCom.c_slider,
          configNme: 'posterRadius',
        },
        {
          components: toolCom.c_slider,
          configNme: 'goodsRadius',
        },
        {
          components: toolCom.c_slider,
          configNme: 'spacing',
        },
        {
          components: toolCom.c_radio,
          configNme: 'toneConfig',
        },
      ],
      styleContent1: [
        {
          components: toolCom.c_bg_color,
          configNme: 'nameColor',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'priceColor',
        },
      ],
      currencyStyle: [
        {
          components: toolCom.c_title,
          configNme: 'titleCurrency',
        },
        {
          components: toolCom.c_bg_color,
          configNme: 'bgColor',
        },
        {
          components: toolCom.c_slider,
          configNme: 'topConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'bottomConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'prConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'mbConfig',
        },
        {
          components: toolCom.c_slider,
          configNme: 'mbBottomConfig',
        },
      ],
      setUp: 0,
      toneConfig: 0,
      posterList: [],
      uploadModal: false,
      goodsModal: false,
      currentPosterIndex: 0,
      gridBtn: {
        xl: 4,
        lg: 8,
        md: 8,
        sm: 8,
        xs: 8,
      },
      gridPic: {
        xl: 6,
        lg: 8,
        md: 12,
        sm: 12,
        xs: 12,
      },
    };
  },
  watch: {
    num(nVal) {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[nVal]));
      this.configObj = value;
      this.posterList = value.posterList?.list || [];
    },
    configObj: {
      handler(nVal, oVal) {
        this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
      },
      deep: true,
    },
    'configObj.setUp.tabVal': {
      handler(nVal, oVal) {
        this.setUp = nVal;
        this.updateRCom();
      },
      deep: true,
    },
    'configObj.toneConfig.tabVal': {
      handler(nVal, oVal) {
        this.toneConfig = nVal;
        this.updateRCom();
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
      this.posterList = value.posterList?.list || [];
    });
  },
  methods: {
    updateRCom() {
      let arr = [this.rCom[0]];
      if (this.setUp === 0) {
        // 内容标签页 - 显示海报列表（在模板中处理）
        this.rCom = arr;
      } else {
        // 样式标签页
        if (this.toneConfig) {
          this.rCom = [...arr, ...this.styleContent, ...this.styleContent1, ...this.currencyStyle];
        } else {
          this.rCom = [...arr, ...this.styleContent, ...this.currencyStyle];
        }
      }
    },
    getConfig(data, name) {
      // 处理配置变更
    },
    // 添加新的海报组
    addPoster() {
      if (this.posterList.length >= 10) return;
      const newPoster = {
        id: 'poster_' + Date.now(),
        posterImg: '',
        posterLink: '',
        goodsIds: [],
        goodsList: [],
      };
      this.posterList.push(newPoster);
      this.updateConfig();
    },
    // 移除海报组
    removePoster(index) {
      this.posterList.splice(index, 1);
      this.updateConfig();
    },
    // 打开图片上传弹窗
    openUpload(index) {
      this.currentPosterIndex = index;
      this.uploadModal = true;
    },
    // 处理图片选择
    getPic(pc) {
      this.$nextTick(() => {
        this.posterList[this.currentPosterIndex].posterImg = pc.att_dir;
        this.uploadModal = false;
        this.updateConfig();
      });
    },
    // 打开商品选择弹窗
    openGoodsModal(index) {
      this.currentPosterIndex = index;
      this.goodsModal = true;
    },
    // 处理商品选择
    getProductId(data) {
      this.goodsModal = false;
      if (data && data.length) {
        const poster = this.posterList[this.currentPosterIndex];
        const existingIds = poster.goodsList.map(g => g.id);
        // Add new goods, max 3
        data.forEach(goods => {
          if (!existingIds.includes(goods.id) && poster.goodsList.length < 3) {
            poster.goodsList.push(goods);
          }
        });
        // Update goodsIds
        poster.goodsIds = poster.goodsList.map(g => g.id);
        this.updateConfig();
      }
    },
    // 从海报中移除商品
    removeGoods(posterIndex, goodsIndex) {
      this.posterList[posterIndex].goodsList.splice(goodsIndex, 1);
      this.posterList[posterIndex].goodsIds = this.posterList[posterIndex].goodsList.map(g => g.id);
      this.updateConfig();
    },
    // 更新配置到 store
    updateConfig() {
      this.configObj.posterList.list = this.posterList;
    },
    // 打开链接选择弹窗
    openLinkDialog(index) {
      this.currentPosterIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    // 接收选中的链接
    linkUrl(url) {
      this.posterList[this.currentPosterIndex].posterLink = url;
      this.updateConfig();
    },
  },
};
</script>

<style scoped lang="scss">
.poster-list-section {
  padding: 15px;

  .section-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;

    span {
      font-size: 14px;
      font-weight: bold;
      color: #333;
    }
  }
}

.poster-items {
  .poster-item {
    background: #f9f9f9;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;

    .poster-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;

      .poster-index {
        font-size: 13px;
        font-weight: bold;
        color: #409eff;
      }

      .delete-btn {
        color: #f56c6c;
      }
    }

    .form-row {
      display: flex;
      align-items: flex-start;
      margin-bottom: 12px;

      .label {
        width: 90px;
        font-size: 12px;
        color: #666;
        padding-top: 8px;
        flex-shrink: 0;
      }

      .el-input {
        flex: 1;

        .link-icon {
          cursor: pointer;
          color: #409eff;
          font-size: 16px;

          &:hover {
            color: #66b1ff;
          }
        }
      }
    }

    .upload-area {
      .img-preview {
        width: 120px;
        height: 80px;
        border-radius: 4px;
        overflow: hidden;
        position: relative;
        cursor: pointer;

        img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .mask {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.5);
          display: flex;
          align-items: center;
          justify-content: center;
          opacity: 0;
          transition: opacity 0.3s;

          i {
            color: #fff;
            font-size: 20px;
          }
        }

        &:hover .mask {
          opacity: 1;
        }
      }

      .add-btn {
        width: 120px;
        height: 80px;
        border: 1px dashed #d9d9d9;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: border-color 0.3s;

        i {
          font-size: 24px;
          color: #999;
        }

        &:hover {
          border-color: #409eff;

          i {
            color: #409eff;
          }
        }
      }
    }

    .goods-select-area {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;

      .goods-list {
        display: flex;
        gap: 8px;
      }

      .goods-thumb {
        width: 60px;
        height: 60px;
        border-radius: 4px;
        overflow: hidden;
        position: relative;

        img {
          width: 100%;
          height: 100%;
          object-fit: cover;
        }

        .del-icon {
          position: absolute;
          top: -6px;
          right: -6px;
          width: 18px;
          height: 18px;
          background: #f56c6c;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;

          i {
            font-size: 10px;
            color: #fff;
          }
        }
      }

      .add-goods-btn {
        width: 60px;
        height: 60px;
        border: 1px dashed #d9d9d9;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;

        i {
          font-size: 20px;
          color: #999;
        }

        &:hover {
          border-color: #409eff;

          i {
            color: #409eff;
          }
        }
      }
    }
  }

  .empty-tip {
    text-align: center;
    color: #999;
    font-size: 13px;
    padding: 30px 0;
  }
}
</style>
