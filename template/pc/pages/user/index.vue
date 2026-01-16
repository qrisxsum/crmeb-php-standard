<template>
  <div class="user-index">
    <div class="user-com-title" style="padding-left: 16px">账户管理</div>
    <div class="user-content">
      <div class="item user-info">
        <!-- <div class="title">我的信息</div> -->
        <div class="info">
          <span class="label">我的头像：</span>
          <img class="user-header" :src="userInfo.avatar" alt=""/>
        </div>
        <div class="edit-txt">
          <el-upload
            class="upload-demo"
            :action="upLoadUrl"
            :on-success="handleSuccess"
            :show-file-list="false"
            :multiple="false"
            :limit="3"
            :on-exceed="handleExceed"
            :on-error="handleError"
            :headers="myHeaders"
            :file-list="fileList"
          >
            修改
          </el-upload>
        </div>
      </div>
      <div class="item text-info">
        <span class="label">我的昵称：</span>
        <span
          class="txt line1"
          style="display: inline-block; vertical-align: middle"
        >{{ userInfo.nickname }}</span
        >
        <div class="edit-txt" @click="editName">修改</div>
      </div>
      <div class="item text-info">
        <span class="label">手机号：</span>
        <span class="txt">{{ userInfo.phone }}</span>
        <div class="edit-txt" @click="isPhone = true">修改</div>
      </div>
      <div class="item text-info">
        <span class="label">邮箱：</span>
        <span class="txt">{{ userInfo.email || "-" }}</span>
        <div class="edit-txt" @click="isEmail = true">修改</div>
      </div>
      <div class="item text-info">
        <span class="label">真实姓名：</span>
        <span class="txt">{{ userInfo.real_name || "-" }}</span>
        <div class="edit-txt" @click="editRealName">修改</div>
      </div>
      <div class="item text-info">
        <span class="label">性别：</span>
        <span class="txt">{{ sexText }}</span>
        <div class="edit-txt" @click="openSexDialog">修改</div>
      </div>
      <div class="item text-info">
        <span class="label">我的ID：</span>
        <span class="txt">{{ userInfo.uid }}</span>
      </div>
      <div class="item text-info">
        <span class="label">密码设置：</span>
        <span class="txt">******</span>
        <div class="edit-txt" @click="isPassword = true">修改密码</div>
      </div>
      <!--        <div class="item user-info acea-row row-middle" style="padding-top: 20px">-->
      <!--          <span class="label">密码设置：</span>-->
      <!--          <span class="txt">******</span>-->
      <!--          <div class="edit-txt">-->
      <!--            <div @click="isPassword = true" style="cursor:pointer;">修改密码</div>-->
      <!--          </div>-->
      <!--        </div>-->
      <div class="out-btn">
        <span @click="longOut">退出登录</span>
      </div>
    </div>
    <!-- 修改密码 -->
    <el-dialog
      title="修改密码"
      :visible.sync="isPassword"
      width="545px"
      :before-close="handleClose"
    >
      <div class="form-box">
        <div class="input-item">
          <el-input
            placeholder="请输入手机号码"
            v-model="userInfo.phone"
            disabled
          >
            <template slot="prepend">+86</template>
          </el-input>
        </div>
        <div class="input-item">
          <el-input placeholder="请输入验证码" v-model="passwordData.code">
          </el-input>
          <el-button
            plain
            class="code-box"
            @click="getVerify(0)"
            :disabled="disabled"
          >{{ text }}
          </el-button
          >
        </div>
        <div class="input-item">
          <el-input
            placeholder="请输入新密码"
            type="password"
            v-model="passwordData.newPassword"
          >
          </el-input>
        </div>
      </div>
      <div class="dialog-footer">
        <span slot="footer">
          <el-button type="primary" @click="bindPassword">确 定</el-button>
          <el-button @click="handleClose">取 消</el-button>
        </span>
      </div>
    </el-dialog>
    <!-- 修改手机号码 -->
    <el-dialog
      title="修改手机号码"
      :visible.sync="isPhone"
      width="545px"
      :before-close="handleClose"
    >
      <div class="form-box">
        <div class="input-item">
          <el-input placeholder="请输入新手机号码" v-model="phoneData.newPhone">
            <template slot="prepend">+86</template>
          </el-input>
        </div>
        <div class="input-item">
          <el-input placeholder="请输入验证码" v-model="phoneData.code">
          </el-input>
          <el-button
            plain
            class="code-box"
            @click="getVerify(1)"
            :disabled="disabled"
          >{{ text }}
          </el-button
          >
        </div>
      </div>
      <div class="dialog-footer">
        <span slot="footer">
          <el-button type="primary" @click="bindNewPhone">确 定</el-button>
          <el-button @click="handleClose">取 消</el-button>
        </span>
      </div>
    </el-dialog>
    <!-- 修改邮箱 -->
    <el-dialog
      title="修改邮箱"
      :visible.sync="isEmail"
      width="545px"
      :before-close="handleClose"
    >
      <div class="form-box">
        <div class="input-item">
          <el-input placeholder="请输入新邮箱" v-model="emailData.email"></el-input>
        </div>
        <div class="input-item">
          <el-input placeholder="请输入验证码" v-model="emailData.code"></el-input>
          <el-button
            plain
            class="code-box"
            @click="getVerify(2)"
            :disabled="emailDisabled"
          >{{ emailText }}
          </el-button>
        </div>
        <div class="input-item">
          <el-input
            placeholder="请输入登录密码"
            type="password"
            v-model="emailData.password"
          ></el-input>
        </div>
      </div>
      <div class="dialog-footer">
        <span slot="footer">
          <el-button type="primary" @click="bindEmail">确 定</el-button>
          <el-button @click="handleClose">取 消</el-button>
        </span>
      </div>
    </el-dialog>
    <!-- 修改性别 -->
    <el-dialog
      title="修改性别"
      :visible.sync="isSex"
      width="545px"
      :before-close="handleClose"
    >
      <div class="form-box">
        <el-radio-group v-model="sexValue">
          <el-radio :label="0">保密</el-radio>
          <el-radio :label="1">男</el-radio>
          <el-radio :label="2">女</el-radio>
        </el-radio-group>
      </div>
      <div class="dialog-footer">
        <span slot="footer">
          <el-button type="primary" @click="bindSex">确 定</el-button>
          <el-button @click="handleClose">取 消</el-button>
        </span>
      </div>
    </el-dialog>
    <Verify
      v-if="verifyModal"
      @success="success"
      captchaType="blockPuzzle"
      :imgSize="{ width: '330px', height: '155px' }"
      ref="verify"
    ></Verify>
  </div>
</template>

<script>
import sendVerifyCode from "@/mixins/SendVerifyCode";
import {Message, MessageBox} from "element-ui";
import Verify from "@/components/verifition/Verify";

import setting from "~/setting";

export default {
  name: "index",
  auth: "guest",
  components: {Verify},
  mixins: [sendVerifyCode],
  data() {
    return {
      userInfo: {},
      fileList: [],
      upLoadUrl: setting.apiBaseURL + "/upload/image",
      myHeaders: {},
      isPassword: false, //修改密码号码弹窗
      verifyModal: false,
      passwordData: {
        phone: "",
        code: "",
        newPassword: "",
      },
      isPhone: false, //修改手机号码弹窗
      phoneData: {
        code: "",
        newPhone: "",
      },
      isEmail: false,
      emailData: {
        email: "",
        code: "",
        password: ""
      },
      emailDisabled: false,
      emailText: "获取验证码",
      emailRunTime: null,
      emailCodeEndAt: 0,
      isSex: false,
      sexValue: 0,
      keyCode: "",
      modalType: 0
    };
  },
  computed: {
    sexText() {
      const sex = Number(this.userInfo.sex);
      if (sex === 1) return "男";
      if (sex === 2) return "女";
      if (sex === 0) return "保密";
      return "-";
    }
  },
  fetch({store}) {
    store.commit("isHeader", true);
    store.commit("isFooter", true);
  },
  head() {
    return {
      title: "账户管理-" + this.$store.state.titleCon,
    };
  },
  created() {
  },
  mounted() {
    let local = this.$cookies.get("auth.strategy");
    this.myHeaders = {
      Authorization: this.$cookies.get(`auth._token.${local}`),
    };
    this.userInfo = this.$auth.user;
    this.getCodeKey();

    const endAt = Number(window.localStorage.getItem("pc_email_verify_code_end_at") || 0);
    if (endAt > Date.now()) {
      this.emailCodeEndAt = endAt;
      this.startEmailCountdown();
    } else if (endAt) {
      window.localStorage.removeItem("pc_email_verify_code_end_at");
    }
  },
  beforeDestroy() {
    if (this.emailRunTime) clearInterval(this.emailRunTime);
  },
  methods: {
    getVerify(type) {
      this.verifyModal = true;
      this.modalType = type
      this.$nextTick(e => {
        this.$refs.verify.show();
      });
    },
    success(params) {
      this.getCode(params);
    },
    // 获取验证码的key
    getCodeKey() {
      this.$axios.$get("/verify_code").then((res) => {
        this.keyCode = res.key;
      });
    },
    handleSuccess(data) {
      if (data.status == 400) {
        return this.$message.error(data.msg);
      }
      this.$axios
        .post("user/edit", {
          avatar: data.data.url,
          nickname: this.userInfo.nickname,
        })
        .then((res) => {
          let jsonData = JSON.parse(JSON.stringify(this.userInfo));
          jsonData.avatar = data.data.url;
          this.$auth.$storage.setUniversal("user", jsonData);
          this.userInfo = this.$auth.user;
          this.$message.success("修改成功");
        })
        .catch((error) => {
        });
    },
    handleExceed(files, fileList) {
      this.$message.warning("请勿频繁重复上传");
    },
    handleError(file, fileList) {
      this.$message.error("上传失败");
    },
    // 修改昵称
    editName() {
      MessageBox.prompt("请输入昵称", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        inputPattern: /\S/,
        inputErrorMessage: "昵称不能为空",
      })
        .then(({value}) => {
          this.$axios
            .post("user/edit", {
              nickname: value,
              avatar: this.userInfo.avatar,
            })
            .then((res) => {
              let jsonData = JSON.parse(JSON.stringify(this.userInfo));
              jsonData.nickname = value;
              this.$auth.$storage.setUniversal("user", jsonData);
              this.userInfo = this.$auth.user;
              this.$message.success("修改成功");
            });
        })
        .catch(() => {
        });
    },
    // 修改真实姓名
    editRealName() {
      MessageBox.prompt("请输入真实姓名", "提示", {
        confirmButtonText: "确定",
        cancelButtonText: "取消",
        inputPattern: /\S/,
        inputErrorMessage: "真实姓名不能为空",
      })
        .then(({value}) => {
          this.$axios
            .post("user/edit", {real_name: value})
            .then((res) => {
              let jsonData = JSON.parse(JSON.stringify(this.userInfo));
              jsonData.real_name = value;
              this.$auth.$storage.setUniversal("user", jsonData);
              this.userInfo = this.$auth.user;
              this.$message.success("修改成功");
            });
        })
        .catch(() => {
        });
    },
    openSexDialog() {
      const sex = Number(this.userInfo.sex);
      this.sexValue = [0, 1, 2].includes(sex) ? sex : 0;
      this.isSex = true;
    },
    bindSex() {
      this.$axios
        .post("user/edit", {sex: this.sexValue})
        .then((res) => {
          let jsonData = JSON.parse(JSON.stringify(this.userInfo));
          jsonData.sex = this.sexValue;
          this.$auth.$storage.setUniversal("user", jsonData);
          this.userInfo = this.$auth.user;
          this.$message.success("修改成功");
          this.isSex = false;
        });
    },
    // 退出登录
    async longOut() {
      let val = this.$cookies.get("auth.strategy");
      await this.$auth.logout(val).then((res) => {
        this.$store.commit("cartNum", 0);
        this.$router.replace({
          path: "/",
        });
      });
    },
    // 修改密码
    bindPassword() {
      let that = this;
      if (!that.passwordData.code) return Message.error("请填写验证码");
      if (!that.passwordData.newPassword) return Message.error("请填写新密码");
      this.$axios
        .post("register/reset", {
          account: that.userInfo.phone,
          captcha: that.passwordData.code,
          password: that.passwordData.newPassword,
        })
        .then((res) => {
          Message.success(res.msg);
          this.isPassword = false;
          this.passwordData.phone = "";
          this.passwordData.code = "";
          this.passwordData.newPassword = "";
        })
        .catch((err) => {
          return Message.error(err);
        });
    },
    handleClose() {
      this.isPassword = false;
      this.isPhone = false;
      this.isEmail = false;
      this.isSex = false;
      this.passwordData.phone = "";
      this.passwordData.code = "";
      this.passwordData.newPassword = "";
      this.phoneData.code = "";
      this.phoneData.newPhone = "";
      this.emailData.email = "";
      this.emailData.code = "";
      this.emailData.password = "";
    },
    // 发送验证码
    async getCode(data) {
      let that = this;
      if (that.modalType === 2) {
        if (!that.emailData.email) return Message.error("请填写新邮箱");
        await this.$axios
          .post("/email/verify", {
            email: that.emailData.email,
            type: "register",
            key: that.keyCode,
            captchaType: "blockPuzzle",
            captchaVerification: data.captchaVerification
          })
          .then((res) => {
            Message.success(res.msg);
            that.sendEmailCode();
          })
          .catch((res) => {
            Message.error(res);
          });
        return;
      }

      await this.$axios
        .post("/register/verify", {
          phone: that.modalType ? that.phoneData.newPhone : that.userInfo.phone,
          type: "mobile",
          key: that.keyCode,
          captchaType: "blockPuzzle",
          captchaVerification: data.captchaVerification
        })
        .then((res) => {
          Message.success(res.msg);
          that.sendCode();
        })
        .catch((res) => {
          Message.error(res);
        });
    },
    sendEmailCode() {
      if (this.emailDisabled) return;
      const seconds = 60;
      this.emailCodeEndAt = Date.now() + seconds * 1000;
      window.localStorage.setItem("pc_email_verify_code_end_at", String(this.emailCodeEndAt));
      this.startEmailCountdown();
    },
    startEmailCountdown() {
      if (this.emailRunTime) clearInterval(this.emailRunTime);
      this.emailDisabled = true;
      const tick = () => {
        const remain = Math.ceil((this.emailCodeEndAt - Date.now()) / 1000);
        if (remain <= 0) {
          if (this.emailRunTime) clearInterval(this.emailRunTime);
          this.emailRunTime = null;
          this.emailCodeEndAt = 0;
          window.localStorage.removeItem("pc_email_verify_code_end_at");
          this.emailDisabled = false;
          this.emailText = "重新获取";
          return;
        }
        this.emailText = "剩余 " + remain + "s";
      };
      tick();
      this.emailRunTime = setInterval(tick, 1000);
    },
    bindEmail() {
      if (!this.emailData.email) return Message.error("请填写新邮箱");
      if (!this.emailData.code) return Message.error("请填写验证码");
      if (!this.emailData.password) return Message.error("请填写登录密码");
      this.$axios
        .post("user/edit", {
          email: this.emailData.email,
          email_captcha: this.emailData.code,
          password: this.emailData.password
        })
        .then((res) => {
          let jsonData = JSON.parse(JSON.stringify(this.userInfo));
          jsonData.email = this.emailData.email;
          this.$auth.$storage.setUniversal("user", jsonData);
          this.userInfo = this.$auth.user;
          this.$message.success("修改成功");
          this.isEmail = false;
          this.emailData.email = "";
          this.emailData.code = "";
          this.emailData.password = "";
        })
        .catch((err) => Message.error(err));
    },
    // 绑定新手机号码
    async bindNewPhone() {
      let that = this;
      if (!that.phoneData.newPhone) return Message.error("请填写新手机号码");
      if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.phoneData.newPhone))
        return Message.error("请输入正确的手机号码");
      if (!that.phoneData.code) return Message.error("请填写验证码");
      this.$axios
        .post("user/updatePhone", {
          phone: that.phoneData.newPhone,
          captcha: that.phoneData.code,
          key: that.keyCode,
        })
        .then((res) => {
          Message.success(res.msg);
          this.userInfo.phone = that.phoneData.newPhone
          this.isPhone = false;
          this.phoneData.newPhone = "";
          this.phoneData.code = "";
        })
        .catch((err) => {
          return Message.error(err);
        });
    },
  },
};
</script>

<style lang="scss" scoped>
.user-index {
  .user-content {
    padding: 34px 0;

    .item {
      padding-left: 16px;
      position: relative;
      font-size: 14px;
      border-bottom: 1px dashed #dddddd;
      margin-right: 54px;

      .edit-txt {
        position: absolute;
        right: 0;
        bottom: 16px;
        color: #e93323;
        cursor: pointer;
      }

      .label {
        display: inline-block;
        width: 80px;
        color: #777777;
      }

      &.user-info {
        padding-bottom: 20px;

        .title {
          color: #282828;
          font-size: 18px;
        }

        .info {
          margin-top: 20px;
          color: #777777;
          font-size: 14px;

          img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            vertical-align: -44px;
            display: inline-block;
          }
        }
      }

      &.text-info {
        height: 70px;
        line-height: 70px;

        .edit-txt {
          top: 0;
        }

        .txt {
          color: #282828;
          width: 700px;
        }
      }
    }

    .out-btn {
      text-align: right;
      margin-right: 54px;

      span {
        display: inline-block;
        width: 130px;
        height: 40px;
        margin-top: 38px;
        line-height: 40px;
        text-align: center;
        background: #e93323;
        color: #fff;
        border-radius: 4px;
        cursor: pointer;
      }
    }
  }
}

.input-item {
  display: flex;
  align-items: center;
  margin-bottom: 20px;

  .code-box {
    width: 115px;
    height: 40px;
    text-align: center;
    cursor: pointer;
    margin-left: 30px;
  }
}

.dialog-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  padding-top: 40px;
  border-top: 1px solid #efefef;

  button {
    width: 190px;
    height: 45px;
  }
}

.user-header:hover {
  transform: rotate(666turn);
  transition: all 59s cubic-bezier(0.34, 0, 0.84, 1) 1s;
}
</style>
