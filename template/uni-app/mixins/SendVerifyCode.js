// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2024 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

export default {
	data() {
		return {
			disabled: false,
			text: this.$t('验证码'),
			runTime: undefined,
			codeEndAt: 0,
			captchaType: 'clickWord'
		};
	},
	methods: {
		sendCode() {
			if (this.disabled) return;
			const seconds = 60;
			this.codeEndAt = Date.now() + seconds * 1000;
			uni.setStorageSync('verify_code_end_at', this.codeEndAt);
			this.startCountdown();
		},
		startCountdown() {
			if (this.runTime) clearInterval(this.runTime);
			this.disabled = true;
			const tick = () => {
				const remain = Math.ceil((this.codeEndAt - Date.now()) / 1000);
				if (remain <= 0) {
					if (this.runTime) clearInterval(this.runTime);
					this.runTime = undefined;
					this.codeEndAt = 0;
					uni.removeStorageSync('verify_code_end_at');
					this.disabled = false;
					this.text = this.$t('重新获取');
					return;
				}
				this.text = this.$t('剩余') + remain + 's';
			};
			tick();
			this.runTime = setInterval(tick, 1000);
		}
	},
	onShow() {
		const endAt = Number(uni.getStorageSync('verify_code_end_at') || 0);
		if (endAt > Date.now()) {
			this.codeEndAt = endAt;
			this.startCountdown();
		} else if (endAt) {
			uni.removeStorageSync('verify_code_end_at');
		}
	},
	onHide() {
		clearInterval(this.runTime);
	}
};
