<template>
	<view :style="colorStyle">
		<form @submit="submit">
			<view class="ChangePassword">
				<view class="list">
					<view class="item">
						<input type="text" :placeholder="$t(`填写新邮箱`)" placeholder-class="placeholder"
							v-model="email"></input>
					</view>
					<view class="item acea-row row-between-wrapper">
						<input type="number" :placeholder="$t(`填写验证码`)" placeholder-class="placeholder" class="codeIput"
							v-model="captcha"></input>
						<button class="code font-num" :class="disabled === true ? 'on' : ''" :disabled="disabled"
							@click="code">
							{{ text }}
						</button>
					</view>
					<view class="item">
						<input type="password" :placeholder="$t(`填写登录密码`)" placeholder-class="placeholder"
							v-model="password"></input>
					</view>
				</view>
				<button form-type="submit" class="confirmBnt bg-color">{{$t(`确认修改`)}}</button>
			</view>
		</form>

		<Verify @success="success" :captchaType="captchaType" :imgSize="{ width: '330px', height: '155px' }"
			ref="verify"></Verify>
	</view>
</template>

<script>
	import sendVerifyCode from "@/mixins/SendVerifyCode";
	import Verify from '../components/verify/index.vue';
	import {
		verifyCode
	} from '@/api/api.js';
	import {
		emailVerify,
		userEdit
	} from '@/api/user.js';
	import {
		mapGetters
	} from "vuex";
	import {
		toLogin
	} from '@/libs/login.js';
	import colors from '@/mixins/color.js';

	export default {
		mixins: [sendVerifyCode, colors],
		components: {
			Verify
		},
		data() {
			return {
				email: '',
				captcha: '',
				password: ''
			};
		},
		computed: mapGetters(['isLogin']),
		onLoad() {
			if (!this.isLogin) toLogin();
		},
		methods: {
			isValidEmail(email) {
				return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || '').trim());
			},
			code() {
				if (!this.email) return this.$util.Tips({
					title: this.$t(`请填写新邮箱`)
				});
				if (!this.isValidEmail(this.email)) return this.$util.Tips({
					title: this.$t(`邮箱格式不正确`)
				});
				this.$refs.verify.show();
			},
			success(data) {
				this.$refs.verify.hide();
				verifyCode().then(res => {
					return emailVerify({
						email: this.email,
						type: 'register',
						key: res.data.key,
						captchaType: this.captchaType,
						captchaVerification: data.captchaVerification
					});
				}).then(res => {
					this.$util.Tips({
						title: res.msg
					});
					this.sendCode();
				}).catch(err => {
					return this.$util.Tips({
						title: err
					});
				});
			},
			submit() {
				if (!this.email) return this.$util.Tips({
					title: this.$t(`请填写新邮箱`)
				});
				if (!this.isValidEmail(this.email)) return this.$util.Tips({
					title: this.$t(`邮箱格式不正确`)
				});
				if (!this.captcha) return this.$util.Tips({
					title: this.$t(`请填写验证码`)
				});
				if (!this.password) return this.$util.Tips({
					title: this.$t(`请填写登录密码`)
				});

				userEdit({
					email: this.email,
					email_captcha: this.captcha,
					password: this.password
				}).then(res => {
					return this.$util.Tips({
						title: res.msg,
						icon: 'success'
					}, {
						tab: 5,
						url: '/pages/users/user_info/index'
					});
				}).catch(err => {
					return this.$util.Tips({
						title: err || this.$t(`保存失败`)
					});
				});
			}
		}
	}
</script>

<style lang="scss">
	page {
		background-color: #fff !important;
	}

	.ChangePassword .list {
		width: 580rpx;
		margin: 53rpx auto 0 auto;
	}

	.ChangePassword .list .item {
		width: 100%;
		height: 96rpx;
		border-bottom: 1px solid #f0f0f0;
		box-sizing: border-box;
	}

	.ChangePassword .list .item input {
		width: 100%;
		height: 96rpx;
		font-size: 30rpx;
	}

	.ChangePassword .list .item .codeIput {
		width: 380rpx;
	}

	.ChangePassword .list .item .code {
		width: 200rpx;
		height: 60rpx;
		line-height: 60rpx;
		border-radius: 30rpx;
		text-align: center;
		font-size: 26rpx;
		color: var(--view-theme);
		border: 1px solid var(--view-theme);
	}

	.ChangePassword .list .item .code.on {
		color: #999;
		border-color: #ccc;
	}

	.confirmBnt {
		width: 580rpx;
		height: 86rpx;
		line-height: 86rpx;
		margin: 60rpx auto 0 auto;
		border-radius: 43rpx;
		color: #fff;
		font-size: 30rpx;
	}
</style>
