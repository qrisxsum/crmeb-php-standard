<template>
	<view :style="colorStyle">
		<goodsCate1 v-if="category == 1" ref="classOne" :isNew="isNew"></goodsCate1>
		<goodsCate2 v-if="category == 2" ref="classTwo" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate2>
		<goodsCate3 v-if="category == 3" ref="classThree" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate3>
		<goodsCate4 v-if="category == 4" ref="classFour" :isNew="isNew" @jumpIndex="jumpIndex"></goodsCate4>
		<pageBottom v-if="category == 1"></pageBottom>
		<pageFooter v-if="category == 1" @newDataStatus="newDataStatus" v-show="showBar"></pageFooter>
	</view>
</template>

<script>
import colors from '@/mixins/color';
import goodsCate1 from './goods_cate1';
import goodsCate2 from './goods_cate2';
import goodsCate3 from './goods_cate3';
import goodsCate4 from './goods_cate4';
import { getCategoryConfig } from '@/api/api.js';
import { mapGetters } from 'vuex';
import { getCategoryVersion } from '@/api/public.js';
import pageFooter from '@/components/pageFooter/index.vue';
import pageBottom from '@/components/pageBottom/index.vue';
export default {
	computed: mapGetters(['isLogin', 'uid']),
	components: {
		goodsCate1,
		goodsCate2,
		goodsCate3,
		goodsCate4,
		pageFooter,
		pageBottom
	},
	mixins: [colors],
	data() {
		return {
			category: '',
			is_diy: uni.getStorageSync('is_diy'),
			status: 0,
			version: '',
			isNew: false,
			isFooter: false,
			showBar: false
		};
	},
	onLoad() {},
	onReady() {},
	onShow() {
		this.getCategoryVersion();
	},
	methods: {
		newDataStatus(val, num) {
			this.isFooter = val ? true : false;
			this.showBar = val ? true : false;
			this.pdHeight = num;
		},
		/**
		 * 清除所有分类相关的本地缓存
		 */
		clearLocalCategoryCache() {
			uni.removeStorageSync('CAT1_DATA');
			uni.removeStorageSync('CAT2_DATA');
			uni.removeStorageSync('CAT3_DATA');
			uni.removeStorageSync('CAT4_DATA');
		},
		getCategoryVersion() {
			uni.$emit('uploadFooter');
			getCategoryVersion().then((res) => {
				const localVersion = uni.getStorageSync('CAT_VERSION');
				const serverVersion = res.data.version;

				// 版本号变化，需要刷新缓存
				if (!localVersion || serverVersion !== localVersion) {
					// 1. 更新本地版本号
					uni.setStorageSync('CAT_VERSION', serverVersion);

					// 2. 清除所有本地分类缓存
					this.clearLocalCategoryCache();

					// 3. 标记需要刷新
					this.isNew = true;
				}

				// 4. 获取分类样式，确定要渲染哪个子组件
				this.classStyle();
			});
		},
		jumpIndex() {
			uni.reLaunch({
				url: '/pages/index/index'
			})
		},
		classStyle() {
			getCategoryConfig().then((res) => {
				let status = res.data.status;
				this.category = status;
				uni.setStorageSync('is_diy', res.data.is_diy);

				// 存储导航配置供子组件使用
				if (status == 4 && res.data.navigation) {
					uni.setStorageSync('category_navigation', res.data.navigation);
				}

				this.$nextTick((e) => {
					// 子组件挂载完成后，如果需要刷新数据，发送事件
					if (this.isNew) {
						uni.$emit('uploadCatData');
						this.isNew = false;  // 重置标记
					}

					if (status == 2 || status == 3) {
						// 样式2和3隐藏 tabBar
						uni.hideTabBar();
					} else if (status == 4) {
						// 样式4显示 tabBar
						uni.showTabBar();
					} else {
						// 样式1根据配置决定
						this.$refs.classOne.is_diy = res.data.is_diy;
						if (!this.is_diy) {
							uni.hideTabBar();
						} else {
							this.$refs.classOne.getNav();
						}
					}
				});
			});
		}
	},
	onReachBottom: function () {
		if (this.category == 2) {
			this.$refs.classTwo.productslist();
		}
		if (this.category == 3) {
			this.$refs.classThree.productslist();
		}
	}
};
</script>
<style scoped lang="scss">
::v-deep .mask {
	z-index: 99;
}
::-webkit-scrollbar {
	width: 0;
	height: 0;
	color: transparent;
	display: none;
}
</style>
