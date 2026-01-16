<template>
	<view class="wrapper" :style="colorStyle">
		<view class='productList'>
			<view class='search bg-color acea-row row-between-wrapper'>
				<view class='input acea-row row-between-wrapper'><text class='iconfont icon-sousuo'></text>
					<input :placeholder='$t(`搜索商品名称`)' placeholder-class='placeholder' confirm-type='search'
						name="search" :value='where.keyword' @confirm="searchSubmit"></input>
				</view>
				<view class="view-switch acea-row row-middle">
					<view class="switch-btn" :class="{ active: viewMode === 0 }" @click="changeViewMode(0)">
						<text class="iconfont icon-caidan"></text>
					</view>
					<view class="switch-btn" :class="{ active: viewMode === 1 }" @click="changeViewMode(1)">
						<text class="iconfont icon-tupianpailie"></text>
					</view>
					<view class="switch-btn" :class="{ active: viewMode === 2 }" @click="changeViewMode(2)">
						<text class="iconfont icon-liebiao1"></text>
					</view>
				</view>
			</view>

			<view class='nav acea-row row-middle'>
				<view class='item line1' :class='title ? "font-num":""' @click='set_where(1)'>
					{{title ? $t(title) : $t(`默认`)}}
				</view>
				<view class='item' @click='set_where(2)'>
					{{$t(`价格`)}}
					<image v-if="price==1" src='../../../static/images/up.png'></image>
					<image v-else-if="price==2" src='../../../static/images/down.png'></image>
					<image v-else src='../../../static/images/horn.png'></image>
				</view>
				<view class='item' @click='set_where(3)'>
					{{$t(`销量`)}}
					<image v-if="stock==1" src='../../../static/images/up.png'></image>
					<image v-else-if="stock==2" src='../../../static/images/down.png'></image>
					<image v-else src='../../../static/images/horn.png'></image>
				</view>
				<!-- down -->
				<view class='item' :class='nows ? "font-color":""' @click='set_where(4)'>{{$t(`新品`)}}</view>
			</view>
			<scroll-view :scroll-top="scrollTop" scroll-y="true" class="scroll-Y" @scroll="scroll">
				<!-- SEO文案展示区域 -->
				<view class="seo-header" v-if="categoryInfo.seo_description || categoryInfo.seo_title">
					<view class="seo-title" v-if="categoryInfo.seo_title">
						{{ categoryInfo.seo_title }}
					</view>
					<view class="seo-description" v-if="categoryInfo.seo_description">
						{{ categoryInfo.seo_description }}
					</view>
				</view>

				<!-- 分页按钮 - 顶部 -->
				<view class="pagination-top" v-if="productList.length > 0">
					<view class="pagination-button pagination-nav" @click="prevPage" :class="{ disabled: where.page <= 1 }">
						< {{$t(`上一页`)}}
					</view>
					<view class="page-info">
						{{$t(`第`)}} {{where.page}} {{$t(`页`)}}
					</view>
					<view class="pagination-button pagination-nav" @click="nextPage" :class="{ disabled: loadend || where.page >= maxPage }">
						{{$t(`下一页`)}} >
					</view>
				</view>
				<!-- 列表视图 viewMode === 0 -->
				<view class="list list-view" v-if="viewMode === 0">
					<view class="item" v-for="(item,index) in productList" :key="index" @click="godDetail(item)">
						<view class="pictrue">
							<image :src="item.image"></image>
							<span class="pictrue_log" v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{$t(`秒杀`)}}</span>
							<span class="pictrue_log" v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{$t(`砍价`)}}</span>
							<span class="pictrue_log" v-if="item.activity && item.activity.type === '3' && $permission('combination')">{{$t(`拼团`)}}</span>
						</view>
						<view class="text">
							<view class="name line2">{{item.store_name}}</view>
							<view class="money font-color">{{$t(`￥`)}}<text class="num">{{item.price}}</text></view>
							<view class="vip acea-row row-between-wrapper">
								<view class="vip-money" v-if="item.vip_price && item.vip_price > 0">
									{{$t(`￥`)}}{{item.vip_price}}
									<image src="../../../static/images/vip.png"></image>
								</view>
								<view v-else></view>
								<view>{{$t(`已售`)}} {{item.sales}}{{$t(item.unit_name) || $t(`件`)}}</view>
							</view>
						</view>
					</view>
				</view>

				<!-- 大图视图 viewMode === 1 -->
				<view class="list large-view" v-else-if="viewMode === 1">
					<view class="item" v-for="(item,index) in productList" :key="index" @click="godDetail(item)">
						<view class="pictrue">
							<image :src="item.image" mode="widthFix"></image>
							<view class="stock-tag" v-if="item.stock > 0">{{$t(`库存`)}}:{{item.stock}}</view>
							<span class="pictrue_log" v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{$t(`秒杀`)}}</span>
							<span class="pictrue_log" v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{$t(`砍价`)}}</span>
							<span class="pictrue_log" v-if="item.activity && item.activity.type === '3' && $permission('combination')">{{$t(`拼团`)}}</span>
						</view>
						<view class="text">
							<view class="name line2">{{item.store_name}}</view>
							<view class="rating acea-row row-middle" v-if="item.star">
								<text class="stars">{{item.star}}</text>
								<text class="review-label">{{$t(`评分`)}}</text>
							</view>
							<view class="price-info acea-row row-between-wrapper">
								<view class="price-range font-color">
									{{$t(`￥`)}}{{item.price}}
									<text v-if="item.max_price && item.max_price != item.price"> - {{$t(`￥`)}}{{item.max_price}}</text>
								</view>
								<view class="sales">{{$t(`已售`)}} {{item.sales}}{{$t(item.unit_name) || $t(`件`)}}</view>
							</view>
						</view>
					</view>
				</view>

				<!-- 双列视图 viewMode === 2 -->
				<view class="list grid-view acea-row row-between-wrapper" v-else>
					<view class="item" v-for="(item,index) in productList" :key="index" @click="godDetail(item)">
						<view class="pictrue">
							<image :src="item.image"></image>
							<span class="pictrue_log_small" v-if="item.activity && item.activity.type === '1' && $permission('seckill')">{{$t(`秒杀`)}}</span>
							<span class="pictrue_log_small" v-if="item.activity && item.activity.type === '2' && $permission('bargain')">{{$t(`砍价`)}}</span>
							<span class="pictrue_log_small" v-if="item.activity && item.activity.type === '3' && $permission('combination')">{{$t(`拼团`)}}</span>
						</view>
						<view class="text">
							<view class="name line2">{{item.store_name}}</view>
							<view class="money font-color">{{$t(`￥`)}}<text class="num">{{item.price}}</text></view>
						</view>
					</view>
				</view>


			<!-- 分页按钮 - 底部 -->
			<view class="pagination-bottom" v-if="productList.length > 0">
				<!-- 第一行：分页按钮和页码 -->
				<view class="pagination-row pagination-row-top">
					<view class="pagination-button pagination-nav" @click="goToFirstPage" :class="{ disabled: where.page <= 1 }">
						««
					</view>
					<view class="page-numbers">
						<view
							class="page-number"
							v-for="page in pageNumbers"
							:key="page"
							:class="{ active: page === where.page }"
							@click="goToPageNumber(page)"
						>
							{{page}}
						</view>
					</view>
					<view class="pagination-button pagination-nav" @click="goToLastPage" :class="{ disabled: loadend || where.page >= maxPage }">
						»»
					</view>
				</view>
				<!-- 第二行：跳转框 -->
				<view class="pagination-row pagination-row-bottom">
					<view class="page-jump">
						<input type="number" v-model="jumpPage" class="jump-input"></input>
						<view class="jump-button" @click="goToPage">{{$t(`跳转`)}}</view>
					</view>
				</view>
			</view>

			<!-- 加载状态 -->
			<view class="loadingicon acea-row row-center-wrapper" v-if="loading">
				<text class="loading iconfont icon-jiazai"></text>{{$t(`加载中...`)}}
			</view>

			</scroll-view>

		</view>
		<view class='noCommodity' v-if="productList.length==0 && where.page > 1">
			<view class='emptyBox'>
				<image :src="imgHost + '/statics/images/no-thing.png'"></image>
				<view class="tips">{{$t(`暂无商品，去看点别的吧`)}}</view>
			</view>
			<recommend :hostProduct="hostProduct"></recommend>
		</view>
		<!-- #ifndef MP -->
		<home></home>
		<!-- #endif -->
		<view v-if="scrollTopShow" class="back-top" @click="goTop">
			<text class="iconfont icon-xiangshang"></text>
		</view>
	</view>
</template>

<script>
	import home from '@/components/home';
	import {
		getProductslist,
		getProductHot,
		getCategoryList
	} from '@/api/store.js';
	import recommend from '@/components/recommend';
	import {
		mapGetters
	} from "vuex";
	import {
		goShopDetail
	} from '@/libs/order.js'
	import {
		HTTP_REQUEST_URL
	} from '@/config/app';
	import colors from '@/mixins/color.js';
	export default {
		computed: {
			...mapGetters(['uid']),
			// 生成页码数组，显示当前页附近的页码
			pageNumbers() {
				const current = this.where.page;
				const total = this.maxPage;
				const pages = [];
				const showPages = 5; // 显示5个页码按钮

				if (total <= showPages) {
					// 如果总页数少于等于5，显示所有页码
					for (let i = 1; i <= total; i++) {
						pages.push(i);
					}
				} else {
					// 计算起始页码：当前页居中显示
					let start = current - Math.floor(showPages / 2);
					let end = start + showPages - 1;

					// 如果起始页码小于1，从1开始
					if (start < 1) {
						start = 1;
						end = showPages;
					}

					// 如果结束页码超过总页数，调整起始页码
					if (end > total) {
						end = total;
						start = total - showPages + 1;
					}

					// 确保总是显示5个页码
					for (let i = start; i <= end; i++) {
						pages.push(i);
					}
				}

				return pages;
			}
		},
		components: {
			recommend,
			home
		},
		mixins: [colors],
		data() {
			return {
				imgHost: HTTP_REQUEST_URL,
				productList: [],
				viewMode: 0,  // 0=列表视图, 1=大图视图, 2=双列视图
				where: {
					sid: 0,
					keyword: '',
					priceOrder: '',
					salesOrder: '',
					news: 0,
					page: 1,
					limit: 10,
					cid: 0,
				},
				price: 0,
				stock: 0,
				nows: false,
				loadend: false,
				loading: false,
				loadTitle: this.$t(`加载更多`),
				title: '',
				hostProduct: [],
				hotPage: 1,
				hotLimit: 10,
				hotScroll: false,
				scrollTop: 0,
				old: {
					scrollTop: 0
				},
				scrollTopShow: false,
				jumpPage: '',
				maxPage: 1,
				categoryInfo: {
					seo_title: '',
					seo_keywords: '',
					seo_description: ''
				}
			};
		},
		onLoad: function(options) {
			// 恢复用户视图偏好
			const savedMode = uni.getStorageSync('GOODS_LIST_VIEW_MODE');
			if (savedMode !== '' && savedMode !== undefined) {
				this.viewMode = savedMode;
			}
			this.where.cid = options.cid || 0;
			this.where.coupon_category_id = options.coupon_category_id || '';
			this.$set(this.where, 'sid', options.sid || 0);
			this.title = options.title || '';
			this.$set(this.where, 'keyword', options.searchValue || '');
			this.$set(this.where, 'productId', options.productId || '');
			// 获取分类SEO信息
			const categoryId = options.sid || options.cid;
			if (categoryId) {
				this.getCategoryInfo(categoryId);
			}
			this.get_product_list();
		},
		methods: {
			scroll(e) {
				this.scrollTopShow = e.detail.scrollTop > 150
				this.old.scrollTop = e.detail.scrollTop
			},
			goTop(e) {
				// 解决view层不同步的问题
				this.scrollTop = this.old.scrollTop
				this.$nextTick(() => {
					this.scrollTop = 0
				});
			},
			// 去详情页
			godDetail(item) {
				goShopDetail(item, this.uid).then(res => {
					uni.navigateTo({
						url: `/pages/goods_details/index?id=${item.id}`
					})
				})
			},
			changeViewMode: function(mode) {
				if (this.viewMode === mode) return;
				this.viewMode = mode;
				uni.setStorageSync('GOODS_LIST_VIEW_MODE', mode);
			},
			searchSubmit: function(e) {
				let that = this;
				that.$set(that.where, 'keyword', e.detail.value);
				that.loadend = false;
				that.$set(that.where, 'page', 1)
				this.get_product_list(true);
			},
			/**
			 * 获取我的推荐
			 */
			get_host_product: function() {
				let that = this;
				if (that.hotScroll) return
				getProductHot(
					that.hotPage,
					that.hotLimit,
				).then(res => {
					that.hotPage++
					that.hotScroll = res.data.length < that.hotLimit
					that.hostProduct = that.hostProduct.concat(res.data)
					// that.$set(that, 'hostProduct', res.data)
				});
			},
			//点击事件处理
			set_where: function(e) {
				switch (e) {
					case 1:
						// #ifdef H5
						return history.back();
						// #endif
						// #ifndef H5
						return uni.navigateBack({
							delta: 1,
						})
						// #endif
						break;
					case 2:
						if (this.price == 0) this.price = 1;
						else if (this.price == 1) this.price = 2;
						else if (this.price == 2) this.price = 0;
						this.stock = 0;
						break;
					case 3:
						if (this.stock == 0) this.stock = 1;
						else if (this.stock == 1) this.stock = 2;
						else if (this.stock == 2) this.stock = 0;
						this.price = 0
						break;
					case 4:
						this.nows = !this.nows;
						break;
				}
				this.loadend = false;
				this.$set(this.where, 'page', 1);
				this.get_product_list(true);
			},
			//设置where条件
			setWhere: function() {
				if (this.price == 0) this.where.priceOrder = '';
				else if (this.price == 1) this.where.priceOrder = 'asc';
				else if (this.price == 2) this.where.priceOrder = 'desc';
				if (this.stock == 0) this.where.salesOrder = '';
				else if (this.stock == 1) this.where.salesOrder = 'asc';
				else if (this.stock == 2) this.where.salesOrder = 'desc';
				this.where.news = this.nows ? 1 : 0;
			},
			//查找产品
		get_product_list: function(isPage, needScrollTop) {
			let that = this;
			that.setWhere();
			if (that.loading) return;
			that.loading = true;
			getProductslist(that.where).then(res => {
				let list = res.data;
				let loadend = list.length < that.where.limit;
				that.loadend = loadend;
				that.loading = false;
				that.$set(that, 'productList', list);

				// 计算最大页数
				if (list.length > 0) {
					// 如果返回的数据长度等于limit，说明可能还有更多页
					if (list.length === that.where.limit) {
						// 至少还有一页，所以最大页数至少是当前页+1
						that.maxPage = that.where.page + 1;
					} else {
						// 返回的数据长度小于limit，说明这是最后一页
						that.maxPage = that.where.page;
					}
				} else {
					// 如果没有数据，设置为当前页
					that.maxPage = that.where.page;
				}

				if (!that.productList.length) this.get_host_product();
				// 数据加载完成后再滚动到顶部
				if (needScrollTop) {
					that.scrollToTop();
				}
			}).catch(err => {
				that.loading = false;
			});
		},
	//上一页
	prevPage: function() {
		if (this.where.page <= 1) return;
		this.where.page--;
		this.get_product_list(false, true);
	},
	//下一页
	nextPage: function() {
		if (this.loadend) return;
		this.where.page++;
		this.get_product_list(false, true);
	},
	//页码跳转（通过输入框）
	goToPage: function() {
		let page = parseInt(this.jumpPage);
		if (!page || page < 1) {
			uni.showToast({
				title: this.$t(`请输入有效的页码`),
				icon: 'none'
			});
			return;
		}
		if (page > this.maxPage && this.loadend) {
			uni.showToast({
				title: this.$t(`页码超出范围，最多${this.maxPage}页`),
				icon: 'none'
			});
			return;
		}
		this.where.page = page;
		this.jumpPage = '';
		this.get_product_list(false, true);
	},
	//点击页码按钮跳转
	goToPageNumber: function(page) {
		if (page === this.where.page) return;
		this.where.page = page;
		this.get_product_list(false, true);
	},
	//跳转到首页
	goToFirstPage: function() {
		if (this.where.page <= 1) return;
		this.where.page = 1;
		this.get_product_list(false, true);
	},
	//跳转到末页
	goToLastPage: function() {
		if (this.loadend || this.where.page >= this.maxPage) return;
		this.where.page = this.maxPage;
		this.get_product_list(false, true);
	},
	//滚动到顶部
	scrollToTop: function() {
		// 在uni-app的scroll-view中，需要改变scrollTop值才能触发滚动
		// 使用时间戳确保值发生变化
		this.scrollTop = Date.now();
		this.$nextTick(() => {
			this.scrollTop = 0;
			this.old.scrollTop = 0;
		});
	},
	// 获取分类SEO信息
	getCategoryInfo: function(categoryId) {
		if (!categoryId) return;

		getCategoryList().then(res => {
			const categories = res.data || [];
			// 查找当前分类
			const category = this.findCategoryById(categories, categoryId);
			if (category) {
				this.categoryInfo = {
					seo_title: category.seo_title || '',
					seo_keywords: category.seo_keywords || '',
					seo_description: category.seo_description || ''
				};
			}
		}).catch(err => {
			console.log('获取分类SEO信息失败', err);
		});
	},
	// 递归查找分类
	findCategoryById: function(categories, id) {
		for (let cat of categories) {
			if (cat.id == id) return cat;
			if (cat.children && cat.children.length > 0) {
				const found = this.findCategoryById(cat.children, id);
				if (found) return found;
			}
		}
		return null;
	}
	},
	onPullDownRefresh() {},
	onReachBottom() {},
	// 滚动监听
	onPageScroll(e) {
		// 传入scrollTop值并触发所有easy-loadimage组件下的滚动监听事件
		uni.$emit('scroll');
	}
}
</script>

<style scoped lang="scss">
	.seo-header {
		background: #fff;
		padding: 20rpx 24rpx;
		margin: 0;
		border-bottom: 1rpx solid #f0f0f0;
	}

	.seo-title {
		font-size: 28rpx;
		font-weight: 500;
		color: #333;
		line-height: 1.6;
	}

	.seo-description {
		margin-top: 6rpx;
		font-size: 24rpx;
		color: #888;
		line-height: 1.8;
	}


	.scroll-Y {
		margin-top: 172rpx;
		height: calc(100vh - 172rpx);
		padding-top: 0;
	}

	.wrapper {
		position: relative;
		max-height: 100vh;
		overflow: hidden;

		.back-top {
			position: absolute;
			right: 40rpx;
			bottom: 60rpx;
			width: 60rpx;
			height: 60rpx;
			border-radius: 50%;
			display: flex;
			justify-content: center;
			align-items: center;
			border: 1rpx solid #ccc;
			background-color: #fff;

			.icon-xiangshang {
				color: #ccc;
				font-weight: bold;
			}
		}
	}

	.productList .search {
		width: 100%;
		height: 86rpx;
		padding: 13rpx 23rpx;
		box-sizing: border-box;
		position: fixed;
		left: 0;
		top: 0;
		z-index: 9;
	}

	.productList .search .input {
		width: 500rpx;
		height: 60rpx;
		background-color: #fff;
		border-radius: 50rpx;
		padding: 0 20rpx;
		box-sizing: border-box;
	}

	.productList .search .input input {
		width: 420rpx;
		height: 100%;
		font-size: 26rpx;
	}

	.productList .search .input .placeholder {
		color: #999;
	}

	.productList .search .input .iconfont {
		font-size: 35rpx;
		color: #555;
	}

	.productList .nav {
		height: 86rpx;
		color: #454545;
		position: fixed;
		left: 0;
		width: 100%;
		font-size: 28rpx;
		background-color: #fff;
		margin-top: 86rpx;
		top: 0;
		z-index: 9;
	}

	.productList .nav .item {
		width: 25%;
		text-align: center;
	}

	.productList .nav .item.font-color {
		font-weight: bold;
	}

	.productList .nav .item image {
		width: 15rpx;
		height: 19rpx;
		margin-left: 10rpx;
	}

	.productList .list {
		padding: 0 20rpx 30rpx 20rpx;
		margin-top: 0;
	}

	.productList .list.on {
		background-color: #fff;
		border-top: 1px solid #f6f6f6;
	}

	.productList .list .item {
		width: 345rpx;
		margin-top: 20rpx;
		background-color: #fff;
		border-radius: 20rpx;
	}

	.productList .list .item.on {
		width: 100%;
		display: flex;
		border-bottom: 1rpx solid #f6f6f6;
		padding: 30rpx 0;
		margin: 0;
	}

	.productList .list .item .pictrue {
		position: relative;
		width: 100%;
		height: 345rpx;

	}

	.productList .list .item .name {
		line-height: 42rpx;
		height: 84rpx;
	}

	.productList .list .item .pictrue.on {
		width: 180rpx;
		height: 180rpx;
	}

	.productList .list .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 20rpx 20rpx 0 0;
	}

	.productList .list .item .pictrue image.on {
		border-radius: 6rpx;
	}

	.productList .list .item .text {
		padding: 20rpx 17rpx 26rpx 17rpx;
		font-size: 30rpx;
		color: #222;
	}

	.productList .list .item .text.on {
		width: 508rpx;
		padding: 0 0 0 22rpx;
	}

	.productList .list .item .text .money {
		font-size: 26rpx;
		font-weight: bold;
		margin-top: 8rpx;
	}

	.productList .list .item .text .money.on {
		margin-top: 50rpx;
	}

	.productList .list .item .text .money .num {
		font-size: 34rpx;
	}

	.productList .list .item .text .vip {
		font-size: 22rpx;
		color: #aaa;
		margin-top: 7rpx;
	}

	.productList .list .item .text .vip.on {
		margin-top: 12rpx;
	}

	.productList .list .item .text .vip .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
		display: flex;
		align-items: center;
	}

	.productList .list .item .text .vip .vip-money image {
		width: 64rpx;
		height: 26rpx;
		margin-left: 4rpx;
	}

	.noCommodity {
		background-color: #fff;
		padding-bottom: 30rpx;

		.emptyBox {
			text-align: center;
			padding-top: 20rpx;

			.tips {
				color: #aaa;
				font-size: 26rpx;
			}

			image {
				width: 414rpx;
				height: 304rpx;
			}
		}
	}

	/* 视图切换按钮样式 */
	.productList .search .view-switch {
		display: flex;
		align-items: center;
		height: 60rpx;
	}
	.productList .search .view-switch .switch-btn {
		padding: 0 16rpx;
		height: 60rpx;
		line-height: 60rpx;
		color: #fff;
		opacity: 0.6;
	}
	.productList .search .view-switch .switch-btn.active {
		opacity: 1;
	}
	.productList .search .view-switch .switch-btn .iconfont {
		font-size: 36rpx;
	}

	/* 列表视图样式 - 单列，图左文右 */
	.list-view {
		background-color: #fff;
		margin-top: 86rpx;
	}
	.list-view .item {
		display: flex;
		padding: 20rpx;
		border-bottom: 1rpx solid #f5f5f5;
	}
	.list-view .item .pictrue {
		position: relative;
		width: 220rpx;
		height: 220rpx;
		flex-shrink: 0;
	}
	.list-view .item .pictrue image {
		width: 100%;
		height: 100%;
		border-radius: 12rpx;
	}
	.list-view .item .pictrue .pictrue_log {
		position: absolute;
		top: 0;
		left: 0;
		background: linear-gradient(90deg, #FF6034 0%, #EE0A24 100%);
		color: #fff;
		font-size: 20rpx;
		padding: 4rpx 12rpx;
		border-radius: 12rpx 0 12rpx 0;
	}
	.list-view .item .text {
		flex: 1;
		padding-left: 20rpx;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}
	.list-view .item .text .name {
		font-size: 28rpx;
		color: #222;
		line-height: 40rpx;
	}
	.list-view .item .text .money {
		font-size: 26rpx;
		font-weight: bold;
		margin-top: 16rpx;
	}
	.list-view .item .text .money .num {
		font-size: 34rpx;
	}
	.list-view .item .text .vip {
		font-size: 22rpx;
		color: #aaa;
		margin-top: 8rpx;
	}
	.list-view .item .text .vip .vip-money {
		font-size: 24rpx;
		color: #282828;
		font-weight: bold;
		display: flex;
		align-items: center;
	}
	.list-view .item .text .vip .vip-money image {
		width: 64rpx;
		height: 26rpx;
		margin-left: 4rpx;
	}

	/* 大图视图样式 - 单列大图 */
	/* 使用 .productList .list.large-view 提高选择器优先级，覆盖 .productList .list .item 的 width: 345rpx */
	.productList .list.large-view {
		padding: 0 20rpx;
		margin-top: 86rpx;
	}
	.productList .list.large-view .item {
		width: 100%;
		background: #fff;
		border-radius: 16rpx;
		margin-bottom: 20rpx;
		overflow: hidden;
	}
	.productList .list.large-view .item .pictrue {
		position: relative;
		width: 100%;
		height: auto;
	}
	.productList .list.large-view .item .pictrue image {
		width: 100%;
		height: auto;
		display: block;
		border-radius: 0;
	}
	.productList .list.large-view .item .pictrue .stock-tag {
		position: absolute;
		top: 20rpx;
		left: 20rpx;
		background: rgba(0,0,0,0.6);
		color: #fff;
		padding: 8rpx 16rpx;
		font-size: 22rpx;
		border-radius: 6rpx;
	}
	.productList .list.large-view .item .pictrue .pictrue_log {
		position: absolute;
		top: 20rpx;
		right: 20rpx;
		background: linear-gradient(90deg, #FF6034 0%, #EE0A24 100%);
		color: #fff;
		font-size: 22rpx;
		padding: 6rpx 16rpx;
		border-radius: 6rpx;
	}
	.productList .list.large-view .item .text {
		padding: 24rpx;
	}
	.productList .list.large-view .item .text .name {
		font-size: 30rpx;
		color: #222;
		font-weight: 500;
		line-height: 44rpx;
		height: auto;
	}
	.productList .list.large-view .item .text .rating {
		margin: 16rpx 0;
	}
	.productList .list.large-view .item .text .rating .stars {
		color: #FFD700;
		font-size: 28rpx;
		font-weight: bold;
	}
	.productList .list.large-view .item .text .rating .review-label {
		font-size: 24rpx;
		color: #666;
		margin-left: 10rpx;
	}
	.productList .list.large-view .item .text .price-info {
		margin-top: 12rpx;
	}
	.productList .list.large-view .item .text .price-range {
		font-size: 32rpx;
		font-weight: bold;
	}
	.productList .list.large-view .item .text .sales {
		font-size: 24rpx;
		color: #999;
	}

	/* 双列视图样式 - 双列网格 */
	.grid-view {
		padding: 0 20rpx 30rpx 20rpx;
		margin-top: 86rpx;
		flex-wrap: wrap;
	}
	.grid-view .item {
		width: 345rpx;
		margin-top: 20rpx;
		background-color: #fff;
		border-radius: 20rpx;
		overflow: hidden;
	}
	.grid-view .item .pictrue {
		position: relative;
		width: 100%;
		height: 345rpx;
	}
	.grid-view .item .pictrue image {
		width: 100%;
		height: 100%;
	}
	.grid-view .item .pictrue .pictrue_log_small {
		position: absolute;
		top: 0;
		left: 0;
		background: linear-gradient(90deg, #FF6034 0%, #EE0A24 100%);
		color: #fff;
		font-size: 18rpx;
		padding: 4rpx 10rpx;
		border-radius: 20rpx 0 10rpx 0;
	}
	.grid-view .item .text {
		padding: 16rpx;
	}
	.grid-view .item .text .name {
		font-size: 26rpx;
		color: #222;
		line-height: 38rpx;
		height: 76rpx;
	}
	.grid-view .item .text .money {
		font-size: 26rpx;
		font-weight: bold;
		margin-top: 8rpx;
	}
	.grid-view .item .text .money .num {
		font-size: 32rpx;
	}

	/* 加载更多样式 */
	.loadingicon {
		height: 60rpx;
		font-size: 24rpx;
		color: #999;
		padding: 20rpx 0;
	}

	/* 分页样式 */
	.pagination-top {
		display: flex;
		align-items: center;
		justify-content: space-around;
		padding: 20rpx;
		background: #fff;
		border-bottom: 1rpx solid #f0f0f0;
		margin-bottom: 10rpx;
	}

	.pagination-bottom {
		display: flex;
		flex-direction: column;
		background: #fff;
		border-top: 1rpx solid #f0f0f0;
		margin-top: 20rpx;
		padding: 20rpx 0;
	}

	.pagination-row {
		display: flex;
		align-items: center;
		justify-content: center;
		width: 100%;
	}

	.pagination-row-top {
		margin-bottom: 20rpx;
	}

	.pagination-row-bottom {
		padding: 0 20rpx;
	}

	.pagination-nav {
		padding: 10rpx 20rpx;
		border: none;
		border-radius: 8rpx;
		font-size: 24rpx;
		color: #fff;
		background: linear-gradient(135deg, #e93323 0%, #d42a1a 100%);
		cursor: pointer;
		transition: all 0.3s;
		box-shadow: 0 2rpx 8rpx rgba(233, 51, 35, 0.2);
		min-width: 60rpx;
		text-align: center;
	}

	.pagination-nav:active {
		background: linear-gradient(135deg, #d42a1a 0%, #c02110 100%);
		transform: scale(0.98);
		box-shadow: 0 1rpx 4rpx rgba(233, 51, 35, 0.3);
	}

	.pagination-nav.disabled {
		color: #999;
		background: #f5f5f5;
		cursor: not-allowed;
		opacity: 0.6;
		box-shadow: none;
	}

	.pagination-nav.disabled:active {
		transform: none;
		background: #f5f5f5;
	}

	.page-numbers {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 8rpx;
		flex: 1;
		margin: 0 20rpx;
	}

	.page-number {
		min-width: 56rpx;
		height: 56rpx;
		display: flex;
		align-items: center;
		justify-content: center;
		border-radius: 8rpx;
		font-size: 26rpx;
		color: #333;
		background: #f5f5f5;
		cursor: pointer;
		transition: all 0.3s;
		border: 1rpx solid #e0e0e0;
	}

	.page-number:active {
		transform: scale(0.95);
	}

	.page-number.active {
		color: #fff;
		background: linear-gradient(135deg, #e93323 0%, #d42a1a 100%);
		border-color: #e93323;
		box-shadow: 0 2rpx 8rpx rgba(233, 51, 35, 0.2);
		font-weight: 500;
	}

	.page-info {
		font-size: 28rpx;
		color: #333;
		min-width: 150rpx;
		text-align: center;
		font-weight: 500;
	}

	.page-jump {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 12rpx;
		width: 100%;
	}

	.jump-input {
		width: 100rpx;
		height: 48rpx;
		border: 1rpx solid #e0e0e0;
		border-radius: 8rpx;
		padding: 0 12rpx;
		font-size: 26rpx;
		text-align: center;
		background: #fff;
		box-sizing: border-box;
	}

	.jump-input:focus {
		border-color: #e93323;
		outline: none;
	}

	.jump-button {
		padding: 12rpx 24rpx;
		background: linear-gradient(135deg, #e93323 0%, #d42a1a 100%);
		border: none;
		border-radius: 8rpx;
		font-size: 26rpx;
		color: #fff;
		cursor: pointer;
		transition: all 0.3s;
		box-shadow: 0 2rpx 8rpx rgba(233, 51, 35, 0.2);
	}

	.jump-button:active {
		background: linear-gradient(135deg, #d42a1a 0%, #c02110 100%);
		transform: scale(0.98);
		box-shadow: 0 1rpx 4rpx rgba(233, 51, 35, 0.3);
	}
</style>
