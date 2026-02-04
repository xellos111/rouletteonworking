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

        // 3. 포인트 체크
        $oPointModel = getModel('point');
        $current_point = $oPointModel->getPoint($member_srl);

        if ($price > 0 && $current_point < $price) {
            return $this->createJSONResponse(false, '포인트가 부족합니다.');
        }

        // 4. 포인트 차감
        $oPointController = getController('point');
        if ($price > 0) {
            $oPointController->setPoint($member_srl, $price, 'minus');
        }

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

        // 6. 보상 지급 (포인트인 경우)
        // is_point가 true이고 point_reward가 설정되어 있으면 지급
        if (isset($selected_item->is_point) && $selected_item->is_point && isset($selected_item->point_reward)) {
             $reward = (int)$selected_item->point_reward;
             if ($reward > 0) {
                 $oPointController->setPoint($member_srl, $reward, 'add');
             }
        }

        // 7. 로그 기록 (TODO: roulette_log 테이블이 있다면 insert)
        
        // 8. 결과 반환
        $remaining_point = $oPointModel->getPoint($member_srl);
        
        $output = new stdClass();
        $output->success = true;
        $output->index = $selected_index;
        $output->item = $selected_item;
        $output->items_list = $items; // Send the list used for calculation to ensure sync
        $output->remaining_point = $remaining_point;
        
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
