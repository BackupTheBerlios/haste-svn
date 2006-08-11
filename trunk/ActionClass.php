<?php
class Haste_ActionClass extends Ethna_ActionClass
{
    /**
     *  [breaking B.C.] Ethna_ClassFactory $BBP1~(B
     *
     */
    function Haste_ActionClass(&$backend)
    {
        // $B?F%/%i%9(B
        parent::Ethna_ActionClass($backend);

        // Ethna_AppManager$B%*%V%8%'%/%H$N@_Dj(B
        $c =& $backend->getController();
        $manager_list = $c->getManagerList();
        foreach ($manager_list as $k => $v) {
            $this->$k =& $backend->getManager($v);
        }
    }
}
?>
