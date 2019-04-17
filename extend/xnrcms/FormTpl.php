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
 * Description:表单模板类
 */
namespace xnrcms;

class FormTpl
{
  private $config  = [];
  private $model   = null;
  private $error   = '';

  /**
   * @access public
   * @return void
   */
  public function __construct() {

    $this->model  = model('devform');
  }

  //配置定制
  public function setConfig($name,$value) {
        if(isset($this->config[$name])) $this->config[$name] = $value;
  }

  //获取模板信息
  private function getTplData($cname = '')
  {
    //根据模板标识获取模板数据
    //先获取缓存数据
    $cachKey    = 'devform_'.md5($cname);
    $data       = cache($cachKey);
    $info       = [];
    $field      = [];

    if (!empty($data))
    {
      $info     = $this->model->where('cname','=',$cname)->find();
      $info     = !empty($info) ? $info->toArray() : [];

      if (!empty($info)) {
          $field  = $this->model->where('pid','=',$info['id'])->limit(100)->select();
          $field  = !empty($field) ? $field->toArray() : [];
      }
    }else{

      $info     = isset($data[0]) ? $data[0] : [];
      $field    = isset($data[1]) ? $data[1] : [];
    }

    foreach ($field as $key => $value) {
      if ($value['status'] != 1 || empty($value['config'])) unset($field[$key]);
    }

    $data           = ['info'=>$info,'list'=>$field];
    cache($cachKey,$data);

    return $data;
  }

  //显示
  public function formTpl($cname = '',$isEdit=1)
  {
    if (empty($cname)) return $this->setError(0);

    $data                   = $this->getTplData($cname);
    $info                   = $data['info'];
    $formList               = $data['list'];

    //缓存表单一条数据
    if (isset($info['id']) && $info['id'] > 0 )  cache('DevformDetails'.$info['id'],$info);
    if ($isEdit == '-2')  return ['info'=>$info,'list'=>$formList] ;

    //数据整理
    $formFields         = array() ;
    foreach ($formList as $index => $datum)
    {
        $config         = !empty($datum['config']) ? json_decode($datum['config'], true) : [];
        unset($datum['config']);

        $config         = array_merge($datum,$config);
        $formFields[]   = $config;
    }

    return $this->formatTplData($formFields,$info,$isEdit) ;
  }

  public function formatTplData($formFields=[],$info=[],$isEdit=0)
  {
    $type                   = $isEdit>0 ? 'edit' : 'add' ;
    
    //格式化
    $i = 0 ;
    $formField = array() ;
    foreach ($formFields as $index => $item) {
        $formFields[$index] = $item;

        if ($formFields[$index][$type] <= 0 && $isEdit != '-1') continue;

        if(!empty($formFields[$index]['default'])){
            //获取当前默认值类型
            $default = explode(':', $formFields[$index]['default']);
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
    if (!empty($formField)) {
        
        foreach ($formField as $k => $v) {
            
            $group = empty($v['group']) ? '基本信息' : $v['group'];

            $arr[$group][]        = $v;
        }
    }

    return ['info'=>$info,'list'=>$arr] ;
  }

  //通过模板ID获取模板数据
  public function getTplById($id=0)
  {
    $info     = [];
    $cachKey  = md5('getTplById_'.$id);
    if ($id >0) {
      $info   = cache($cachKey);
      if (empty($info)) {
        $info = $this->model->where('id',$id)->find();
        $info = !empty($info) ? $info->toArray() : [];
      }
    }

    cache($cachKey,$info);
    return $info;
  }

  //新增模板
  public function addTpl(){

  }

  //编辑模板
  public function editTpl()
  {

  }

  //删除模板
  public function delTpl()
  {

  }

  public function getError(){
    return $this->error;
  }

  private function setError($code=''){
    $this->error    = $this->errorMsg($code);
    return $this;
  }

  private function errorMsg($code='')
  {
    $msg        = [];
    $msg[0]     = '模板标识不能为空';
    $msg[1]     = '模板数据不存在';

    return isset($msg[$code]) ? $msg[$code] : '未知错误';
  }
}

?>
