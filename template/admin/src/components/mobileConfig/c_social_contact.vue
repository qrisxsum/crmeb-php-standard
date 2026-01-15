<template>
  <div class="mobile-config social-contact">
    <Form ref="formInline">
      <div v-for="(item, key) in rCom" :key="key">
        <component
          :is="item.components.name"
          :configObj="configObj"
          ref="childData"
          :configNme="item.configNme"
          :key="key"
          @getConfig="getConfig"
          :index="activeIndex"
          :num="item.num"
        ></component>
      </div>
      <!-- 社交链接列表配置 -->
      <div class="social-list-config" v-if="configObj.setUp && configObj.setUp.tabVal === 0">
        <div class="config-title">社交链接配置</div>
        <div class="social-item" v-for="(item, index) in configObj.socialList" :key="index">
          <div class="item-header">
            <span class="platform-name">{{ item.name }}</span>
            <el-switch v-model="item.enabled" active-text="启用" inactive-text="禁用"></el-switch>
          </div>
          <div class="item-content" v-if="item.enabled">
            <div class="config-row">
              <span class="label">图标：</span>
              <div class="icon-upload" @click="openUpload(index)">
                <img :src="item.icon" v-if="item.icon" />
                <div class="upload-placeholder" v-else>
                  <i class="el-icon-plus"></i>
                  <span>上传图标</span>
                </div>
              </div>
            </div>
            <div class="config-row">
              <span class="label">链接：</span>
              <el-input v-model="item.url" placeholder="请输入跳转链接URL" clearable>
                <i class="el-icon-link" slot="suffix" @click="openLinkDialog(index)"></i>
              </el-input>
            </div>
          </div>
        </div>
      </div>
      <rightBtn :activeIndex="activeIndex" :configObj="configObj"></rightBtn>
    </Form>
    <!-- 图片上传弹窗 -->
    <el-dialog :visible.sync="modalPic" width="960px" title="上传图标">
      <uploadPictures
        :isChoice="'单选'"
        @getPic="getPic"
        :gridBtn="gridBtn"
        :gridPic="gridPic"
        v-if="modalPic"
      ></uploadPictures>
    </el-dialog>
    <!-- 链接选择弹窗 -->
    <linkaddress ref="linkaddres" @linkUrl="linkUrl"></linkaddress>
  </div>
</template>

<script>
import toolCom from '@/components/mobileConfigRight/index.js';
import rightBtn from '@/components/rightBtn/index.vue';
import uploadPictures from '@/components/uploadPictures';
import linkaddress from '@/components/linkaddress';
import { mapMutations } from 'vuex';

export default {
  name: 'c_social_contact',
  componentsName: 'social_contact',
  cname: '联系我们',
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
    uploadPictures,
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
      modalPic: false,
      currentUploadIndex: 0,
      currentLinkIndex: 0,
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
    },
    configObj: {
      handler(nVal, oVal) {
        this.$store.commit('mobildConfig/UPDATEARR', { num: this.num, val: nVal });
      },
      deep: true,
    },
    'configObj.setUp.tabVal': {
      handler(nVal, oVal) {
        var arr = [this.rCom[0]];
        if (nVal === 0) {
          // 链接设置 - 使用自定义的社交链接配置
          let tempArr = [
            {
              components: toolCom.c_title,
              configNme: 'titleLeft',
            },
          ];
          this.rCom = arr.concat(tempArr);
        } else {
          // 样式设置
          let tempArr = [
            {
              components: toolCom.c_title,
              configNme: 'titleRight',
            },
            {
              components: toolCom.c_radio,
              configNme: 'locationConfig',
            },
            {
              components: toolCom.c_slider,
              configNme: 'topConfig',
            },
            {
              components: toolCom.c_slider,
              configNme: 'iconSizeConfig',
            },
            {
              components: toolCom.c_slider,
              configNme: 'iconSpacingConfig',
            },
          ];
          this.rCom = arr.concat(tempArr);
        }
      },
      deep: true,
    },
  },
  mounted() {
    this.$nextTick(() => {
      let value = JSON.parse(JSON.stringify(this.$store.state.mobildConfig.defaultArray[this.num]));
      this.configObj = value;
    });
  },
  methods: {
    getConfig(data) {},
    // 打开图片上传弹窗
    openUpload(index) {
      this.currentUploadIndex = index;
      this.modalPic = true;
    },
    // 获取上传的图片
    getPic(pc) {
      this.$nextTick(() => {
        this.configObj.socialList[this.currentUploadIndex].icon = pc.att_dir;
        this.modalPic = false;
      });
    },
    // 打开链接选择弹窗
    openLinkDialog(index) {
      this.currentLinkIndex = index;
      this.$refs.linkaddres.modals = true;
    },
    // 获取选择的链接
    linkUrl(url) {
      this.configObj.socialList[this.currentLinkIndex].url = url;
    },
  },
};
</script>

<style scoped lang="scss">
.social-contact {
  .social-list-config {
    margin: 0 15px 20px 15px;

    .config-title {
      padding-bottom: 15px;
      color: #333;
      font-size: 14px;
      font-weight: 500;
    }

    .social-item {
      background: #f9f9f9;
      border-radius: 6px;
      padding: 15px;
      margin-bottom: 12px;

      .item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
        margin-bottom: 15px;

        .platform-name {
          font-size: 14px;
          font-weight: 500;
          color: #333;
        }
      }

      .item-content {
        .config-row {
          display: flex;
          align-items: flex-start;
          margin-bottom: 12px;

          &:last-child {
            margin-bottom: 0;
          }

          .label {
            width: 50px;
            font-size: 12px;
            color: #666;
            padding-top: 8px;
            flex-shrink: 0;
          }

          .icon-upload {
            width: 64px;
            height: 64px;
            border: 1px dashed #ddd;
            border-radius: 6px;
            cursor: pointer;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            transition: border-color 0.3s;

            &:hover {
              border-color: #409eff;
            }

            img {
              width: 100%;
              height: 100%;
              object-fit: cover;
            }

            .upload-placeholder {
              display: flex;
              flex-direction: column;
              align-items: center;
              color: #999;

              i {
                font-size: 20px;
                margin-bottom: 4px;
              }

              span {
                font-size: 10px;
              }
            }
          }

          .el-input {
            flex: 1;
          }
        }
      }
    }
  }
}
</style>
