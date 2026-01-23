<template>
    <view class="page-bottom" v-if="showPageBottom">
        <!-- 公告区域 -->
        <view class="notice-section" v-if="config.notice && config.notice.enabled && config.notice.list && config.notice.list.length">
            <view class="notice-header">
                <text class="notice-header-title">お知らせ</text>
            </view>
            <view class="notice-list">
                <view class="notice-item" v-for="(item, index) in config.notice.list" :key="index" @click="goNoticeDetail(item)">
                    <view class="notice-item-header">
                        <text class="notice-item-title">{{ item.title }}</text>
                        <text class="notice-item-date" v-if="item.date">{{ item.date }}</text>
                    </view>
                    <view class="notice-item-content" v-if="item.content">{{ item.content }}</view>
                    <view class="notice-item-more" v-if="item.link">
                        <text>もっと見る</text>
                        <text class="arrow">></text>
                    </view>
                </view>
            </view>
        </view>

        <!-- 导航链接列表 -->
        <view class="nav-links-section" v-if="config.navLinks && config.navLinks.enabled && config.navLinks.list && config.navLinks.list.length">
            <view class="nav-links-list">
                <view class="nav-link-item" v-for="(item, index) in config.navLinks.list" :key="index" @click="goPage(item)">
                    <view class="nav-link-left">
                        <image v-if="getIconUrl(item.icon)" :src="getIconUrl(item.icon)" class="nav-link-icon" mode="aspectFit"></image>
                        <text class="nav-link-name">{{ item.name }}</text>
                    </view>
                    <text class="nav-link-arrow">></text>
                </view>
            </view>
        </view>

        <!-- 版权信息 -->
        <view class="copyright-section" v-if="config.copyright && config.copyright.enabled && config.copyright.text">
            <text class="copyright-text">{{ config.copyright.text }}</text>
        </view>

        <!-- 返回顶部按钮 -->
        <view class="back-top-btn" v-if="config.backTop && config.backTop.enabled" @click="scrollToTop">
            <text class="back-top-text">トップに戻る</text>
            <text class="back-top-arrow">↑</text>
        </view>
    </view>
</template>

<script>
import { getPageBottom, getPageBottomVersion } from '@/api/public.js';

export default {
    name: 'pageBottom',
    data() {
        return {
            config: {
                notice: { enabled: false, list: [] },
                navLinks: { enabled: true, list: [] },
                copyright: { enabled: true, text: '' },
                backTop: { enabled: true }
            },
            showPageBottom: false,
            showBackTop: false,
            scrollTop: 0,
            iconMap: {
                'home': '/static/images/pageBottom/home.png',
                'category': '/static/images/pageBottom/category.png',
                'cart': '/static/images/pageBottom/cart.png',
                'user': '/static/images/pageBottom/user.png',
                'guide': '/static/images/pageBottom/guide.png',
                'contact': '/static/images/pageBottom/contact.png',
                'law': '/static/images/pageBottom/law.png'
            }
        };
    },
    mounted() {
        this.loadConfig();
    },
    methods: {
        async loadConfig() {
            const cachedConfig = uni.getStorageSync('pageBottomConfig');
            if (cachedConfig) {
                try {
                    const versionRes = await getPageBottomVersion();
                    const cachedVersion = uni.getStorageSync('pageBottomVersion');
                    if (versionRes.data && versionRes.data.version === cachedVersion) {
                        this.config = JSON.parse(cachedConfig);
                        this.showPageBottom = true;
                        return;
                    }
                } catch (e) {
                    console.error('获取版本号失败', e);
                }
            }
            this.fetchConfig();
        },
        async fetchConfig() {
            try {
                const res = await getPageBottom();
                if (res.data) {
                    this.config = res.data;
                    this.showPageBottom = true;
                    uni.setStorageSync('pageBottomConfig', JSON.stringify(res.data));

                    // 同时获取并缓存版本号
                    try {
                        const versionRes = await getPageBottomVersion();
                        if (versionRes.data && versionRes.data.version) {
                            uni.setStorageSync('pageBottomVersion', versionRes.data.version);
                        }
                    } catch (e) {
                        console.error('获取版本号失败', e);
                    }
                }
            } catch (e) {
                console.error('获取底部配置失败', e);
                this.showPageBottom = false;
            }
        },
        getIconUrl(icon) {
            if (!icon) return '';
            // 如果是完整URL，直接返回
            if (icon.startsWith('http://') || icon.startsWith('https://')) {
                return icon;
            }
            // 否则从iconMap中获取
            return this.iconMap[icon] || '';
        },
        handleScroll(scrollTop) {
            this.scrollTop = scrollTop;
            this.showBackTop = scrollTop > 300;
        },
        scrollToTop() {
            uni.pageScrollTo({
                scrollTop: 0,
                duration: 300
            });
        },
        goPage(item) {
            if (!item.link) return;
            // 先尝试switchTab（用于tabBar页面）
            uni.switchTab({
                url: item.link,
                fail: () => {
                    // 失败则尝试navigateTo
                    uni.navigateTo({
                        url: item.link,
                        fail: () => {
                            // 最后尝试redirectTo
                            uni.redirectTo({
                                url: item.link
                            });
                        }
                    });
                }
            });
        },
        goNoticeDetail(item) {
            if (item.link) {
                uni.navigateTo({
                    url: item.link,
                    fail: () => {
                        uni.redirectTo({ url: item.link });
                    }
                });
            }
        }
    }
};
</script>

<style lang="scss" scoped>
.page-bottom {
    background-color: #f5f5f5;
    padding: 20rpx 24rpx 40rpx;

    .notice-section {
        background: #fff;
        border-radius: 16rpx;
        padding: 24rpx;
        margin-bottom: 20rpx;

        .notice-header {
            margin-bottom: 20rpx;
            padding-bottom: 16rpx;
            border-bottom: 1rpx solid #eee;

            .notice-header-title {
                font-size: 30rpx;
                font-weight: bold;
                color: #333;
            }
        }

        .notice-list {
            .notice-item {
                padding: 16rpx 0;
                border-bottom: 1rpx solid #f5f5f5;

                &:last-child {
                    border-bottom: none;
                }

                .notice-item-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-bottom: 10rpx;

                    .notice-item-title {
                        font-size: 28rpx;
                        color: #333;
                        font-weight: 500;
                        flex: 1;
                    }

                    .notice-item-date {
                        font-size: 22rpx;
                        color: #999;
                        margin-left: 16rpx;
                    }
                }

                .notice-item-content {
                    font-size: 24rpx;
                    color: #666;
                    line-height: 1.6;
                    margin-bottom: 10rpx;
                    display: -webkit-box;
                    -webkit-box-orient: vertical;
                    -webkit-line-clamp: 2;
                    overflow: hidden;
                }

                .notice-item-more {
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    font-size: 24rpx;
                    color: var(--view-theme, #e93323);

                    .arrow {
                        margin-left: 8rpx;
                    }
                }
            }
        }
    }

    .nav-links-section {
        background: #fff;
        border-radius: 16rpx;
        margin-bottom: 20rpx;
        overflow: hidden;

        .nav-links-list {
            .nav-link-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 28rpx 24rpx;
                border-bottom: 1rpx solid #f5f5f5;

                &:last-child {
                    border-bottom: none;
                }

                &:active {
                    background-color: #f9f9f9;
                }

                .nav-link-left {
                    display: flex;
                    align-items: center;

                    .nav-link-icon {
                        width: 40rpx;
                        height: 40rpx;
                        margin-right: 20rpx;
                    }

                    .nav-link-name {
                        font-size: 28rpx;
                        color: #333;
                    }
                }

                .nav-link-arrow {
                    font-size: 28rpx;
                    color: #ccc;
                }
            }
        }
    }

    .copyright-section {
        text-align: center;
        padding: 30rpx 20rpx;

        .copyright-text {
            font-size: 22rpx;
            color: #999;
            line-height: 1.8;
        }
    }

    .back-top-btn {
        position: fixed;
        right: 30rpx;
        bottom: 200rpx;
        background: #fff;
        border-radius: 8rpx;
        padding: 16rpx 24rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
        z-index: 999;

        .back-top-text {
            font-size: 20rpx;
            color: #666;
            white-space: nowrap;
        }

        .back-top-arrow {
            font-size: 28rpx;
            color: var(--view-theme, #e93323);
            margin-top: 4rpx;
        }
    }
}
</style>
