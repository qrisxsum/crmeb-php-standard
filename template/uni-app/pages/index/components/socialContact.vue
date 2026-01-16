<template>
	<!-- 联系我们社交媒体浮窗 -->
	<view class="social-contact" v-show="!isSortType && hasEnabledLinks">
		<view class="social-list"
			:class="{ 'on': !positions }"
			:style="containerStyle"
			@touchmove.stop.prevent="setTouchMove">
			<view
				v-for="(item, index) in enabledLinks"
				:key="index"
				class="social-item"
				:style="itemStyle"
				@click="handleClick(item)">
				<image :src="item.icon" mode="aspectFit" />
			</view>
		</view>
	</view>
</template>

<script>
export default {
	name: 'socialContact',
	props: {
		dataConfig: {
			type: Object,
			default: () => ({})
		},
		isSortType: {
			type: [String, Number],
			default: 0
		}
	},
	data() {
		return {
			topConfig: 30,
			positions: 1, // 0左 1右
			socialList: [],
			iconSize: 40,
			iconSpacing: 8,
		};
	},
	computed: {
		enabledLinks() {
			return this.socialList.filter(item => item.enabled && item.icon);
		},
		hasEnabledLinks() {
			return this.enabledLinks.length > 0;
		},
		containerStyle() {
			const top = this.topConfig >= 80 ? 80 : (this.topConfig <= 10 ? 10 : this.topConfig);
			return {
				top: top + '%',
			};
		},
		itemStyle() {
			return {
				width: this.iconSize * 2 + 'rpx',
				height: this.iconSize * 2 + 'rpx',
				marginBottom: this.iconSpacing * 2 + 'rpx',
			};
		},
	},
	created() {
		this.initConfig();
	},
	methods: {
		initConfig() {
			if (!this.dataConfig) return;

			// 初始化社交链接列表
			this.socialList = this.dataConfig.socialList || [];

			// 初始化位置配置
			if (this.dataConfig.locationConfig) {
				this.positions = this.dataConfig.locationConfig.tabVal;
			}

			// 初始化上下偏移
			if (this.dataConfig.topConfig) {
				this.topConfig = this.dataConfig.topConfig.val || 30;
			}

			// 初始化图标大小
			if (this.dataConfig.iconSizeConfig) {
				this.iconSize = this.dataConfig.iconSizeConfig.val || 40;
			}

			// 初始化图标间距
			if (this.dataConfig.iconSpacingConfig) {
				this.iconSpacing = this.dataConfig.iconSpacingConfig.val || 8;
			}
		},
		handleClick(item) {
			if (!item.url) {
				uni.showToast({
					title: '链接未配置',
					icon: 'none'
				});
				return;
			}

			// 判断链接类型并跳转
			const url = item.url;

			// 检查是否是外部链接
			if (url.startsWith('http://') || url.startsWith('https://')) {
				// #ifdef H5
				window.open(url, '_blank');
				// #endif

				// #ifdef APP-PLUS
				plus.runtime.openURL(url);
				// #endif

				// #ifdef MP
				// 小程序中需要使用web-view页面打开外部链接
				uni.navigateTo({
					url: `/pages/annex/web_view/index?url=${encodeURIComponent(url)}`
				});
				// #endif
			} else {
				// 内部页面跳转
				this.$util.JumpPath(url);
			}
		},
		setTouchMove(e) {
			// 拖动调整位置
			const clientY = e.touches[0].clientY;
			const windowHeight = uni.getSystemInfoSync().windowHeight;
			const percentage = (clientY / windowHeight) * 100;

			// 限制范围在10%-80%
			if (percentage >= 10 && percentage <= 80) {
				this.topConfig = percentage;
			}
		},
	}
};
</script>

<style lang="scss" scoped>
.social-contact {
	touch-action: none;

	.social-list {
		position: fixed;
		right: 20rpx;
		z-index: 40;
		display: flex;
		flex-direction: column;
		align-items: center;

		&.on {
			right: auto;
			left: 20rpx;
		}

		.social-item {
			width: 80rpx;
			height: 80rpx;
			display: flex;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			transition: transform 0.2s ease;

			&:last-child {
				margin-bottom: 0 !important;
			}

			&:active {
				transform: scale(0.9);
			}

			image {
				width: 100%;
				height: 100%;
				border-radius: 50%;
			}
		}
	}
}
</style>
