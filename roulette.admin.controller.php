<?php
class rouletteAdminController extends roulette
{
    public function init()
    {
    }

    public function procRouletteAdminInsertConfig()
    {
        $args = Context::getRequestVars();
        
        // Save module config
        $oModuleController = getController('module');
        $oModuleController->insertModuleConfig('roulette', $args);
        
        $this->setMessage('success_saved');
        
        if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON'))) {
            $returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getUrl('act', 'dispRouletteAdminIndex');
            $this->setRedirectUrl($returnUrl);
        }
    }
}
