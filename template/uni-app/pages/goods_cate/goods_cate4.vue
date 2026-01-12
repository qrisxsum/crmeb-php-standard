<template>
	<view class="goods-cate4" :style="colorStyle">
		<!-- 推广卡片区域 -->
		<swiperBg
			v-if="diyConfig"
			:dataConfig="diyConfig"
			:isSortType="0"
		/>

		<!-- 面包屑导航 -->
		<view class="breadcrumb">
			<view class="crumb-item" @click="handleBreadcrumbClick('home')">
				<text>{{ $t('首页') }}</text>
			</view>
			<text class="separator">/</text>
			<view
				class="crumb-item"
				:class="{ active: viewMode === 'level1' }"
				@click="handleBreadcrumbClick('all')"
			>
				<text>{{ $t('全部分类') }}</text>
			</view>
			<template v-if="viewMode === 'level2' && currentL1">
				<text class="separator">/</text>
				<view class="crumb-item active">
					<text>{{ $t(currentL1.cate_name) }}</text>
				</view>
			</template>
		</view>

		<!-- 分类列表区域 -->
		<scroll-view scroll-y class="category-scroll" :style="{ height: scrollHeight }">
			<!-- 一级分类列表 -->
			<view v-if="viewMode === 'level1'" class="level1-list">
				<view
					v-for="item in categoryList"
					:key="item.id"
					class="category-item"
					@click="handleL1Click(item)"
					hover-class="item-hover"
				>
					<view class="item-left">
						<text class="category-name">{{ $t(item.cate_name) }}</text>
					</view>
					<view class="item-right">
						<text class="category-count" v-if="item.children && item.children.length">
							{{ item.children.length }}{{ $t('个分类') }}
						</text>
						<text class="iconfont icon-xiangyou arrow-icon"></text>
					</view>
				</view>
			</view>

			<!-- 二级分类网格 -->
			<view v-else-if="viewMode === 'level2'" class="level2-list">
				<view class="grid-container">
					<view
						v-for="item in currentL2List"
						:key="item.id"
						class="category-item"
						@click="handleL2Click(item)"
					>
						<view class="category-image">
							<easy-loadimage
								mode="aspectFill"
								:image-src="item.pic || defaultImg"
							/>
						</view>
						<text class="category-name line1">{{ $t(item.cate_name) }}</text>
					</view>
				</view>
			</view>

			<!-- 空状态 -->
			<view v-if="!loading && categoryList.length === 0" class="empty-state">
				<image :src="emptyImg" mode="aspectFit" class="empty-img" />
				<text class="empty-text">{{ $t('暂无分类数据') }}</text>
			</view>
		</scroll-view>
	</view>
</template>

<script>
import { getCategoryList } from '@/api/store.js';
import { getDiy } from '@/api/api.js';
import swiperBg from '@/pages/index/components/swiperBg.vue';
import colors from '@/mixins/color';

export default {
	name: 'goodsCate4',
	components: {
		swiperBg
	},
	mixins: [colors],
	props: {
		isNew: {
			type: Boolean,
			default: false
		}
	},
	data() {
		return {
			// 视图状态
			viewMode: 'level1',    // 'level1' | 'level2'

			// 分类数据
			categoryList: [],       // 完整分类树
			currentL1: null,        // 当前选中的一级分类
			currentL2List: [],      // 当前二级分类列表

			// 推广配置
			diyConfig: null,        // swiperBg 组件配置

			// UI 控制
			loading: false,
			scrollHeight: 'calc(100vh - 200rpx)',

			// 默认资源
			defaultImg: require('@/static/images/f.png'),
			emptyImg: require('@/static/images/f.png')
		};
	},
	mounted() {
		this.init();

		// 监听分类数据更新事件
		uni.$on('uploadCatData', () => {
			this.loadCategoryData(true);
		});
	},
	methods: {
		async init() {
			try {
				// 并行加载分类数据和DIY配置
				await Promise.all([
					this.loadCategoryData(false),
					this.loadDiyConfig()
				]);
			} catch (error) {
				console.error('初始化失败', error);
			}
		},

		async loadCategoryData(forceRefresh) {
			const cacheKey = 'CAT4_DATA';
			this.loading = true;

			try {
				if (!forceRefresh && uni.getStorageSync(cacheKey)) {
					this.categoryList = uni.getStorageSync(cacheKey);
				} else {
					const res = await getCategoryList();
					this.categoryList = res.data || [];
					uni.setStorageSync(cacheKey, res.data);
				}
			} catch (error) {
				console.error('加载分类数据失败', error);
				this.$util.Tips({ title: this.$t('加载分类失败') });
			} finally {
				this.loading = false;
			}
		},

		async loadDiyConfig() {
			try {
				const res = await getDiy(0);
				if (res.data && res.data.value) {
					const diyArr = this.objToArr(res.data.value);
					const swiperConfig = diyArr.find(item => item.name === 'swiperBg');
					if (swiperConfig) {
						this.diyConfig = swiperConfig;
					}
				}
			} catch (error) {
				console.warn('DIY配置加载失败', error);
			}
		},

		objToArr(obj) {
			const arr = [];
			for (let key in obj) {
				arr.push(obj[key]);
			}
			return arr;
		},

		handleL1Click(category) {
			this.currentL1 = category;
			this.currentL2List = category.children || [];
			this.viewMode = 'level2';
		},

		handleL2Click(category) {
			const cid = this.currentL1.id;
			const sid = category.id;
			const title = category.cate_name;

			uni.navigateTo({
				url: `/pages/goods/goods_list/index?cid=${cid}&sid=${sid}&title=${encodeURIComponent(title)}`
			});
		},

		backToL1() {
			this.viewMode = 'level1';
			this.currentL1 = null;
			this.currentL2List = [];
		},

		handleBreadcrumbClick(level) {
			if (level === 'home') {
				this.$emit('jumpIndex');
			} else if (level === 'all') {
				this.backToL1();
			}
		}
	},

	// 处理物理返回键
	onBackPress() {
		if (this.viewMode === 'level2') {
			this.backToL1();
			return true;
		}
		return false;
	}
};
</script>

<style lang="scss" scoped>
.goods-cate4 {
	min-height: 100vh;
	background-color: #FAFAFA;
}

/* 面包屑导航 */
.breadcrumb {
	height: 80rpx;
	padding: 0 40rpx;
	display: flex;
	align-items: center;
	background-color: #fff;
	font-size: 26rpx;
	color: #999;

	.crumb-item {
		&.active {
			color: #333;
			font-weight: 500;
		}
	}

	.separator {
		margin: 0 12rpx;
		color: #ddd;
	}
}

/* 分类滚动区域 */
.category-scroll {
	background-color: #fff;
}

/* 一级分类列表 - 日本风格 */
.level1-list {
	padding: 40rpx 60rpx;

	.category-item {
		padding: 36rpx 0;
		border-bottom: 1rpx solid #F0F0F0;
		display: flex;
		justify-content: space-between;
		align-items: center;

		&:last-child {
			border-bottom: none;
		}

		.item-left {
			flex: 1;

			.category-name {
				font-size: 32rpx;
				color: #333;
				letter-spacing: 1rpx;
			}
		}

		.item-right {
			display: flex;
			align-items: center;

			.category-count {
				font-size: 24rpx;
				color: #999;
				margin-right: 12rpx;
			}

			.arrow-icon {
				font-size: 28rpx;
				color: #BFBFBF;
			}
		}
	}

	.item-hover {
		background-color: #F7F7F7;
	}
}

/* 二级分类网格 */
.level2-list {
	padding: 40rpx;

	.grid-container {
		display: flex;
		flex-wrap: wrap;
		justify-content: space-between;
	}

	.category-item {
		width: 200rpx;
		margin-bottom: 48rpx;
		text-align: center;

		.category-image {
			width: 200rpx;
			height: 200rpx;
			border-radius: 16rpx;
			overflow: hidden;
			background: #F7F7F7;
			margin-bottom: 20rpx;
		}

		.category-name {
			font-size: 26rpx;
			color: #333;
			display: block;
		}
	}
}

/* 空状态 */
.empty-state {
	padding: 200rpx 0;
	text-align: center;

	.empty-img {
		width: 300rpx;
		height: 300rpx;
		margin-bottom: 40rpx;
	}

	.empty-text {
		font-size: 28rpx;
		color: #999;
	}
}
</style>
