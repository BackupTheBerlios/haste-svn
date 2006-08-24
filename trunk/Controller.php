<?php
class Haste_Controller extends Ethna_Controller
{
    //{{{ _getActionName_Form
    /**
     *  フォームにより要求されたアクション名を返す
     *
     *  @access protected
     *  @return string  フォームにより要求されたアクション名
     */
    function _getActionName_Form()
    {
        
        isset($_SERVER['PATH_INFO']) ? $arr = explode('/', $_SERVER['PATH_INFO']):
            $arr = false;
        
        return $arr[1];
    
    }
    //}}}
}
?>
