<?php
class rouletteView extends roulette
{
    public function init()
    {
        // 스킨 경로 설정
        $this->setTemplatePath($this->module_path . 'skins/default');
    }

    public function dispRouletteIndex()
    {
        // 설정 로드
        $oRouletteModel = getModel('roulette');
        $config = $oRouletteModel->getRouletteConfig();

        // 사용자 포인트 정보 (옵션)
        if(Context::get('is_logged')) {
            $oPointModel = getModel('point');
            $member_srl = Context::get('logged_info')->member_srl;
            $current_point = $oPointModel->getPoint($member_srl);
            Context::set('current_point', $current_point);
        } else {
             Context::set('current_point', 0);
        }

        Context::set('roulette_config', $config);

        // Prepare Full Config for JS
        $items = json_decode($config->items_json ? $config->items_json : '[]');
        if (!$items) {
             // Fallback if DB JSON is corrupt
             $oRouletteModel = getModel('roulette');
             // Re-fetch default items logic if possible, or hardcode defaults here just for safety
             $items = [
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
        }

        $js_config = [
            'settings' => [
                'spinDuration' => ($config->spin_duration ? $config->spin_duration : 3) * 1000,
                'ticketPrice' => ($config->ticket_point_price) ? (int)$config->ticket_point_price : 0
            ],
            'items' => $items
        ];
        
        // Pass JSON string to template
        Context::set('config_json', json_encode($js_config, JSON_UNESCAPED_UNICODE));
        
        // JS에 넘길 변수 설정
        Context::addBrowserTitle('Roulette Event');
        
        // 템플릿 파일 지정
        $this->setTemplateFile('roulette');

        // 모달 모드 지원 (레이아웃 제거)
        if (Context::get('mode') == 'modal') {
            Context::set('layout', 'none');
            Context::addHtmlHeader('<style>
                #rhymix_admin_bar, .rhymix_admin_bar,
                .xe-widget-wrapper, .xe_content_admin,
                div[id*="admin"], div[class*="admin"],
                a[href*="admin"], button[class*="admin"],
                .postit-toggle, #dok_sticker, .sticker-area,
                a[href*="dispMemberAdmin"], .x_btn-primary
                { display: none !important; }
                
                .roulette-module button { display: flex !important; }
                .roulette-module .result-card { align-items: center !important; }
            </style>');
        }
    }
}
