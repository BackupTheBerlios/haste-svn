<?php
class Haste_ActionClass extends Ethna_ActionClass
{
    /**
     *  [breaking B.C.] Ethna_ClassFactory 対応
     *
     */
    function Haste_ActionClass(&$backend)
    {
        // 親クラス
        parent::Ethna_ActionClass($backend);

        // Ethna_AppManagerオブジェクトの設定
        $c =& $backend->getController();
        $manager_list = $c->getManagerList();
        foreach ($manager_list as $k => $v) {
            $this->$k =& $backend->getManager($v);
        }
    }
}
?>
