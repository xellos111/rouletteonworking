<?php
class rouletteAdminView extends roulette
{
    public function init()
    {
        $this->setTemplatePath($this->module_path . 'tpl');
    }

    public function dispRouletteAdminIndex()
    {
        // 설정 로드
        $oRouletteModel = getModel('roulette');
        $config = $oRouletteModel->getRouletteConfig();
        Context::set('config', $config);

        $this->setTemplateFile('index');
    }
}
