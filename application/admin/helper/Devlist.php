<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 小能人科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * Helper只要处理业务逻辑，默认会初始化数据列表接口、数据详情接口、数据更新接口、数据删除接口、数据快捷编辑接口
 * 如需其他接口自行扩展，默认接口如实在无需要可以自行删除
 */
namespace app\admin\helper;

use app\common\helper\Base;
use think\facade\Lang;

class Devlist extends Base
{
	private $dataValidate 		= null;
    private $mainTable          = 'devlist';
	
	public function __construct($parame=[],$className='',$methodName='',$modelName='')
    {
        parent::__construct($parame,$className,$methodName,$modelName);
        $this->apidoc           = request()->param('apidoc',0);
    }
    
    /**
     * 初始化接口 固定不用动
     * @param  [array]  $parame     接口需要的参数
     * @param  [string] $className  类名
     * @param  [string] $methodName 方法名
     * @return [array]              接口输出数据
     */
    public function apiRun()
    {   
        if (!$this->checkData($this->postData)) return json($this->getReturnData());
        //加载验证器
        $this->dataValidate = new \app\api\validate\DataValidate;
        
        //规避没有设置主表名称
        if (empty($this->mainTable)) return $this->returnData(['Code' => '120020', 'Msg'=>lang('120020')]);
        
        //接口执行分发
        $methodName     = $this->actionName;
        $data           = $this->$methodName($this->postData);
        //设置返回数据
        $this->setReturnData($data);
        //接口数据返回
        return json($this->getReturnData());
    }

    //支持内部调用
    public function isInside($parame,$aName)
    {
        return $this->$aName($parame);
    }

    /**
     * 接口列表数据
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function listData($parame)
    {
        //主表数据库模型
		$dbModel					= model($this->mainTable);

		/*定义数据模型参数*/
		//主表名称，可以为空，默认当前模型名称
		$modelParame['MainTab']		= $this->mainTable;

		//主表名称，可以为空，默认为main
		$modelParame['MainAlias']	= 'main';

		//主表待查询字段，可以为空，默认全字段
		$modelParame['MainField']	= [];

		//定义关联查询表信息，默认是空数组，为空时为单表查询,格式必须为一下格式
		//Rtype :`INNER`、`LEFT`、`RIGHT`、`FULL`，不区分大小写，默认为`INNER`。
		$RelationTab				= [];
		//$RelationTab['member']		= array('Ralias'=>'me','Ron'=>'me.uid=main.uid','Rtype'=>'LEFT','Rfield'=>array('nickname'));

		$modelParame['RelationTab']	= $RelationTab;

        //接口数据
        $modelParame['apiParame']   = $parame;

		//检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
		$modelParame['whereFun']	= 'formatWhereDefault';

		//排序定义
		$modelParame['order']		= 'main.sort desc,id asc';		
		
		//数据分页步长定义
		$modelParame['limit']		= $this->apidoc == 2 ? 1 : 1000;

		//数据分页页数定义
		$modelParame['page']		= (isset($parame['page']) && $parame['page'] > 0) ? $parame['page'] : 1;

		//数据缓存是时间，默认0 不缓存 ,单位秒
		$modelParame['cacheTime']	= 0;

		//列表数据
		$lists 						= $dbModel->getPageList($modelParame);

		//数据格式化
		$data 						= (isset($lists['lists']) && !empty($lists['lists'])) ? $lists['lists'] : [];

    	if (!empty($data)) {

            //自行定义格式化数据输出
    		//foreach($data as $k=>$v){

    		//}
    	}

    	$lists['lists'] 			= $data;

    	return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$lists];
    }

    /**
     * 接口数据添加/更新
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function saveData($parame)
    {
        //主表数据库模型
    	$dbModel					= model($this->mainTable);

        //数据ID
        $id                         = isset($parame['id']) ? intval($parame['id']) : 0;

        //自行定义入库数据 为了防止参数未定义报错，先采用isset()判断一下
        $saveData                   = [];
        //$saveData['parame']         = isset($parame['parame']) ? $parame['parame'] : '';
        $saveData['title']          = isset($parame['title']) ? $parame['title'] : '';
        $saveData['status']         = isset($parame['status']) ? $parame['status'] : 2;
        $saveData['tag']            = isset($parame['tag']) ? $parame['tag'] : '';
        $saveData['cname']          = isset($parame['cname']) ? $parame['cname'] : '';
        $saveData['sort']           = isset($parame['sort']) ? $parame['sort'] : '';
        $saveData['pid']            = isset($parame['pid']) ? $parame['pid'] : 0;
        $saveData['width']          = isset($parame['width']) ? $parame['width'] : '';
        $saveData['config']         = isset($parame['config']) ? $parame['config'] : '';
        $saveData['update_time']    = time();

        //数据校验
        if ($dbModel->checkValue($saveData['title'],$id,'title')) return ['Code' =>'200001','Msg'=>lang('200001')];

        if ($saveData['pid'] <= 0) {

            if (empty($saveData['title'])) return ['Code' => '200003', 'Msg'=>lang('200003')];
            if (empty($saveData['cname'])) return ['Code' => '200004', 'Msg'=>lang('200004')];
        }else{

            if (empty($saveData['title'])) return ['Code' => '200005', 'Msg'=>lang('200005')];
            if (empty($saveData['tag'])) return ['Code' => '200006', 'Msg'=>lang('200006')];
        }

        //规避遗漏定义入库数据
        if (empty($saveData)) return ['Code' => '120021', 'Msg'=>lang('120021')];

        //自行处理数据入库条件
        //...
		
        //通过ID判断数据是新增还是更新
    	if ($id <= 0) {

            $saveData['create_time']                = time();

            //执行新增
    		$info 									= $dbModel->addData($saveData);
    	}else{

            //执行更新
    		$info 									= $dbModel->updateById($id,$saveData);
    	}

    	if (!empty($info)) {

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info->toArray()];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * 接口数据详情
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function detailData($parame)
    {
        //主表数据库模型
    	$dbModel			= model($this->mainTable);

    	if (is_numeric($parame['id'])) {
            
            $info               = $dbModel->getOneById($parame['id']);
        }else{

            $info               = $dbModel->where('cname','eq',$parame['id'])->find();
        }

    	if (!empty($info)) {
    		
            //格式为数组
            $info                   = $info->toArray();

            //自行对数据格式化输出
            //...

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$info];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * 接口数据快捷编辑
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function quickEditData($parame)
    {
        //主表数据库模型
    	$dbModel			= model($this->mainTable);

        //数据ID
        $id                 = isset($parame['id']) ? intval($parame['id']) : 0;
        if ($id <= 0) return ['Code' => '120023', 'Msg'=>lang('120023')];

    	$info 				= $dbModel->updateById($id,[$parame['fieldName']=>$parame['updata']]);

    	if (!empty($info)) {

    		return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['id'=>$id]];
    	}else{

    		return ['Code' => '100015', 'Msg'=>lang('100015')];
    	}
    }

    /**
     * 接口数据删除
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function delData($parame)
    {
        //主表数据库模型
    	$dbModel				= model($this->mainTable);

        //数据ID
        $id                 = isset($parame['id']) ? intval($parame['id']) : 0;
        if ($id <= 0) return ['Code' => '120023', 'Msg'=>lang('120023')];

        //自行定义删除条件
        $modelParame['whereFun']    = 'formatWhereChildList';
        $modelParame['apiParame']   = $parame;

        $childCount                 = $dbModel->getDataCount($modelParame);

        if ($childCount > 0) {

            return ['Code' => '200007', 'Msg'=>lang('200007')];
        }
        //...
        
        //执行删除操作
    	$delCount				= $dbModel->delData($id);

    	return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>['count'=>$delCount]];
    }

    /*api:e2261b8f76e5c7c628d25cdccf3890ac*/
    /**
     * * 列表模板克隆接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function saveClone($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);

        //自行书写业务逻辑代码

        $dbModel                    = model($this->mainTable);

        $listId                     = $parame['listid'];
        $parame['pid']              = $listId;

        $cloneData                  = json_decode($parame['cloneData'],true);

        //主表名称，可以为空，默认当前模型名称
        $modelParame['MainTab']     = $this->mainTable;

        //接口数据
        $modelParame['apiParame']   = $parame;
        
        //检索条件 需要对应的模型里面定义查询条件 格式为formatWhere...
        $modelParame['whereFun']    = 'formatWhereDefault';
        
        //数据分页步长定义
        $modelParame['limit']       = 1000;
        
        //列表数据
        $lists                      = $dbModel->getPageList($modelParame);

        if (empty($lists) || !isset($lists['lists']) || empty($lists['lists']))
        return ['Code' => '200008', 'Msg'=>lang('200008')];

        $lists                      = $lists['lists'];
        $tpl                        = [];

        foreach ($lists as $key => $value) {
            
            foreach ($value as $kk => $vv) {
                    
                if ($kk == 'config') {

                    $value[$kk]   = json_decode($vv,true);
                }
            }

            $tpl[$value['id']]  = $value;
        }

        //入库表单数据
        //检测菜单名称是否存在
        if ($dbModel->checkValue($parame['listname'],0,'title'))
        return ['Code' => '200001', 'Msg'=>lang('200001')];

        $saveData['title']                      = $parame['listname'];
        $saveData['status']                     = 1;
        $saveData['sort']                       = 1;
        $saveData['pid']                        = 0;
        $saveData['update_time']                = time();
        $saveData['create_time']                = time();
        $info                                   = $dbModel->addData($saveData);

        if (empty($info) || $info['id'] <= 0)
        return ['Code' => '200009', 'Msg'=>lang('200009')];

        $pid                    = $info['id'];

        $updata                 = [];
        $cloneListId            = $cloneData['listid'];

        foreach ($cloneListId as $key => $value) {

            $title   = (isset($cloneData['title'][$key]) && !empty($cloneData['title'][$key])) ? $cloneData['title'][$key] : $tpl[$value]['title'];
            $tag     = (isset($cloneData['tag'][$key]) && !empty($cloneData['tag'][$key])) ? $cloneData['tag'][$key] : $tpl[$value]['tag'];
            $status  = (isset($cloneData['status'][$key]) && !empty($cloneData['status'][$key])) ? $cloneData['status'][$key] : $tpl[$value]['status'];
            $sort    = (isset($cloneData['sort'][$key]) && !empty($cloneData['sort'][$key])) ? $cloneData['sort'][$key] : $tpl[$value]['sort'];
            $width   = (isset($cloneData['width'][$key]) && !empty($cloneData['width'][$key])) ? $cloneData['width'][$key] : $tpl[$value]['width'];

            $type    = (isset($cloneData['type'][$key]) && !empty($cloneData['type'][$key])) ? $cloneData['type'][$key] : $tpl[$value]['config']['type'];
            $edit    = (isset($cloneData['edit'][$key]) && !empty($cloneData['edit'][$key])) ? $cloneData['edit'][$key] : $tpl[$value]['config']['edit'];
            $search  = (isset($cloneData['search'][$key]) && !empty($cloneData['search'][$key])) ? $cloneData['search'][$key] : $tpl[$value]['config']['search'];
            $default = (isset($cloneData['default'][$key]) && !empty($cloneData['default'][$key]))?$cloneData['default'][$key]:$tpl[$value]['config']['default'];
            $attr    = (isset($cloneData['attr'][$key]) && !empty($cloneData['attr'][$key])) ? $cloneData['attr'][$key] : $tpl[$value]['config']['attr'];

            $config                   = [];

            $config['title']          = $title ;
            $config['tag']            = $tag ;
            $config['type']           = $type ;
            $config['width']          = $width;
            $config['edit']           = $edit;
            $config['search']         = $search ;
            $config['default']        = $default ;
            $config['attr']           = $attr ;
            $config                   = json_encode($config);

            $updata[] = [
            'title'=>$title,
            'pid'=>$pid,
            'tag'=>$tag,
            'cname'=>'',
            'config'=>$config,
            'status'=>$status,
            'sort'=>$sort,
            'create_time'=>time(),
            'update_time'=>time(),
            'width'=>$width
            ];
        }

        if (!empty($updata)) {
            
            $res = $dbModel->insertAll($updata);

            return ['Code' => '000000', 'Msg'=>lang('200011'),'Data'=>['id'=>$pid]];
        }

        $dbModel->delData($pid);

        return ['Code' => '200009', 'Msg'=>lang('200009')];
    }

    /*api:e2261b8f76e5c7c628d25cdccf3890ac*/

    /*api:f7cbd4d84eec5b63f3edb55034775e00*/
    /**
     * * 列表模板发布接口
     * @param  [array] $parame 接口参数
     * @return [array]         接口输出数据
     */
    private function releaseData($parame)
    {
        //主表数据库模型
        $dbModel                = model($this->mainTable);

        //自行书写业务逻辑代码

        //需要返回的数据体
        $Data                   = ['TEST'];

        return ['Code' => '000000', 'Msg'=>lang('000000'),'Data'=>$Data];
    }

    /*api:f7cbd4d84eec5b63f3edb55034775e00*/

    /*接口扩展*/
}
