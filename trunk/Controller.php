<?php
class Haste_Controller extends Ethna_Controller
{
    //{{{ _getActionName_Form
    /**
     *  �ե�����ˤ���׵ᤵ�줿���������̾���֤�
     *
     *  @access protected
     *  @return string  �ե�����ˤ���׵ᤵ�줿���������̾
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
