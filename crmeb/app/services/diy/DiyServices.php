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

namespace app\services\diy;

use app\services\activity\bargain\StoreBargainServices;
use app\services\activity\combination\StoreCombinationServices;
use app\services\activity\seckill\StoreSeckillServices;
use app\services\BaseServices;
use app\dao\diy\DiyDao;
use app\services\other\QrcodeServices;
use app\services\product\product\StoreProductServices;
use app\services\system\config\SystemGroupDataServices;
use app\services\system\config\SystemGroupServices;
use crmeb\exceptions\AdminException;
use crmeb\services\CacheService;
use crmeb\services\FormBuilder as Form;
use crmeb\services\SystemConfigService;
use think\facade\Route as Url;

/**
 *
 * Class DiyServices
 * @package app\services\diy
 */
class DiyServices extends BaseServices
{

    /**
     * DiyServices constructor.
     * @param DiyDao $dao
     */
    public function __construct(DiyDao $dao)
    {
        $this->dao = $dao;
    }

    /**
     * 获取DIY列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiyList(array $where)
    {
        [$page, $limit] = $this->getPageValue();
        $where['is_del'] = 0;
        if ($where['type'] == 2) $limit = 1000;
        $list = $this->dao->getDiyList($where, $page, $limit, ['id', 'name', 'type', 'add_time', 'update_time', 'is_diy', 'status']);
        foreach ($list as &$item) {
            $item['type_name'] = $item['type'] == 0 ? '可视化' : '专题页';
        }
        $count = $this->dao->count($where);
        return compact('list', 'count');
    }

    /**
     * 保存资源
     * @param int $id
     * @param array $data
     */
    public function saveData(int $id = 0, array $data = [])
    {
        if ($id) {
            $data['update_time'] = time();
            $res = $this->dao->update($id, $data);
            if (!$res) throw new AdminException(100007);
        } else {
            $data['add_time'] = time();
            $data['update_time'] = time();
            $res = $this->dao->save($data);
            if (!$res) throw new AdminException(100006);
            $id = $res->id;
        }

        return $id;
    }

    /**
     * 删除DIY模板
     * @param int $id
     */
    public function del(int $id)
    {
        if ($id == 1) throw new AdminException(400457);
        $count = $this->dao->getCount(['id' => $id, 'status' => 1]);
        if ($count) throw new AdminException(400458);
        $res = $this->dao->update($id, ['is_del' => 1]);
        if (!$res) throw new AdminException(100008);

        CacheService::clear();
    }

    /**
     * 设置模板使用
     * @param int $id
     */
    public function setStatus(int $id)
    {
        $this->dao->update(['is_diy' => 1], ['is_show' => 1, 'type' => 2]);
        $this->dao->update([['id', '<>', $id]], ['status' => 0]);
        $this->dao->update($id, ['status' => 1, 'update_time' => time()]);
    }

    /**
     * @param int $id
     * @return array|mixed|\think\Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author 吴汐
     * @email 442384644@qq.com
     * @date 2023/05/08
     */
    public function getDiyVersion(int $id)
    {
        if ($id) {
            $where = ['id' => $id];
        } else {
            $where = ['status' => 1, 'is_del' => 0];
        }
        $data = $this->dao->getOne($where, 'version,is_diy');
        if (isset($data['version']) && isset($data['is_diy'])) {
            return $data;
        } else {
            return $this->dao->getOne($where, 'version,is_diy');
        }
    }

    /**
     * 获取页面数据
     * @param int $id
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getDiy($id = 0)
    {
        $field = 'name,value,is_show,is_bg_color,color_picker,bg_pic,bg_tab_val,is_bg_pic,order_status,is_diy,title';

        if ($id) {
            $info = $this->dao->getOne(['id' => $id], $field);
        } else {
            $info = $this->dao->getOne(['status' => 1, 'is_del' => 0], $field);
        }
        $info = $info ? $info->toArray() : [];

        if ($info) {
            if ($info['value']) {
                $info['value'] = json_decode($info['value'], true);
                if ($info['is_diy']) {
                    foreach ($info['value'] as &$item) {
                        if ($item['name'] == 'customerService') {
                            $item['routine_contact_type'] = sys_config('routine_contact_type', 0);
                        }
                    }
                    return $info;
                } else {
                    foreach ($info['value'] as $key => &$item) {
                        if ($key == 'customerService') {
                            foreach ($item as $k => &$v) {
                                $v['routine_contact_type'] = sys_config('routine_contact_type', 0);
                            }
                        }
                    }
                    return $info['value'];
                }
            }
        }
        return [];
    }

    /**
     * 添加表单
     * @return array
     * @throws \FormBuilder\Exception\FormBuilderException
     */
    public function createForm()
    {
        $field = array();
        $title = '添加模板';
        $field[] = Form::input('name', '页面名称', '')->required();
        return create_form($title, $field, Url::buildUrl('/diy/create'), 'POST');
    }

    /**
     * 获取商品数据
     * @param array $where
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function ProductList(array $where)
    {
        /** @var StoreProductServices $StoreProductServices */
        $StoreProductServices = app()->make(StoreProductServices::class);
        /** @var StoreBargainServices $StoreBargainServices */
        $StoreBargainServices = app()->make(StoreBargainServices::class);
        /** @var  $StoreCombinationServices StoreCombinationServices */
        $StoreCombinationServices = app()->make(StoreCombinationServices::class);
        /** @var  $StoreSeckillServices  StoreSeckillServices */
        $StoreSeckillServices = app()->make(StoreSeckillServices::class);
        $type = $where['type'];
        unset($where['type']);
        $data = [];
        switch ($type) {
            case 0:
                $data = $StoreProductServices->searchList($where);
                break;
            //秒杀
            case 2:
                $data = $StoreSeckillServices->getDiySeckillList($where);
                break;
            //拼团
            case 3:
                $data = $StoreCombinationServices->getDiyCombinationList($where);
                break;
            case 4:
                $where['is_hot'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            case 5:
                $where['is_new'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            case 6:
                $where['is_benefit'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            case 7:
                $where['is_best'] = 1;
                $data = $StoreProductServices->searchList($where);
                break;
            //砍价
            case 8:
                $data = $StoreBargainServices->getDiyBargainList($where);
                break;
        }
        return $data;
    }

    /**
     * 前台获取首页数据接口
     * @param array $where
     */
    public function homeProductList(array $where, int $uid)
    {
        /** @var StoreProductServices $StoreProductServices */
        $StoreProductServices = app()->make(StoreProductServices::class);
        /** @var StoreBargainServices $StoreBargainServices */
        $StoreBargainServices = app()->make(StoreBargainServices::class);
        /** @var  $StoreCombinationServices StoreCombinationServices */
        $StoreCombinationServices = app()->make(StoreCombinationServices::class);
        /** @var  $StoreSeckillServices  StoreSeckillServices */
        $StoreSeckillServices = app()->make(StoreSeckillServices::class);
        $type = $where['type'];
        $data = [];
        switch ($type) {
            case 0:
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            //秒杀
            case 2:
                $data = $StoreSeckillServices->getHomeSeckillList($where);
                break;
            //拼团
            case 3:
                $data = $StoreCombinationServices->getHomeList($where);
                break;
            case 4:
                $where['is_hot'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            case 5:
                $where['is_new'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            case 6:
                $where['is_benefit'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            case 7:
                $where['is_best'] = 1;
                $where['type'] = $where['isType'] ?? 0;
                $data['list'] = $StoreProductServices->getGoodsList($where, $uid);
                break;
            //砍价
            case 8:
                $data = $StoreBargainServices->getHomeList($where);
                break;
        }
        foreach ($data['list'] as &$item) {
            $item['image'] = set_file_url($item['image'], sys_config('site_url'));
        }
        return $data;
    }

    /**
     * 分类、个人中心、一键换色
     * @param string $name
     * @return mixed
     */
    public function getColorChange(string $name)
    {
        return $this->dao->value(['template_name' => $name, 'type' => 1], 'value');
    }

    /**
     * 获取分类页完整配置
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getCategoryConfig(): array
    {
        $value = $this->dao->value(['template_name' => 'category', 'type' => 1], 'value');

        // 兼容旧数据格式（纯数字）
        if (is_numeric($value)) {
            return [
                'status' => (int)$value,
                'navigation' => [
                    'enabled' => false,
                    'title' => '相关配套',
                    'items' => []
                ]
            ];
        }

        // 解析JSON格式
        $config = json_decode($value, true);
        if (!$config) {
            return [
                'status' => 1,
                'navigation' => [
                    'enabled' => false,
                    'title' => '相关配套',
                    'items' => []
                ]
            ];
        }

        // 确保navigation字段存在
        if (!isset($config['navigation'])) {
            $config['navigation'] = [
                'enabled' => false,
                'title' => '相关配套',
                'items' => []
            ];
        }

        return $config;
    }

    /**
     * 保存分类页完整配置
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function saveCategoryConfig(array $data): bool
    {
        $info = $this->dao->get(['template_name' => 'category', 'type' => 1]);

        if (!$info) {
            throw new AdminException(100026);
        }

        // 构建存储数据
        $saveData = [
            'status' => $data['status'],
        ];

        // 保存导航配置
        if (!empty($data['navigation'])) {
            $saveData['navigation'] = $data['navigation'];
        }

        $info->value = json_encode($saveData, JSON_UNESCAPED_UNICODE);
        $info->update_time = time();
        $info->version = uniqid();  // 更新版本号触发前端缓存刷新

        return $info->save();
    }

    public function getMemberData()
    {
        $info = $this->dao->get(['template_name' => 'member', 'type' => 1]);
        $status = (int)$info['value'];
        $order_status = $info['order_status'] ? (int)$info['order_status'] : 1;
        $color_change = (int)$this->getColorChange('color_change');
        /** @var SystemGroupDataServices $systemGroupDataServices */
        $systemGroupDataServices = app()->make(SystemGroupDataServices::class);
        /** @var SystemGroupServices $systemGroupServices */
        $systemGroupServices = app()->make(SystemGroupServices::class);
        $menus_gid = $systemGroupServices->value(['config_name' => 'routine_my_menus'], 'id');
        $banner_gid = $systemGroupServices->value(['config_name' => 'routine_my_banner'], 'id');
        $routine_my_menus = $systemGroupDataServices->getGroupDataList(['gid' => $menus_gid], 'all');
        $routine_my_menus = $routine_my_menus['list'] ?? [];
        foreach ($routine_my_menus as &$item) {
            if (!isset($item['is_show']) || $item['is_show'] === '') {
               $item['is_show'] = '1';
            }
        }
        $routine_my_banner = $systemGroupDataServices->getGroupDataList(['gid' => $banner_gid], 'all');
        $routine_my_banner = $routine_my_banner['list'] ?? [];
        $my_banner_status = boolval($info['my_banner_status']);
        return compact('status', 'order_status', 'routine_my_menus', 'routine_my_banner', 'color_change', 'my_banner_status');
    }

    /**
     * 保存个人中心数据配置
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function memberSaveData(array $data)
    {
        /** @var SystemGroupDataServices $systemGroupDataServices */
        $systemGroupDataServices = app()->make(SystemGroupDataServices::class);
        if (!$data['status']) throw new AdminException(100100);
        $info = $this->dao->get(['template_name' => 'member', 'type' => 1]);
        if ($info) {
            $info->my_banner_status = $data['my_banner_status'];
            $info->value = $data['status'];
            $info->order_status = $data['order_status'];
            $info->business_status = $data['business_status'];
            $info->my_menus_status = $data['my_menus_status'];
            $info->update_time = time();
            $res = $info->save();
        } else {
            throw new AdminException(400459);
        }
        $systemGroupDataServices->saveAllData($data['routine_my_banner'], 'routine_my_banner');
        $systemGroupDataServices->saveAllData($data['routine_my_menus'], 'routine_my_menus');
        return true;
    }

    /**
     * 获取底部导航
     * @param string $template_name
     * @return array|mixed
     */
    public function getNavigation(string $template_name)
    {
        $value = $this->dao->value(['status' => 1], 'value');
        if (!$value) {
            $value = $this->dao->value(['template_name' => 'default'], 'value');
        }

        $navigation = [];
        if ($value) {
            $value = json_decode($value, true);
            foreach ($value as $item) {
                if (isset($item['name']) && strtolower($item['name']) === 'pagefoot') {
                    $navigation = $item;
                    break;
                }
            }
        }
        return $navigation;
    }

    /**
     * 取单个diy小程序预览二维码
     * @param int $id
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoutineCode(int $id)
    {
        $diy = $this->dao->getOne(['id' => $id, 'is_del' => 0]);
        if (!$diy) {
            throw new AdminException(100026);
        }
        /** @var QrcodeServices $QrcodeService */
        $QrcodeService = app()->make(QrcodeServices::class);
        return $QrcodeService->getRoutineQrcodePath($id, 0, 6, [], false);
    }

    /**
     * 获取底部预留模块配置
     * @return array
     */
    public function getPageBottomConfig(): array
    {
        $value = $this->dao->value(['template_name' => 'page_bottom', 'type' => 1], 'value');

        // 默认配置
        $defaultConfig = [
            'notice' => [
                'enabled' => false,
                'list' => []
            ],
            'navLinks' => [
                'enabled' => true,
                'list' => [
                    ['name' => 'ホーム', 'icon' => 'home', 'link' => '/pages/index/index'],
                    ['name' => 'カテゴリー覧', 'icon' => 'category', 'link' => '/pages/goods_cate/goods_cate'],
                    ['name' => 'カート', 'icon' => 'cart', 'link' => '/pages/order_addcart/order_addcart'],
                    ['name' => 'マイページ', 'icon' => 'user', 'link' => '/pages/user/index']
                ]
            ],
            'copyright' => [
                'enabled' => true,
                'text' => 'Copyright © 2012-2024 Example.co.jp | All Rights Reserved.'
            ],
            'backTop' => [
                'enabled' => true
            ]
        ];

        if (!$value) {
            return $defaultConfig;
        }

        $config = json_decode($value, true);
        if (!$config) {
            return $defaultConfig;
        }

        // 确保 enabled 字段是布尔类型
        if (isset($config['notice']['enabled'])) {
            $config['notice']['enabled'] = (bool)$config['notice']['enabled'];
        }
        if (isset($config['navLinks']['enabled'])) {
            $config['navLinks']['enabled'] = (bool)$config['navLinks']['enabled'];
        }
        if (isset($config['copyright']['enabled'])) {
            $config['copyright']['enabled'] = (bool)$config['copyright']['enabled'];
        }
        if (isset($config['backTop']['enabled'])) {
            $config['backTop']['enabled'] = (bool)$config['backTop']['enabled'];
        }

        return $config;
    }

    /**
     * 保存底部预留模块配置
     * @param array $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function savePageBottomConfig(array $data): bool
    {
        $info = $this->dao->get(['template_name' => 'page_bottom', 'type' => 1]);

        if (!$info) {
            // 创建新记录
            $this->dao->save([
                'name' => '底部预留模块',
                'template_name' => 'page_bottom',
                'type' => 1,
                'value' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'add_time' => time(),
                'update_time' => time(),
                'version' => uniqid(),
                'is_del' => 0
            ]);
            return true;
        }

        $info->value = json_encode($data, JSON_UNESCAPED_UNICODE);
        $info->update_time = time();
        $info->version = uniqid();

        return $info->save();
    }

    /**
     * 获取底部预留模块版本号
     * @return string
     */
    public function getPageBottomVersion(): string
    {
        return $this->dao->value(['template_name' => 'page_bottom', 'type' => 1], 'version') ?: '';
    }
}
