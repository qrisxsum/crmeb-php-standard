<template>
  <div class="login">
    <div class="wrapper_1200">
      <div class="header acea-row row-between-wrapper" v-show="isShow">
        <div class="acea-row row-middle">
          <div class="icon" @click="goHome">
            <img :src="info.logoUrl" />
          </div>
          <div class="name" @click="goHome">官方商城</div>
        </div>
        <div class="acea-row row-middle">
          <div class="item">
            <span class="iconfont icon-pinzhongqiquan font-color"></span
            >品种齐全
          </div>
          <div class="item">
            <span class="iconfont icon-dijiachangxuan font-color"></span
            >低价畅选
          </div>
          <div class="item">
            <span class="iconfont icon-zhengpinhanghuo font-color"></span
            >正品行货
          </div>
        </div>
      </div>
    </div>
    <div class="loginBg min_wrapper_1200">
      <div class="wrapper" v-show="current === 3">
        <div class="title">
          邮箱验证码登录
          <span
            @click="ewmLogin"
            v-if="appidNum"
            class="iconfont icon-weixindenglu1"
          ></span>
        </div>
        <div class="item phone acea-row row-middle">
          <input type="text" placeholder="请输入邮箱" v-model="email" />
        </div>
        <div class="item verificat acea-row row-between-wrapper">
          <input type="text" placeholder="请输入验证码" v-model="captcha" />
          <button
            class="code font-color"
            :disabled="disabled"
            :class="disabled === true ? 'on' : ''"
            @click="getVerify"
          >
            {{ text }}
          </button>
        </div>
        <div class="isAgree">
          <el-checkbox v-model="agreement"></el-checkbox
          ><span class="agree"
            >我已阅读并同意<span class="agreement" @click="agreementTap(4)"
              >《用户协议》</span
            >和<span class="agreement" @click="agreementTap(3)"
              >《隐私协议》</span
            ></span
          >
        </div>
        <div class="signIn bg-color" @click="loginEmail">登录</div>
        <div class="login-switch">
          <span class="switch-item" @click="current = 1">手机登录</span>
          <span class="switch-divider">|</span>
          <span class="switch-item" @click="current = 2">账号登录</span>
        </div>
        <div class="register-link">
          <span class="register-text">还没有账号？</span>
          <span class="register-btn font-color" @click="current = 4">立即注册</span>
        </div>
      </div>
		      <div class="wrapper wrapper-register" v-show="current === 4">
		        <div class="title">
		          注册
	          <span
            @click="ewmLogin"
            v-if="appidNum"
            class="iconfont icon-weixindenglu1"
          ></span>
        </div>
		        <div class="item phone acea-row row-middle" v-if="registerVerifyType === 'phone'">
		          <div class="number">+86</div>
		          <input type="text" placeholder="请输入手机号" v-model="account" />
		        </div>
		        <div class="item phone acea-row row-middle" v-else>
		          <input type="text" placeholder="请输入邮箱" v-model="email" />
		        </div>
		        <div class="verify-switch">
		          <span
		            class="switch-item"
	            :class="{ on: registerVerifyType === 'phone' }"
	            @click="registerVerifyType = 'phone'"
	          >
	            手机验证码
	          </span>
	          <span class="switch-divider">|</span>
	          <span
	            class="switch-item"
	            :class="{ on: registerVerifyType === 'email' }"
	            @click="registerVerifyType = 'email'"
	          >
	            邮箱验证码
	          </span>
		        </div>
		        <div class="item verificat acea-row row-between-wrapper">
		          <input type="text" placeholder="请输入验证码" v-model="captcha" />
		          <button
	            class="code font-color"
	            :disabled="disabled"
	            :class="disabled === true ? 'on' : ''"
            @click="getVerify"
          >
            {{ text }}
          </button>
		        </div>
	        <div class="item phone acea-row row-middle" v-if="registerVerifyType === 'phone'">
	          <input type="text" placeholder="请输入邮箱" v-model="email" />
	        </div>
	        <div class="item phone acea-row row-middle" v-else>
	          <div class="number">+86</div>
	          <input type="text" placeholder="请输入手机号" v-model="account" />
	        </div>
	        <div class="item phone acea-row row-middle">
	          <input type="text" placeholder="请输入昵称" v-model="nickname" />
	        </div>
        <div class="item phone acea-row row-middle">
          <input type="text" placeholder="请输入真实姓名" v-model="real_name" />
        </div>
	        <div class="item phone acea-row row-middle">
	          <select v-model="sex" class="sexSelect" :class="{ 'is-placeholder': sex === '' }">
	            <option disabled value="">请选择性别</option>
	            <option :value="0">保密</option>
	            <option :value="1">男</option>
	            <option :value="2">女</option>
	          </select>
	        </div>
        <div class="item pwd">
          <input type="password" placeholder="请输入密码" v-model="password" />
        </div>
        <div class="isAgree">
          <el-checkbox v-model="agreement"></el-checkbox
          ><span class="agree"
            >我已阅读并同意<span class="agreement" @click="agreementTap(4)"
              >《用户协议》</span
            >和<span class="agreement" @click="agreementTap(3)"
              >《隐私协议》</span
            ></span
          >
        </div>
        <div class="signIn bg-color" @click="doRegister">注册</div>
        <div class="register-link">
          <span class="register-text">已有账号？</span>
          <span class="register-btn font-color" @click="current = 3">返回登录</span>
        </div>
      </div>
      <div class="wrapper" v-show="current === 1">
        <div class="title">
          手机验证码登录
          <!--@click="current = 3"-->
          <span
            @click="ewmLogin"
            v-if="appidNum"
            class="iconfont icon-weixindenglu1"
          ></span>
          <!-- <a :href="`https://open.weixin.qq.com/connect/qrconnect?appid=${appidNum}&redirect_uri=${hosts}&response_type=code&scope=snsapi_login&state=EqMkUDWh8F3euWlt23jHJ8ZJuaTAVPZyiKEoq5U0`" v-if="appidNum" class="iconfont icon-weixindenglu1"></a> -->
        </div>
        <div class="item phone acea-row row-middle">
          <div class="number">+86</div>
          <input type="text" placeholder="请输入手机号" v-model="account" />
        </div>
        <div class="item verificat acea-row row-between-wrapper">
          <input type="text" placeholder="请输入验证码" v-model="captcha" />
          <button
            class="code font-color"
            :disabled="disabled"
            :class="disabled === true ? 'on' : ''"
            @click="getVerify"
          >
            {{ text }}
          </button>
        </div>
        <div class="isAgree">
          <el-checkbox v-model="agreement"></el-checkbox
          ><span class="agree"
            >我已阅读并同意<span class="agreement" @click="agreementTap(4)"
              >《用户协议》</span
            >和<span class="agreement" @click="agreementTap(3)"
              >《隐私协议》</span
            ></span
          >
        </div>
        <div class="signIn bg-color" @click="loginMobile">登录</div>
        <div class="login-switch">
          <span class="switch-item" @click="current = 3">邮箱登录</span>
          <span class="switch-divider">|</span>
          <span class="switch-item" @click="current = 2">账号登录</span>
        </div>
        <div class="register-link">
          <span class="register-text">还没有账号？</span>
          <span class="register-btn font-color" @click="current = 4">立即注册</span>
        </div>
      </div>
      <div class="wrapper" v-show="current === 2">
        <div class="title">
          账号登录
          <!--@click="current = 3"-->
          <span
            @click="ewmLogin"
            v-if="appidNum"
            class="iconfont icon-weixindenglu1"
          ></span>
          <!-- <a :href="`https://open.weixin.qq.com/connect/qrconnect?appid=${appidNum}&redirect_uri=${hosts}&response_type=code&scope=snsapi_login&state=EqMkUDWh8F3euWlt23jHJ8ZJuaTAVPZyiKEoq5U0`" v-if="appidNum" class="iconfont icon-weixindenglu1"></a> -->
        </div>
        <div class="item phone acea-row row-middle">
          <div class="number">+86</div>
          <input
            type="text"
            placeholder="请输入手机号"
            maxlength="11"
            v-model="account"
          />
        </div>
        <div class="item pwd">
          <input type="password" placeholder="请输入密码" v-model="password" />
        </div>
        <div class="isAgree">
          <el-checkbox v-model="agreement"></el-checkbox
          ><span class="agree"
            >我已阅读并同意<span class="agreement" @click="agreementTap(4)"
              >《用户协议》</span
            >和<span class="agreement" @click="agreementTap(3)"
              >《隐私协议》</span
            ></span
          >
        </div>
        <div class="signIn bg-color" @click="loginH5">登录</div>
        <div class="login-switch">
          <span class="switch-item" @click="current = 1">手机登录</span>
          <span class="switch-divider">|</span>
          <span class="switch-item" @click="current = 3">邮箱登录</span>
        </div>
        <div class="register-link">
          <span class="register-text">还没有账号？</span>
          <span class="register-btn font-color" @click="current = 4">立即注册</span>
        </div>
      </div>
      <!--<div class="wxLogin" v-if="current === 3">-->
      <!--<div class="title">扫码登录<div class="iconfont icon-zhanghaodenglu1" @click="current = 1"></div></div>-->
      <!--<div class="wxCode">-->
      <!--<div class="acea-row row-between-wrapper">-->
      <!--<span class="iconfont icon-erweimabianjiao"></span>-->
      <!--<span class="iconfont icon-erweimabianjiao right"></span>-->
      <!--</div>-->
      <!--<div class="pictrue">-->
      <!--<img src="../assets/images/loginBg.jpg">-->
      <!--</div>-->
      <!--<div class="acea-row row-between-wrapper">-->
      <!--<span class="iconfont icon-erweimabianjiao bottomL"></span>-->
      <!--<span class="iconfont icon-erweimabianjiao right bottomR"></span>-->
      <!--</div>-->
      <!--</div>-->
      <!--<div class="tip">请使用微信扫一扫登录</div>-->
      <!--</div>-->
    </div>
    <div class="footer wrapper_1200">
      <div>
        <span>联系电话：{{ info.contact_number }}</span>
        <span class="adress">地址：{{ info.company_address }}</span>
      </div>
      <div class="record">
        {{ info.copyright
        }}<a href="https://beian.miit.gov.cn/" target="_blank" class="num">{{
          info.record_No
        }}</a>
      </div>
    </div>
    <el-dialog
      class="detail-bd"
      :title="agreementTitle"
      :visible.sync="userAgreement"
      :show-close="false"
      width="900px"
      center
    >
      <div class="userAgree" v-html="agreementCon"></div>
      <span slot="footer" class="dialog-footer">
        <el-button type="primary" @click="agreementClose">确 定</el-button>
      </span>
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
import Verify from "@/components/verifition/Verify";

export default {
  name: "login",
  auth: false,
  mixins: [sendVerifyCode],
  components: { Verify },
  data() {
    return {
      verifyModal: false,
      current: 3,
      account: "",
	      email: "",
	      password: "",
		      captcha: "",
		      nickname: "",
		      real_name: "",
		      sex: "",
		      registerVerifyType: "phone",
		      keyCode: "",
		      info: "",
		      isShow: true,
      appidNum: "",
      hosts: "",
      codes: "",
      fromPath: "",
      agreement: false,
      userAgreement: false,
      agreementCon1: "",
      agreementCon2: "",
      agreementCon: "",
      agreementTitle: ""
    };
  },
  async asyncData({ $axios, query }) {
    const keyCode = await $axios.$get("/verify_code");
    const companyInfo = await $axios.$get("/pc/get_company_info");
    const appidNum = await $axios.$get("/pc/get_appid");
    const agreement1 = await $axios.$get("/get_agreement/4");
    const agreement2 = await $axios.$get("/get_agreement/3");
    return {
      keyCode: keyCode.key,
      info: companyInfo,
      appidNum: appidNum.appid,
      codes: query.code || "",
      agreementCon1: agreement1.content,
      agreementCon2: agreement2.content
    };
  },
  fetch({ store }) {
    store.commit("isHeader", false);
    store.commit("isFooter", false);
  },
  head() {
    return {
      title: this.$store.state.titleCon
    };
  },
  created() {
    if (this.$auth.loggedIn) {
      this.$router.push("/");
    }
  },
  mounted() {
    window.addEventListener("keydown", this.keyDown);
    this.hosts = location.origin + location.pathname;
    this.fromPath = this.$cookies.get("fromPath");
    if (this.codes) {
      this.loginCode();
    }
  },
  destroyed() {
    window.removeEventListener("keydown", this.keyDown, false);
  },
  methods: {
    keyDown(e) {
      if (e.keyCode === 13) {
        if (this.current === 1) this.loginMobile();
        else if (this.current === 2) this.loginH5();
        else if (this.current === 3) this.loginEmail();
        else if (this.current === 4) this.doRegister();
      }
    },
    ewmLogin() {
      if (!this.agreement) return this.$message.error("请确认阅读用户协议");
      window.location.href = `https://open.weixin.qq.com/connect/qrconnect?appid=${this.appidNum}&redirect_uri=${this.hosts}&response_type=code&scope=snsapi_login&state=EqMkUDWh8F3euWlt23jHJ8ZJuaTAVPZyiKEoq5U0`;
    },
    agreementTap(type) {
      if (type == 4) {
        this.agreementTitle = "用户协议";
        this.agreementCon = this.agreementCon1;
      } else {
        this.agreementTitle = "隐私协议";
        this.agreementCon = this.agreementCon2;
      }
      this.userAgreement = true;
    },
    agreementClose() {
      this.userAgreement = false;
      this.agreement = true;
    },
    goHome() {
      this.$router.push({ path: "/" });
    },
    async loginCode() {
      let that = this;
      await that.$auth
        .loginWith("local3", { params: { code: this.codes } })
        .then(() => {
          that.isShow = false;
          if (this.fromPath) {
            let path = this.fromPath.split(that.$router.history.base);
            let fromPath = path.join("");
            that.$router.push(fromPath);
          } else {
            that.$router.push("/");
          }
          that.$cookies.remove("fromPath");
        })
        .catch(err => {
          // that.$layer.msg('登录失败');
        });
    },
    async loginH5() {
      let that = this;
      if (!that.agreement) return that.$message.error("请确认阅读用户协议");
      if (!that.account) return that.$message.error("请填写手机号码");
      if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
        return that.$message.error("请输入正确的手机号码");
      if (!that.password) return that.$message.error("请填写密码");
      let userInfo = {
        account: that.account,
        password: that.password
      };
      await that.$auth
        .loginWith("local1", { data: userInfo })
        .then(() => {
          that.isShow = false;
          if (this.fromPath) {
            let path = this.fromPath.split(that.$router.history.base);
            let fromPath = path.join("");
            that.$router.push(fromPath);
          } else {
            that.$router.push("/");
          }
          that.$cookies.remove("fromPath");
        })
        .catch(err => {
          that.$message.error(err);
        });
    },
	    getVerify() {
	      if (this.current === 3 || (this.current === 4 && this.registerVerifyType === "email")) {
	        if (!this.email) return this.$message.error("请填写邮箱");
	        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email))
	          return this.$message.error("邮箱格式不正确");
	      } else {
        if (!this.account) return this.$message.error("请填写手机号码");
        if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(this.account))
          return this.$message.error("请输入正确的手机号码");
      }
      if (!this.agreement) return this.$message.error("请确认阅读用户协议");
      this.verifyModal = true;
      this.$nextTick(e => {
        this.$refs.verify.show();
      });
    },
    success(params) {
      this.closeModel(params);
    },
    // 关闭模态框
    closeModel(params) {
      this.code(params);
    },
    async loginMobile() {
      let that = this;
      if (!that.agreement) return that.$message.error("请确认阅读用户协议");
      if (!that.account) return that.$message.error("请填写手机号码");
      if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
        return that.$message.error("请输入正确的手机号码");
      if (!that.captcha) return that.$message.error("请填写验证码");
      if (!/^[\w\d]+$/i.test(that.captcha))
        return that.$message.error("请输入正确的验证码");
      let userInfo = {
        phone: that.account,
        captcha: that.captcha
      };
      await that.$auth
	        .loginWith("local2", { data: userInfo })
	        .then(() => {
	          that.isShow = false;
	          if (this.fromPath) {
            let path = this.fromPath.split(that.$router.history.base);
            let fromPath = path.join("");
            that.$router.push(fromPath);
          } else {
            that.$router.push("/");
          }
	          that.$cookies.remove("fromPath");
	        })
	        .catch(err => {
	          const msg = String(err || "");
	          if (msg.includes("请先注册") || msg.includes("sign up") || msg === "411604") {
	            that.$message.error(msg);
	            that.current = 4;
	            return;
	          }
	          that.$message.error(msg || "验证码错误");
	        });
	    },
    async loginEmail() {
      let that = this;
      if (!that.agreement) return that.$message.error("请确认阅读用户协议");
      if (!that.email) return that.$message.error("请填写邮箱");
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(that.email))
        return that.$message.error("邮箱格式不正确");
      if (!that.captcha) return that.$message.error("请填写验证码");
      if (!/^[\w\d]+$/i.test(that.captcha))
        return that.$message.error("请输入正确的验证码");
      let userInfo = {
        email: that.email,
        captcha: that.captcha
      };
      await that.$auth
        .loginWith("local4", { data: userInfo })
        .then(() => {
          that.isShow = false;
          if (this.fromPath) {
            let path = this.fromPath.split(that.$router.history.base);
            let fromPath = path.join("");
            that.$router.push(fromPath);
          } else {
            that.$router.push("/");
          }
          that.$cookies.remove("fromPath");
        })
        .catch(err => {
          that.$message.error(err);
        });
    },
    async doRegister() {
      let that = this;
      if (!that.agreement) return that.$message.error("请确认阅读用户协议");
      if (!that.account) return that.$message.error("请填写手机号码");
      if (!/^1(3|4|5|7|8|9|6)\d{9}$/i.test(that.account))
        return that.$message.error("请输入正确的手机号码");
      if (!that.captcha) return that.$message.error("请填写验证码");
      if (!/^[\w\d]+$/i.test(that.captcha))
        return that.$message.error("请输入正确的验证码");
      if (!that.email) return that.$message.error("请填写邮箱");
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(that.email))
        return that.$message.error("邮箱格式不正确");
      if (!that.nickname) return that.$message.error("请填写昵称");
      if (!that.real_name) return that.$message.error("请填写真实姓名");
      if (!that.password) return that.$message.error("请填写密码");

	      await this.$axios
	        .post("/register", {
	          account: that.account,
	          captcha: that.captcha,
	          password: that.password,
	          email: that.email,
	          nickname: that.nickname,
	          real_name: that.real_name,
	          sex: Number(that.sex) || 0,
	          verify_type: that.registerVerifyType,
	          spread: 0
	        })
        .then(res => {
          that.$message.success(res.msg || "注册成功");
          that.current = 3;
        })
        .catch(err => {
          that.$message.error(err);
        });
    },
    async code(data) {
      let that = this;
      if (!that.agreement) return that.$message.error("请确认阅读用户协议");
      // 刷新一次 key，避免页面停留过久导致 key 过期
      const keyCode = await this.$axios.$get("/verify_code");
      this.keyCode = keyCode.key;

	      if (this.current === 3) {
	        await this.$axios
	          .post("/email/verify", {
	            email: that.email,
	            type: "login",
            key: that.keyCode,
            captchaType: "blockPuzzle",
            captchaVerification: data.captchaVerification
          })
          .then(res => {
            that.$message.success(res.msg);
            that.sendCode();
          })
          .catch(err => {
            that.$message.error(err);
          });
	        return;
	      }

	      if (this.current === 4 && that.registerVerifyType === "email") {
	        if (!that.email) return that.$message.error("请填写邮箱");
	        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(that.email))
	          return that.$message.error("邮箱格式不正确");
	        await this.$axios
	          .post("/email/verify", {
	            email: that.email,
	            type: "register",
	            key: that.keyCode,
	            captchaType: "blockPuzzle",
	            captchaVerification: data.captchaVerification
	          })
	          .then(res => {
	            that.$message.success(res.msg);
	            that.sendCode();
	          })
	          .catch(err => {
	            that.$message.error(err);
	          });
	        return;
	      }

	      await this.$axios
	        .post("/register/verify", {
	          phone: that.account,
	          type: this.current === 4 ? "register" : "login",
          key: that.keyCode,
          captchaType: "blockPuzzle",
          captchaVerification: data.captchaVerification
        })
	        .then(res => {
	          that.$message.success(res.msg);
	          that.sendCode();
	        })
	        .catch(err => {
	          const msg = String(err || "");
	          that.$message.error(msg);
	          if (msg.includes("请先注册") || msg.includes("sign up") || msg === "411604") {
	            that.current = 4;
	          }
	        });
	    }
	  }
	};
	</script>

<style scoped lang="scss">
.login {
  .header {
    height: 110px;
    .icon {
      cursor: pointer;
      width: 112px;
      height: 40px;
      img {
        width: 100%;
        height: 100%;
      }
    }
    .name {
      font-size: 28px;
      margin-left: 15px;
      cursor: pointer;
    }
    .item {
      margin-left: 40px;
      font-size: 16px;
      color: #666666;
      .iconfont {
        margin-right: 6px;
        font-size: 20px;
      }
    }
  }
  .loginBg {
    width: 100%;
    height: 608px;
    background: url(../assets/images/loginBg.jpg) no-repeat;
    background-size: 100% 100%;
    position: relative;
    .wxLogin {
      width: 450px;
      height: 427px;
      background: #ffffff;
      position: absolute;
      right: 360px;
      top: 91px;
      padding-top: 34px;
      .title {
        font-weight: 400;
        font-size: 20px;
        padding-left: 30px;
        position: relative;
        .iconfont {
          font-size: 60px;
          position: absolute;
          right: 0;
          top: -35px;
        }
      }
      .wxCode {
        width: 220px;
        margin: 38px auto 0 auto;
        .iconfont {
          font-size: 30px;
          color: #cbcbcb;
          &.right {
            transform: rotateY(180deg);
          }
          &.bottomL {
            transform: rotateX(180deg);
          }
          &.bottomR {
            transform: rotateX(180deg);
          }
        }
        .pictrue {
          width: 190px;
          height: 190px;
          margin: -15px auto;
          img {
            width: 100%;
            height: 100%;
          }
        }
      }
      .tip {
        color: #666;
        font-size: 16px;
        margin-top: 20px;
        text-align: center;
      }
    }
	    .wrapper {
      width: 450px;
      height: auto;
      min-height: 427px;
      background-color: #fff;
      position: absolute;
      top: 91px;
      right: 360px;
      text-align: center;
      padding: 50px 0;
      &.wrapper-register {
        padding: 40px 0;
      }
      .title {
        font-size: 20px;
        font-weight: 400;
        position: relative;
        .iconfont {
          position: absolute;
          top: -71px;
          right: 0;
          font-size: 60px;
          cursor: pointer;
        }
      }
	      .item {
	        width: 358px;
	        height: 50px;
	        border: 1px solid #dbdbdb;
	        margin: 16px auto 0 auto;
        &:first-of-type {
          margin-top: 30px;
        }
        &.phone {
          .number {
            width: 65px;
            height: 100%;
            line-height: 50px;
            color: #666666;
            border-right: 1px solid #dbdbdb;
          }
          input {
            width: 291px;
          }
        }
        &.pwd {
          input {
            width: 100%;
          }
        }
        &.verificat {
          input {
            width: 246px;
          }
          .code {
            width: 110px;
            height: 100%;
            border: 0;
            background-color: #fff;
            border-left: 1px solid #dbdbdb;
            &.on {
              color: #ccc !important;
            }
          }
        }
	        input {
	          padding-left: 15px;
	          height: 100%;
	          border: 0;
	          outline: none;
	        }
		        select.sexSelect {
		          width: 100%;
		          height: 100%;
		          padding: 0 15px;
		          border: 0;
		          outline: none;
		          background: transparent;
		          color: #333;
		          -webkit-appearance: none;
		          -moz-appearance: none;
		          appearance: none;
		          &.is-placeholder {
		            color: #999;
		          }
		        }
	      }
	      .signIn {
	        width: 358px;
	        height: 50px;
	        text-align: center;
        line-height: 50px;
        margin: 24px auto 0 auto;
        color: #fff;
        cursor: pointer;
      }
      .fastLogin {
        margin-top: 14px;
        cursor: pointer;
      }
      .login-switch {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
        .switch-item {
          font-size: 14px;
          color: #666;
          cursor: pointer;
          padding: 0 12px;
          &:hover {
            color: #e93323;
          }
        }
        .switch-divider {
          color: #ddd;
          font-size: 14px;
        }
      }
		      .verify-switch {
		        display: flex;
		        justify-content: center;
		        align-items: center;
		        width: 358px;
		        height: 34px;
		        margin: 10px auto 0;
		        user-select: none;
		        .switch-item {
		          font-size: 14px;
		          color: #666;
		          cursor: pointer;
		          padding: 0 12px;
		          &.on {
		            color: #e93323;
		            font-weight: 500;
		          }
		        }
		        .switch-divider {
		          color: #ddd;
		          font-size: 14px;
		        }
		      }
		      .register-link {
	        display: flex;
	        justify-content: center;
	        align-items: center;
	        margin-top: 16px;
	        font-size: 14px;
        .register-text {
          color: #999;
        }
	        .register-btn {
	          cursor: pointer;
	          margin-left: 4px;
	          font-weight: 500;
		      }
		    }
	    }
	  }
  .isAgree {
    width: 358px;
    margin: 12px auto 0 auto;
    text-align: left;
    .agree {
      margin-left: 6px;
      color: #999999;
      cursor: pointer;
      .agreement {
        color: #e93323;
      }
    }
  }
  .footer {
    text-align: center;
    font-size: 12px;
    color: #555;
    margin-top: 100px;
    .adress {
      margin-left: 40px;
    }
    .record {
      margin-top: 6px;
      .num {
        margin-left: 10px;
        &:hover {
          color: #e93323;
        }
      }
    }
  }
}
</style>
