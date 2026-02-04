<?php
class rouletteModel extends roulette
{
    public function init()
    {
    }
    
    /**
     * @brief 기본 설정을 가져오는 함수
     */
    public function getRouletteConfig() {
        $oModuleModel = getModel('module');
        $config = $oModuleModel->getModuleConfig('roulette');
        
        // 기본값 설정
        if(!$config->ticket_point_price) $config->ticket_point_price = 0;
        if(!$config->spin_duration) $config->spin_duration = 3; // 기본 3초
        
        // items_json이 없으면 기본 아이템 셋팅 (config.js의 내용을 기반으로)
        if(!$config->items_json) {
            $default_items = [
                ["text" => "1천", "subText" => "캡슐", "color" => "#FFFFFF", "textColor" => "#6A4DFF", "weight" => 0.5, "is_point" => true, "point_reward" => 1000],
                ["text" => "15", "subText" => "캡슐", "color" => "#F0F4FF", "textColor" => "#333", "weight" => 20, "is_point" => true, "point_reward" => 15],
                ["text" => "25", "subText" => "캡슐", "color" => "#FFFFFF", "textColor" => "#333", "weight" => 20, "is_point" => true, "point_reward" => 25],
                ["text" => "50", "subText" => "캡슐", "color" => "#F0F4FF", "textColor" => "#333", "weight" => 15, "is_point" => true, "point_reward" => 50],
                ["text" => "100", "subText" => "캡슐", "color" => "#FFFFFF", "textColor" => "#333", "weight" => 15, "is_point" => true, "point_reward" => 100],
                ["text" => "150", "subText" => "캡슐", "color" => "#F0F4FF", "textColor" => "#333", "weight" => 10, "is_point" => true, "point_reward" => 150],
                ["text" => "200", "subText" => "캡슐", "color" => "#FFFFFF", "textColor" => "#333", "weight" => 10, "is_point" => true, "point_reward" => 200],
                ["text" => "500", "subText" => "캡슐", "color" => "#F0F4FF", "textColor" => "#333", "weight" => 4.5, "is_point" => true, "point_reward" => 500],
                ["text" => "보너스", "subText" => "티켓 1장", "color" => "#dbeafe", "textColor" => "#2563eb", "weight" => 5, "is_point" => false, "point_reward" => 0] 
            ];
            $config->items_json = json_encode($default_items);
        }
        
        return $config;
    }
}
