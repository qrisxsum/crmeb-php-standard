<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------
declare (strict_types=1);

namespace app\services\order;

use app\services\BaseServices;

/**
 * 税费计算服务
 * Class TaxCalculationServices
 * @package app\services\order
 */
class TaxCalculationServices extends BaseServices
{
    /**
     * 获取系统税率配置（返回小数形式，如0.10表示10%）
     * @return float 税率（小数形式）
     */
    public function getTaxRate(): float
    {
        $ratePercent = (float)sys_config('tax_rate_global', 10);
        return $ratePercent / 100;
    }

    /**
     * 检查税费功能是否开启
     * @return bool
     */
    public function isTaxEnabled(): bool
    {
        return (bool)sys_config('tax_enable', 1);
    }

    /**
     * 获取税费展示方式（1=内税，2=外税）
     * @return int
     */
    public function getTaxDisplayMode(): int
    {
        return (int)sys_config('tax_display_mode', 1);
    }

    /**
     * 内税反向计算 - 从含税价计算税额
     *
     * 公式说明：
     * - 含税价 = 不含税价 × (1 + 税率)
     * - 不含税价 = 含税价 / (1 + 税率)
     * - 税额 = 含税价 × 税率 / (1 + 税率)
     *
     * 示例：含税价3999元，税率10%
     * - 不含税价 = 3999 / 1.10 = 3635.45元
     * - 税额 = 3999 × 0.10 / 1.10 = 363.55元
     * - 验证：3635.45 + 363.55 = 3999 ✓
     *
     * @param float $includedTaxPrice 含税价格
     * @return array ['tax_amount' => 税额, 'price_ex_tax' => 不含税价格, 'tax_rate_used' => 使用的税率]
     */
    public function calculateTaxFromIncludedPrice(float $includedTaxPrice): array
    {
        // 税费功能未启用或价格无效时，返回零税额
        if (!$this->isTaxEnabled() || $includedTaxPrice <= 0) {
            return [
                'tax_amount' => 0.00,
                'price_ex_tax' => $includedTaxPrice,
                'tax_rate_used' => 0
            ];
        }

        $taxRate = $this->getTaxRate();

        // 税率无效时，返回零税额
        if ($taxRate <= 0) {
            return [
                'tax_amount' => 0.00,
                'price_ex_tax' => $includedTaxPrice,
                'tax_rate_used' => 0
            ];
        }

        // 使用bcmath进行高精度计算，避免浮点数误差
        // 计算 (1 + 税率)
        $divisor = bcadd('1', (string)$taxRate, 6);

        // 计算税额 = 含税价 × 税率 / (1 + 税率)
        $taxAmount = bcdiv(
            bcmul((string)$includedTaxPrice, (string)$taxRate, 6),
            $divisor,
            2  // 保留2位小数
        );

        // 计算不含税价 = 含税价 - 税额
        $priceExTax = bcsub((string)$includedTaxPrice, $taxAmount, 2);

        return [
            'tax_amount' => (float)$taxAmount,        // 税额
            'price_ex_tax' => (float)$priceExTax,     // 不含税价格
            'tax_rate_used' => $taxRate * 100         // 使用的税率（百分比形式）
        ];
    }

    /**
     * 外税计算 - 从不含税价计算税额（备用方法，当前版本未使用）
     *
     * 公式：税额 = 不含税价 × 税率
     *
     * @param float $priceExTax 不含税价格
     * @return array
     */
    public function calculateTaxFromExcludedPrice(float $priceExTax): array
    {
        if (!$this->isTaxEnabled() || $priceExTax <= 0) {
            return [
                'tax_amount' => 0.00,
                'price_inc_tax' => $priceExTax,
                'tax_rate_used' => 0
            ];
        }

        $taxRate = $this->getTaxRate();

        if ($taxRate <= 0) {
            return [
                'tax_amount' => 0.00,
                'price_inc_tax' => $priceExTax,
                'tax_rate_used' => 0
            ];
        }

        // 税额 = 不含税价 × 税率
        $taxAmount = bcmul((string)$priceExTax, (string)$taxRate, 2);

        // 含税价 = 不含税价 + 税额
        $priceIncTax = bcadd((string)$priceExTax, $taxAmount, 2);

        return [
            'tax_amount' => (float)$taxAmount,
            'price_inc_tax' => (float)$priceIncTax,
            'tax_rate_used' => $taxRate * 100
        ];
    }

    /**
     * 计算购物车单个商品的税额信息
     *
     * @param array $cartItem 购物车商品项，必须包含以下字段：
     *                        - truePrice: 商品真实价格（含税价）
     *                        - cart_num: 商品数量
     * @return array 扩展了税额字段的商品信息，新增字段：
     *               - tax_unit_price: 单价税额
     *               - tax_item_total: 商品税额小计（单价税额 × 数量）
     *               - price_ex_tax: 不含税单价
     *               - tax_rate_used: 使用的税率
     */
    public function calculateCartItemTax(array $cartItem): array
    {
        // 获取商品价格和数量
        $price = (float)($cartItem['truePrice'] ?? 0);
        $quantity = (int)($cartItem['cart_num'] ?? 1);

        // 计算单价税额
        $taxInfo = $this->calculateTaxFromIncludedPrice($price);

        // 计算商品税额小计 = 单价税额 × 数量
        $itemTaxTotal = bcmul((string)$taxInfo['tax_amount'], (string)$quantity, 2);

        // 扩展原商品数组，添加税额相关字段
        $cartItem['tax_unit_price'] = $taxInfo['tax_amount'];      // 单价税额
        $cartItem['tax_item_total'] = (float)$itemTaxTotal;        // 商品税额小计
        $cartItem['price_ex_tax'] = $taxInfo['price_ex_tax'];      // 不含税单价
        $cartItem['tax_rate_used'] = $taxInfo['tax_rate_used'];    // 使用的税率

        return $cartItem;
    }

    /**
     * 计算购物车总税额
     *
     * @param array $cartList 购物车商品列表
     * @return array 返回包含税额汇总信息的数组：
     *               - tax_total: 总税额
     *               - tax_rate: 税率（百分比）
     *               - tax_enabled: 是否启用税费
     *               - items: 处理后的商品列表（每个商品都包含税额信息）
     */
    public function calculateCartTaxTotal(array $cartList): array
    {
        $taxTotal = '0.00';
        $processedItems = [];

        // 遍历所有商品，计算每个商品的税额并累加
        foreach ($cartList as $item) {
            $itemWithTax = $this->calculateCartItemTax($item);
            $taxTotal = bcadd($taxTotal, (string)$itemWithTax['tax_item_total'], 2);
            $processedItems[] = $itemWithTax;
        }

        return [
            'tax_total' => (float)$taxTotal,                 // 总税额
            'tax_rate' => $this->getTaxRate() * 100,         // 税率（百分比）
            'tax_enabled' => $this->isTaxEnabled(),          // 是否启用税费
            'items' => $processedItems                       // 处理后的商品列表
        ];
    }

    /**
     * 计算订单税额信息
     * 用于订单创建时记录税额信息
     *
     * @param array $cartInfo 购物车信息数组
     * @param float $totalPrice 订单总价
     * @return array 订单税额信息：
     *               - order_tax_total: 订单总税额
     *               - order_tax_rate: 订单税率
     */
    public function calculateOrderTax(array $cartInfo, float $totalPrice): array
    {
        if (!$this->isTaxEnabled()) {
            return [
                'order_tax_total' => 0.00,
                'order_tax_rate' => 0.00
            ];
        }

        // 计算订单总税额
        $taxInfo = $this->calculateTaxFromIncludedPrice($totalPrice);

        return [
            'order_tax_total' => $taxInfo['tax_amount'],
            'order_tax_rate' => $taxInfo['tax_rate_used']
        ];
    }

    /**
     * 格式化税额显示（用于前端展示）
     *
     * @param float $taxAmount 税额
     * @param string $currency 货币符号，默认为'¥'
     * @return string 格式化后的税额字符串
     */
    public function formatTaxAmount(float $taxAmount, string $currency = '¥'): string
    {
        return $currency . number_format($taxAmount, 2);
    }

    /**
     * 验证税费计算准确性（开发调试用）
     * 验证：不含税价 + 税额 = 含税价
     *
     * @param float $includedTaxPrice 含税价
     * @return array 验证结果
     */
    public function validateTaxCalculation(float $includedTaxPrice): array
    {
        $taxInfo = $this->calculateTaxFromIncludedPrice($includedTaxPrice);

        $calculatedTotal = bcadd(
            (string)$taxInfo['price_ex_tax'],
            (string)$taxInfo['tax_amount'],
            2
        );

        $isValid = bccomp((string)$includedTaxPrice, $calculatedTotal, 2) === 0;

        return [
            'is_valid' => $isValid,
            'included_tax_price' => $includedTaxPrice,
            'price_ex_tax' => $taxInfo['price_ex_tax'],
            'tax_amount' => $taxInfo['tax_amount'],
            'calculated_total' => (float)$calculatedTotal,
            'difference' => bcsub((string)$includedTaxPrice, $calculatedTotal, 4)
        ];
    }
}
