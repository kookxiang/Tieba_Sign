<?php

namespace Controller;

use Model\User;

class Setting
{
    /**
     * @ForceJSON
     * @RequireLogin
     * @Route /Setting/Get.action
     */
    public function getBasicSetting()
    {
        $account = User::getCurrent()->getDefaultAccount();
        return [
            'accountExp' => "当前账号有效期还有 7 天.",
            'allowRenew' => true,
            'wenkuSign' => (bool)$account->wenkuSign,
            'zhidaoSign' => (bool)$account->zhidaoSign,
            'mailSetting' => (int)$account->mailSetting
        ];
    }

    /**
     * @ForceJSON
     * @RequireLogin
     * @Route /Setting/Save.action
     */
    public function saveBasicSetting()
    {
        $newSetting = json_decode($_POST['data'], true);
        $account = User::getCurrent()->getDefaultAccount();
        $account->mailSetting = $newSetting['mailSetting'];
        $account->wenkuSign = $newSetting['wenkuSign'];
        $account->zhidaoSign = $newSetting['zhidaoSign'];
        $account->save();
    }
}
