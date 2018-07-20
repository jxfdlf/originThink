<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 13:54
 */

namespace app\admin\controller;
use think\Controller;
use think\Loader;
class Common extends Controller
{
    public $uid;             //用户id
    public $group_id;        //用户组
    public $view_path='';   //模板
    /**
     * 后台控制器初始化
     */
    protected function initialize()
    {
        $user = session('user_auth');
        $this->uid=$user['uid'];
        $this->group_id=$user['group_id'];
        if($this->uid!=1){
            $ruleslist= cache('ruleslist_'.$this->group_id );//获取当前用户组菜单
        }else{
            $ruleslist= cache('ruleslist_admin' );//获取当前用户组菜单
        }
        $this->assign('ruleslist',$ruleslist);
        $this->config();
    }

    /**
     * 动态配置
     * @author 原点 <467490186@qq.com>
     */
    private function config(){
        config('template.view_path','layui');
        if(cache('config')){
            $list=cache('config');
        }else{
            $list=db('config')->where('name','=','system_config')->json(['value'])->field('value,status')->find();
            cache('config',$list);
        }
        if($list['status']==1){
            config('app_debug',$list['value']['debug']);
            config('app_trace',$list['value']['trace']);
            config('trace.type',$list['value']['trace_type']==0?'Html':'Console');
            $this->view_path=$list['value']['view_path'];
        }else{
//            $this->view_path='default';
            $this->view_path='layui';
        }
    }
    protected function getActionTemplate($request)
    {
        $rule = [$request->action(true), Loader::parseName($request->action(true)), $request->action()];
        $type = config('template.auto_rule');

        return isset($rule[$type]) ? $rule[$type] : $rule[0];
    }

    public function return_fetch($template='')
    {
        if($template==''){
            $request = $this->app['request'];
            $controller = Loader::parseName($request->controller());
            $template=str_replace('.', DIRECTORY_SEPARATOR, $controller) . DIRECTORY_SEPARATOR .$this->getActionTemplate($request);
        }
        $template=$this->view_path.DIRECTORY_SEPARATOR.$template;
        return $this->fetch($template);
    }

}