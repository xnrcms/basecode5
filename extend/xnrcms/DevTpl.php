<?php
/**
 * XNRCMS<562909771@qq.com>
 * ============================================================================
 * 版权所有 2018-2028 小能人科技有限公司，并保留所有权利。
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * Author: 王远庆
 * Date: 2018-02-08
 * Description:列表模板类
 */
namespace xnrcms;

class DevTpl
{
  private $config  = [];
  private $model   = null;
  private $tplType = 0;
  private $tt      = 0;
  private $error   = '';
  private $tplid   = 0;

  /**
   * @access public
   * @return void
   */
  public function __construct($type=0) {
    //定义模型
    $this->tplType  = $type;
    $this->model    = $this->tplType == 0 ? model('devlist') : model('devform');
    $this->tt       = time();
    $this->tplid    = 0;
    $this->pk       = 'id';
  }

  //配置定制
  public function setConfig($name,$value)
  {
      if(isset($this->config[$name])) $this->config[$name] = $value;
  }

  public function getTplId()
  {
    return $this->tplid;
  }

  //显示模板
  public function showTpl($pid = 0,$isEdit=1)
  {
    if (empty($pid) || $pid <= 0) return [];

    //获取模板字段数据
    $data                   = $this->getTplData($pid);
    $info                   = $data['info'];
    $formList               = $data['list'];

    //缓存表单一条数据
    if ($isEdit == '-2')  return ['info'=>$info,'list'=>$formList] ;

    //数据整理
    $tplFields              = [];
    foreach ($formList as $index => $datum)
    {
        $config         = !empty($datum['config']) ? json_decode($datum['config'], true) : [];
        unset($datum['config']);

        $config         = array_merge($datum,$config);
        $tplFields[]    = $config;
    }

    return $this->tplType == 0 ? $this->formatListTplData($tplFields,$info,$isEdit) : $this->formatFormTplData($tplFields,$info,$isEdit) ;
  }

  //格式化列表模板数据
  public function formatListTplData($listNote = [],$info=[],$isEdit=0)
  {
      //初始化数据
      $search                 = [];
      $thead                  = [];
      $data                   = ['info'=>$info,'search'=>$search,'thead'=>$thead];

      //格式化数据
      if (!empty($listNote)) {

          $width              = 0;
          $counts             = count($listNote);
          $nums               = 0;
          $i                  = 0 ;
          foreach ($listNote as $index => $item)
          {
              $nums++;

              //处理默认数据
              $default            = !empty($item['default']) ? explode(':',$item['default']) : [];
              $item['default']    = [];

              if (isset($default[0]) && isset($default[1]))
              {
                  if ($default[0] == 'parame') {
                      $listNote[$index]['default']['type'] = $default[0];
                      $listNote[$index]['default']['parame'] = $default[1];
                  } else {
                      $parame = array() ;
                      $arr = explode(',',$default[1]) ;
                      foreach ($arr as $key => $value) {
                          $arr = explode('=',$value) ;
                          $parame[$arr[0]] = $arr[1] ;
                      }
                      $item['default']['type'] = $default[0];
                      $item['default']['parame'] = count($arr)>1 ? $parame : $default[1];
                  }
              }

              if ($counts == $nums) {
                  $item['width']          = $width >= 100 ? 0 : 100-$width;
              }else{
                  $width                  += $item['width'];
              }

              if ($width >= 100)  continue;

              //表头位数据
              $thead[$index]['id']       = $item['id'] ;
              $thead[$index]['title']    = $item['title'] ;
              $thead[$index]['tag']      = $item['tag'] ;
              $thead[$index]['width']    = $item['width'] ;
              $thead[$index]['edit']     = $item['edit'] ;
              $thead[$index]['search']   = $item['search'] ;
              $thead[$index]['type']     = $item['type'] ;
              $thead[$index]['attr']     = $item['attr'] ;
              $thead[$index]['default']  = $item['default'] ;

              //搜索位数据
              if ($item['search'] ==1){
                  $search[$i]['id']       = $item['id'] ;
                  $search[$i]['title']    = $item['title'] ;
                  $search[$i]['tag']      = $item['tag'] ;
                  $search[$i]['width']    = $item['width'] ;
                  $search[$i]['edit']     = $item['edit'] ;
                  $search[$i]['search']   = $item['search'] ;
                  $search[$i]['type']     = $item['type'] ;
                  $search[$i]['attr']     = $item['attr'] ;
                  $search[$i]['default']  = $item['default'] ;

                  $i++ ;
              }
          }
      }

      $data['info']   = $info ;
      $data['search'] = $search ;
      $data['thead']  = $thead ;

      return $data ;
  }

  public function formatFormTplData($formFields=[],$info=[],$isEdit=0)
  {
    $type                   = $isEdit>0 ? 'edit' : 'add' ;

    //格式化
    $i                      = 0;
    $formField              = [];
    foreach ($formFields as $index => $item)
    {
        $formFields[$index] = $item;

        if ($formFields[$index][$type] <= 0 && $isEdit != '-1') continue;
        if(!empty($formFields[$index]['default']))
        {
            //获取当前默认值类型
            $default          = explode(':', $formFields[$index]['default']);
            $formFields[$index]['default'] = [];
            if ( isset($default[0]) && isset($default[1]) )
            {
                if ($default[0] == 'parame') {
                    $formFields[$index]['default']['type'] = $default[0];
                    $formFields[$index]['default']['parame'] = $default[1];
                } else {
                    $parame = array() ;
                    $arr = explode(',',$default[1]) ;
                    foreach ($arr as $key => $value) {
                        $arr = explode('=',$value) ;
                        $parame[$arr[0]] = $arr[1] ;
                    }
                    $formFields[$index]['default']['type'] = $default[0];
                    $formFields[$index]['default']['parame'] = count($arr)>1 ? $parame : $default[1];
                }
            }
        }

        $formField[$i] = $formFields[$index];
        $i++;
    }

    $arr = [];
    if (!empty($formField))
    {
        foreach ($formField as $k => $v)
        {
            $group = empty($v['group']) ? '基本信息' : $v['group'];
            $arr[$group][]        = $v;
        }
    }

    return ['info'=>$info,'list'=>$arr];
  }

  //获取模板信息
  private function getTplData($pid = 0)
  {
    if (empty($pid) || $pid <= 0) return [];

    //根据模板标识获取模板数据
    //先获取缓存数据
    $cachKey    = md5('tpl_tplType='.$this->tplType.'_pid='.$pid);
    $data       = cache($cachKey);

    $info       = [];
    $field      = [];

    if (!empty($data))
    {
      $info     = isset($data['info']) ? $data['info'] : [];
      $field    = isset($data['list']) ? $data['list'] : [];
    }else{
      $info   = $this->getTplById($pid);
      $field  = $this->model->where('pid','=',$pid)->limit(200)->order('sort desc')->select();
      $field  = !empty($field) ? $field->toArray() : [];
    }

    foreach ($field as $key => $value) {
      if ($value['status'] != 1 || empty($value['config'])) unset($field[$key]);
    }

    $data           = ['info'=>$info,'list'=>$field];
    cache($cachKey,$data);

    return $data;
  }

  //初始化表单或者列表模板
  public function initTplData($cname='',$title='',$type=0)
  {
      if (empty($cname) || empty($title) || !in_array($type,[0,1])) return 0;

      //定义模型
      $this->tplType  = $type;
      $this->model    = $this->tplType == 0 ? model('devlist') : model('devform');

      $title      = !empty($title) ? $title : $cname;
      $cname      = md5(strtolower($cname));
      $id         = $this->checkCname($cname);

      //如果模板数据存在直接返回模板ID
      if (!empty($id) && $id >= 0)
      {
        $info     = $this->getTplById($id);

        //表单名称相同 直接返回
        if (isset($info['title']) && $info['title'] == $title) return $id;

        cache(md5('getTplById_tplType='.$this->tplType.'_'.$id),null);
        cache(md5('tpl_tplType='.$this->tplType.'_pid='.$id),null);

        $this->model->save(['title'=>$title,'update_time'=>$this->tt],['id'=>$id]);
        return $id;
      }


      //不存在新增并返回模板ID
      $updata                 = [];
      $updata['title']        = $title;
      $updata['pid']          = 0;
      $updata['cname']        = $cname;
      $updata['config']       = '';
      $updata['create_time']  = $this->tt;
      $updata['update_time']  = $this->tt;

      //入库数据
      $this->model->save($updata);

      $this->tplid    = $this->model->id;

      //返回模板ID
      return $this->tplid;
  }

  public function saveTplData($param=[])
  {
    //数据收集
    $id           = isset($param['id']) ? intval($param['id']) : 0;
    $pid          = isset($param['pid']) ? intval($param['pid']) : 0;
    $title        = isset($param['title']) ? trim($param['title']) : '';
    $status       = isset($param['status']) ? intval($param['status']) : 0;
    $sort         = isset($param['sort']) ? intval($param['sort']) : 1;
    $tag          = isset($param['tag']) ? trim($param['tag']) : '';
    $type         = isset($param['type']) ? trim($param['type']) : '';
    $width        = isset($param['width']) ? intval($param['width']) : 0;
    $attr         = isset($param['attr']) ? trim($param['attr']) : '';
    $group        = isset($param['group']) ? trim($param['group']) : '';
    $notice       = isset($param['notice']) ? trim($param['notice']) : '';
    $require      = isset($param['require']) ? intval($param['require']) : 0;
    $cname        = isset($param['cname']) ? md5(strtolower(trim($param['cname']))) : '';

    //定义配置数据
    $config                 = '';
    $cdata                  = [];
    $cdata['width']         = $width;
    $cdata['type']          = $type;

    if ($this->tplType == 1) {
      $cdata['add']         = (isset($param['fdone'][1]) && $param['fdone'][1] ==1) ? 1 : 0;
      $cdata['edit']        = (isset($param['fdone'][2]) && $param['fdone'][2] ==2) ? 1 : 0;
      $cdata['group']       = $group;
      $cdata['require']     = $require;
      $cdata['group']       = $group;
      $cdata['notice']       = $notice;
    }else{
      $cdata['edit']        = (isset($param['fdone'][1]) && $param['fdone'][1] ==1) ? 1 : 0;
      $cdata['search']      = (isset($param['fdone'][2]) && $param['fdone'][2] ==2) ? 1 : 0;
    }

    $cdata['default']       = trim($param['default']);
    $cdata['attr']          = !empty($attr)?str_replace(array("\r\n", "\r", "\n")," ",$attr):'';
    $config                 = json_encode($cdata);

    //定义入库数据
    $updata                 = [];
    $updata['title']        = $title;
    $updata['status']       = $status == 1 ? 1 : 2;
    $updata['sort']         = $sort <= 0 ? 1 : $sort;
    $updata['tag']          = $tag;
    $updata['pid']          = $pid;
    $updata['config']       = $config;
    $updata['width']        = $width;
    $updata['cname']        = $cname;
    $updata['update_time']  = $this->tt;

    if ($id > 0) {
      //清理缓存
      cache(md5('getTplById_tplType='.$this->tplType.'_'.$id),null);
      cache(md5('tpl_tplType='.$this->tplType.'_pid='.$pid),null);

      $this->model->save($updata,['id'=>$id]);

      $this->tplid    = $id;
      return 1;
    }else{

      $updata['create_time'] = $this->tt;

      $this->model->save($updata);

      $this->tplid    = $this->model->id;
      return 2;
    }
  }

  //获取模板允许提交的数据
  public function getFormTplData($tplid = 0,$postData = [])
  {
      if (empty($tplid) || empty($postData))  return [];

      $formNode   = $this->showTpl($tplid,'-2');
      $field      = [];
      $signData   = [];

      //定义允许提交的字段
      if (!empty($formNode) && isset($formNode['list']) && !empty($formNode['list']))
      {
          foreach ($formNode['list'] as $arr)
          {
              $field[]    = $arr['tag'];
          }
      }
      
      //过滤允许提交的数据
      if (!empty($field))
      {
          foreach ($postData as $key => $value)
          {
              if (in_array($key,$field))
              {
                  $signData[$key]     = $value;
              }
          }
      }

      return $signData;
  }

  //校验调用标识是否存在
  public function checkCname($cname='')
  {
    return $this->model->where('cname','=',$cname)->value('id');
  }

  private function errorMsg($code='')
  {
    $msg        = [];
    $msg[0]     = '模板标识不能为空';
    $msg[1]     = '模板数据不存在';

    return isset($msg[$code]) ? $msg[$code] : '未知错误';
  }

  //通过模板ID获取模板数据
  public function getTplById($id=0)
  {
    if (empty($id) || $id <= 0) return [];

    //缓存KEY
    $cachKey    = md5('getTplById_tplType='.$this->tplType.'_'.$id);
    $info       = cache($cachKey);

    if (empty($info)) {
      $info = $this->model->where('id',$id)->find();
      $info = !empty($info) ? $info->toArray() : [];
    }

    cache($cachKey,$info);

    $this->tplid    = (isset($info['id']) && $info['id'] > 0) ? $info['id'] : 0;

    return $info;
  }

  public function delTplData($id=0)
  {
    if (empty($id) || $id <= 0) return 0;

    $this->tplid    = $id;

    $info           = $this->getTplById($id);
    if (empty($info)) return 0;

    $pid            = isset($info['pid']) ? $info['pid'] : 0;
    
    //数据删除
    $this->model->where("id",$id)->delete();

    //清除缓存
    cache(md5('getTplById_tplType='.$this->tplType.'_'.$id),null);
    cache(md5('tpl_tplType='.$this->tplType.'_pid='.$pid),null);

    return 1;
  }

  public function checkTpl($id,$type)
  {
    $this->tplType  = $type;
    $this->getTplById($id);
    return $this->tplid;
  }
}

?>
