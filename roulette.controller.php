<?php
class rouletteController extends roulette
{
    public function init()
    {
    }

    /**
     * @brief 룰렛 회전 (게임 실행)
     * AJAX 요청 처리
     */
    public function procRouletteSpin()
    {
        // 1. 로그인 체크
        if (!Context::get('is_logged')) {
            return $this->createJSONResponse(false, '로그인이 필요합니다.');
        }

        $logged_info = Context::get('logged_info');
        $member_srl = $logged_info->member_srl;

        // 2. 설정 로드
        $oRouletteModel = getModel('roulette');
        $config = $oRouletteModel->getRouletteConfig();
        
        $price = (int)$config->ticket_point_price;
        $items = json_decode($config->items_json);

        if (!$items) {
             return $this->createJSONResponse(false, '룰렛 아이템이 설정되지 않았습니다.');
        }

        // 3. 티켓 체크 (A-Mission Integration)
        $oAMissionModel = getModel('a_mission');
        $oAMissionController = getController('a_mission');
        
        if(!$oAMissionModel || !$oAMissionController) {
             return $this->createJSONResponse(false, 'A-Mission 모듈이 설치되지 않았습니다.');
        }

        $current_tickets = $oAMissionModel->getTicketCount($member_srl);
        // Use configured price as ticket cost (fallback to 1 if not set or 0)
        $setting_cost = (int)$config->ticket_point_price;
        $ticket_cost = ($setting_cost > 0) ? $setting_cost : 1; 

        if ($current_tickets < $ticket_cost) {
            return $this->createJSONResponse(false, '티켓이 부족합니다.');
        }

        // 4. 티켓 차감
        // ($member_srl, $amount, $message)
        $oAMissionController->addTicket($member_srl, -$ticket_cost, 'Roulette Game');

        // 5. 확률 로직 (Weighted Random)
        $total_weight = 0;
        foreach ($items as $item) {
            $total_weight += $item->weight;
        }

        $rand = mt_rand(0, $total_weight * 100) / 100;
        $cumulative_weight = 0;
        $selected_index = 0;
        $selected_item = null;

        foreach ($items as $index => $item) {
            $cumulative_weight += $item->weight;
            if ($rand <= $cumulative_weight) {
                $selected_index = $index;
                $selected_item = $item;
                break;
            }
        }
        
        if (!$selected_item) {
            // 만약 계산 오류로 선택되지 않았다면 첫번째 아이템 선택 (Fallback)
             $selected_index = 0;
             $selected_item = $items[0];
        }

        // 6. 보상 지급 Logic
        $reward_value = isset($selected_item->value) ? (int)$selected_item->value : 0;
        $reward_type = isset($selected_item->type) ? $selected_item->type : 'point';

        // Legacy compatibility: validity check
        if (!isset($selected_item->type) && isset($selected_item->point_reward) && $selected_item->point_reward > 0) {
            $reward_type = 'point';
            $reward_value = (int)$selected_item->point_reward;
        }

        if ($reward_value > 0) {
            if ($reward_type === 'point') {
                $oPointController = getController('point');
                $oPointController->setPoint($member_srl, $reward_value, 'add');
            } elseif ($reward_type === 'ticket') {
                // [Ticket Reward] "One More!"
                $oAMissionController->addTicket($member_srl, $reward_value, 'Roulette Prize: ' . $selected_item->text);
            }
        }

        // 7. 로그 기록 (DB Insert)
        $args = new stdClass();
        $args->module_srl = $config->module_srl ? $config->module_srl : 0;
        $args->member_srl = $member_srl;
        $args->point_spent = 0; // Tickets used
        $args->reward_text = $selected_item->text . ($selected_item->subText ? ' (' . $selected_item->subText . ')' : '');
        $args->reward_point = ($reward_type === 'point') ? $reward_value : 0; 
        $args->ipaddress = $_SERVER['REMOTE_ADDR'];
        
        $output_log = executeQuery('roulette.insertRouletteLog', $args);
        
        // 7-2. A-Mission Game Log (Economy Tracking)
        $game_args = new stdClass();
        $game_args->member_srl = $member_srl;
        $game_args->spent_tickets = $ticket_cost; 
        $game_args->won_points = ($reward_type === 'point') ? $reward_value : 0;
        $game_args->won_tickets = ($reward_type === 'ticket') ? $reward_value : 0;
        $game_args->regdate = date('YmdHis');
        
        executeQuery('a_mission.insertGameLog', $game_args);
        
        if(!$output_log->toBool()) {
           // 로그 저장은 실패해도 게임은 진행되도록 함 (조용히 넘어가거나 에러 로그만 남김)
           // return $this->createJSONResponse(false, '로그 저장 실패'); 
        }
        // 8. 결과 반환
        // [FIX] Update remaining Balance (Tickets)
        // JS uses 'remaining_point' variable, so we map ticket count to it to avoid JS edit
        $remaining_tickets = $oAMissionModel->getTicketCount($member_srl);
        
        $output = new stdClass();
        $output->success = true;
        $output->index = $selected_index;
        $output->item = $selected_item;
        $output->items_list = $items; // Send the list used for calculation to ensure sync
        $output->remaining_point = $remaining_tickets;
        
        $this->add('result', $output);
    }

    private function createJSONResponse($success, $message, $data = null) {
        $output = new stdClass();
        $output->success = $success;
        $output->message = $message;
        if($data) {
            foreach($data as $key => $val) {
                $output->{$key} = $val;
            }
        }
        $this->add('result', $output);
        return $output; 
    }
}
