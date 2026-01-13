<template>
	<view class="spec-selector" v-if="attr.productAttr && attr.productAttr.length" :style="colorStyle">
		<!-- 规格列表 -->
		<view class="spec-group" v-for="(item, indexw) in attr.productAttr" :key="indexw">
			<view class="spec-title">{{ $t(item.attr_name) }}</view>
			<view class="spec-list acea-row row-middle">
				<view
					class="spec-item"
					:class="{ 'on': item.index === itemn.attr, 'disabled': isSpecDisabled(indexw, itemn.attr) }"
					v-for="(itemn, indexn) in item.attr_value"
					:key="indexn"
					@click="tapAttr(indexw, indexn)"
				>
					<image v-if="itemn.pic" class="spec-img" :src="itemn.pic" />
					<text>{{ $t(itemn.attr) }}</text>
				</view>
			</view>
		</view>

		<!-- 数量选择器 -->
		<view class="spec-quantity acea-row row-between-wrapper" v-if="showQuantity">
			<view class="quantity-title">{{ $t(`数量`) }}</view>
			<view class="quantity-box acea-row row-middle">
				<text class="limit-tip" v-if="limitNum">{{ $t(`限购`) }}{{ limitNum }}{{ unitName }}</text>
				<text class="limit-tip" v-if="minQty > 1"> | {{ $t(`起购`) }}{{ minQty }}{{ unitName }}</text>
				<view class="quantity-btn minus" :class="{ 'disabled': attr.productSelect.cart_num <= minQty }" @click="handleCartNumDes">
					<text class="iconfont icon-shangpinshuliang-jian"></text>
				</view>
				<input
					type="number"
					class="quantity-input"
					v-model="attr.productSelect.cart_num"
					@input="handleInput"
				/>
				<view class="quantity-btn plus" :class="{ 'disabled': attr.productSelect.cart_num >= getMaxNum() }" @click="handleCartNumAdd">
					<text class="iconfont icon-shangpinshuliang-jia"></text>
				</view>
			</view>
		</view>

		<!-- 选中规格信息展示 -->
		<view class="spec-selected-info acea-row row-between-wrapper">
			<view class="selected-text">
				{{ $t(`已选`) }}：{{ selectedValue || $t(`请选择规格`) }}
			</view>
			<view class="stock-text">
				{{ $t(`库存`) }}：{{ getStockValue() }}{{ unitName }}
			</view>
		</view>
	</view>
</template>

<script>
import colors from "@/mixins/color";
export default {
	name: 'SpecSelector',
	mixins: [colors],
	props: {
		attr: {
			type: Object,
			required: true
		},
		showQuantity: {
			type: Boolean,
			default: true
		},
		minQty: {
			type: Number,
			default: 1
		},
		limitNum: {
			type: Number,
			default: 0
		},
		unitName: {
			type: String,
			default: ''
		},
		type: {
			type: String,
			default: '' // seckill, combination, presell等活动类型
		}
	},
	computed: {
		selectedValue() {
			if (!this.attr.productAttr || !this.attr.productAttr.length) return '';
			return this.attr.productAttr.map(item => item.index).filter(Boolean).join(',');
		}
	},
	methods: {
		// 规格选择
		tapAttr(indexw, indexn) {
			if (!this.attr.productAttr[indexw] || !this.attr.productAttr[indexw].attr_value) {
				console.warn('规格数据异常:', this.attr.productAttr[indexw]);
				return;
			}
			const selectedValue = this.attr.productAttr[indexw].attr_value[indexn].attr;
			this.$emit('attrVal', { indexw, indexn });
			this.$set(this.attr.productAttr[indexw], 'index', selectedValue);
			this.$nextTick(() => {
				const value = this.getCheckedValue().join(',');
				this.$emit('ChangeAttr', value);
			});
		},

		// 获取已选中的规格值
		getCheckedValue() {
			let productAttr = this.attr.productAttr;
			let value = [];
			if (!productAttr || !Array.isArray(productAttr)) {
				return value;
			}
			for (let i = 0; i < productAttr.length; i++) {
				if (productAttr[i].index && productAttr[i].attr_value && Array.isArray(productAttr[i].attr_value)) {
					let attrValues = productAttr[i].attr_value.map(item => item.attr);
					if (attrValues.includes(productAttr[i].index)) {
						value.push(productAttr[i].index);
					}
				}
			}
			return value;
		},

		// 判断规格是否禁用（库存为0时禁用）
		isSpecDisabled(attrIndex, attrValue) {
			// 可选：根据库存判断是否禁用某个规格选项
			// 此处可根据业务需求实现
			return false;
		},

		// 获取库存值（兼容不同活动类型）
		getStockValue() {
			if (this.type === 'seckill' || this.type === 'combination' || this.type === 'presell') {
				return this.attr.productSelect.quota || 0;
			}
			return this.attr.productSelect.stock || 0;
		},

		// 获取最大购买数量
		getMaxNum() {
			let maxNum = this.getStockValue();
			if (this.limitNum) {
				maxNum = Math.min(maxNum, this.limitNum);
			}
			return maxNum;
		},

		// 数量减少
		handleCartNumDes() {
			if (this.attr.productSelect.cart_num > this.minQty) {
				this.attr.productSelect.cart_num--;
				this.$emit('ChangeCartNum', false);
			}
		},

		// 数量增加
		handleCartNumAdd() {
			let maxNum = this.getMaxNum();
			if (this.attr.productSelect.cart_num < maxNum) {
				this.attr.productSelect.cart_num++;
				this.$emit('ChangeCartNum', true);
			}
		},

		// 数量输入
		handleInput(e) {
			this.$emit('iptCartNum', e.detail.value);
		}
	}
}
</script>

<style scoped lang="scss">
.spec-selector {
	background: #fff;
	margin-top: 20rpx;
	padding: 30rpx;
	border-radius: 10rpx;
}

.spec-group {
	margin-bottom: 30rpx;

	&:last-child {
		margin-bottom: 0;
	}
}

.spec-title {
	font-size: 28rpx;
	color: #666;
	margin-bottom: 20rpx;
	font-weight: 500;
}

.spec-list {
	flex-wrap: wrap;
}

.spec-item {
	display: flex;
	align-items: center;
	padding: 14rpx 30rpx;
	margin: 0 20rpx 20rpx 0;
	background: #f5f5f5;
	border-radius: 8rpx;
	border: 2rpx solid transparent;
	font-size: 26rpx;
	color: #333;
	transition: all 0.3s;

	&.on {
		background: var(--view-minorColorT);
		border-color: var(--view-theme);
		color: var(--view-theme);
	}

	&.disabled {
		background: #f5f5f5;
		color: #ccc;
		text-decoration: line-through;
		pointer-events: none;
	}

	.spec-img {
		width: 40rpx;
		height: 40rpx;
		margin-right: 10rpx;
		border-radius: 4rpx;
	}
}

.spec-quantity {
	padding: 30rpx 0;
	border-top: 1rpx solid #eee;
	margin-top: 20rpx;
	align-items: center;
}

.quantity-title {
	font-size: 28rpx;
	color: #666;
	font-weight: 500;
}

.quantity-box {
	align-items: center;

	.limit-tip {
		font-size: 22rpx;
		color: #999;
		margin-right: 20rpx;
	}
}

.quantity-btn {
	width: 60rpx;
	height: 60rpx;
	display: flex;
	align-items: center;
	justify-content: center;
	background: #f5f5f5;
	border-radius: 8rpx;
	transition: all 0.3s;

	.iconfont {
		font-size: 24rpx;
		color: #333;
	}

	&.disabled {
		background: #eee;

		.iconfont {
			color: #ccc;
		}
	}
}

.quantity-input {
	width: 100rpx;
	height: 60rpx;
	text-align: center;
	font-size: 28rpx;
	margin: 0 10rpx;
	background: #f5f5f5;
	border-radius: 8rpx;
}

.spec-selected-info {
	padding-top: 20rpx;
	border-top: 1rpx solid #eee;
	margin-top: 20rpx;
	font-size: 24rpx;

	.selected-text {
		color: #333;
	}

	.stock-text {
		color: #999;
	}
}
</style>
