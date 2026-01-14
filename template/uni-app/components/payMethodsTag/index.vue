<template>
    <view class="pay-methods-tag">
        <view class="title">お支払い方法</view>
        <view class="icons">
            <view class="icon-item" v-for="(item, idx) in payMethods" :key="idx">
                <image :src="item.icon" mode="heightFix" />
            </view>
        </view>
    </view>
</template>

<script>
export default {
    name: 'payMethodsTag',
    data() {
        return {
            payMethods: [
                { name: 'VISA', icon: '/static/images/pay/visa.png' },
                { name: 'MasterCard', icon: '/static/images/pay/mastercard.png' },
                { name: 'JCB', icon: '/static/images/pay/jcb.png' },
                { name: 'PayPay', icon: '/static/images/pay/paypay.png' },
                { name: '銀行振込', icon: '/static/images/pay/bank.png' },
                { name: 'コンビニ', icon: '/static/images/pay/konbini.png' }
            ]
        }
    }
}
</script>

<style scoped lang="scss">
.pay-methods-tag {
    padding: 20rpx 24rpx;
    background: #f8f8f8;
    border-radius: 12rpx;
    margin: 20rpx 24rpx;
}
.title {
    font-size: 24rpx;
    color: #666;
    margin-bottom: 16rpx;
}
.icons {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
}
.icon-item {
    margin-right: 24rpx;
    margin-bottom: 16rpx;
}
.icon-item image {
    height: 56rpx;
}
</style>
