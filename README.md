# 🎰 약준모 룰렛 모듈 (Roulette/Neon)

약준모(약사 준비 모임)를 위한 라이믹스(Rhymix) 전용 이벤트 룰렛 모듈입니다.
**Neon Glassmorphism** 디자인이 적용되어 있으며, 관리자 페이지에서 확률과 아이템을 손쉽게 설정할 수 있습니다.

![Preview](skins/default/img/preview.png) (이미지가 있다면 추가)

---

## 🛠 설치 방법 (Installation)

1.  **파일 업로드**
    *   다운로드 받은 `roulette` 폴더를 라이믹스 설치 경로의 `modules/` 디렉토리 안에 업로드합니다.
    *   최종 경로: `/modules/roulette`

2.  **모듈 설치/업데이트**
    *   라이믹스 관리자 페이지 > **모듈/애드온** 메뉴로 이동합니다.
    *   **'룰렛 (Roulette)'** 모듈을 찾아 **[설치]** 또는 **[업데이트]** 버튼을 클릭합니다.
    *   자동으로 DB 테이블(`rx_roulette_tickets`, `rx_roulette_log`)이 생성됩니다.

---

## ⚙️ 설정 방법 (Configuration)

관리자 페이지에서 룰렛의 당첨 항목, 확률, 디자인 색상을 엑셀처럼 쉽게 관리할 수 있습니다.

1.  관리자 페이지 > **룰렛 (Roulette)** 모듈 클릭.
2.  **[설정]** 탭으로 이동.
3.  **Spin Duration**: 룰렛이 돌아가는 시간(ms)을 설정합니다 (기본 5000ms = 5초).
4.  **Items Configuration (아이템 설정)**:
    *   **테이블(표)** 형태로 아이템이 표시됩니다.
    *   **[+ Add Item]**: 새로운 당첨 항목을 추가합니다.
    *   **Name (Admin)**: 관리자용 식별 이름 (예: 1등 상품).
    *   **Text (Wheel)**: 룰렛 판에 표시될 큰 글씨 (예: 1000).
    *   **SubText**: 룰렛 판에 표시될 작은 글씨 (예: 포인트).
    *   **Type**: `Point`(포인트 지급) 또는 `Ticket`(티켓 반환).
    *   **Value**: 지급할 포인트 양.
    *   **Weight**: 당첨 가중치 (확률). 높을수록 당첨 확률이 높습니다.
    *   **Color**: 해당 칸의 배경색.
    *   **Text Color**: 글자 색상.
    *   **Del**: 해당 항목 삭제.
5.  **[Save]** 버튼을 눌러 저장합니다.

---

## 🔗 사이트 적용 및 호출 방법

### 1. 사이트 메뉴에 추가하기 (권장)
가장 일반적이고 쉬운 방법입니다.
1.  **사이트 디자인** > **사이트 메뉴 편집**.
2.  원하는 메뉴 위치 선택 > **[메뉴 추가]**.
3.  **모듈** 탭 선택 > **'룰렛 (Roulette)'** 선택.
4.  메뉴 이름 입력 후 저장.
5.  이제 해당 메뉴를 클릭하면 룰렛 페이지로 이동합니다.

### 2. 직접 링크 (URL)
배너나 버튼에 직접 링크를 걸어 이동시킬 수 있습니다.
*   URL: `http://일반주소/?act=dispRouletteIndex`
*   짧은주소(mid)를 생성했다면: `http://일반주소/mid_name`

### 3. 모달(팝업)로 띄우기 (고급)
이 모듈은 기본적으로 **전체 페이지** 형태로 동작하지만, 레이아웃에 아래 스크립트를 추가하면 **팝업**처럼 띄울 수 있습니다.

**[레이아웃 스크립트 추가 방법]**
라이믹스 관리자 > 사이트 디자인 설정 > 레이아웃 설정 > **'스크립트/스타일'** 탭의 **'하단 스크립트'**란에 붙여넣으세요.

```html
<!-- 룰렛 모달 스크립트 -->
<script>
jQuery(document).ready(function($) {
    if($('#roulette-layer').length == 0) {
        var modalHtml = 
        '<div id="roulette-layer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center;">' +
            '<div style="position:relative; width:400px; max-width:95%; height:650px; max-height:85%; background:#fcf9f2; border-radius:25px; overflow:hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25); border: 2px solid #555;">' +
                 '<button onclick="jQuery(\'#roulette-layer\').hide();" style="position:absolute; top:15px; right:15px; z-index:100; background:rgba(0,0,0,0.2); border:none; width:32px; height:32px; border-radius:50%; color:#fff; font-size:20px; cursor:pointer;">&times;</button>' +
                '<iframe src="" id="roulette-frame" style="width:100%; height:100%; border:none; background:transparent;" scrolling="no"></iframe>' +
            '</div>' +
        '</div>';
        $('body').append(modalHtml);
    }

    window.openRouletteLayer = function() {
        var frame = $('#roulette-frame');
        if(frame.attr('src') === "") {
            frame.attr('src', '/?act=dispRouletteIndex'); 
        }
        $('#roulette-layer').css('display', 'flex');
    };
});
</script>
```
버튼에는 `<button onclick="openRouletteLayer()">룰렛 열기</button>` 처럼 사용하시면 됩니다.

---

## ❓ 자주 묻는 질문 (Troubleshooting)

**Q. 룰렛이 돌지 않고 멈춰있어요.**
A. 브라우저 콘솔(F12)을 확인해보세요. 만약 자바스크립트 오류가 없다면, 티켓이 부족한 경우일 수 있습니다. 관리자 권한으로 로그인하면 '티켓 충전' 버튼이 보입니다.

**Q. 디자인이 깨져서 보여요.**
A. `skins/default/css/style.css` 파일이 제대로 로드되지 않은 것일 수 있습니다. 강력 새로고침(Ctrl+F5)을 해보시거나, 레이아웃 설정에서 충돌이 없는지 확인하세요.

**Q. 확률은 어떻게 계산되나요?**
A. 설정된 모든 아이템의 **Weight(가중치)** 총합을 구한 뒤, 랜덤 난수가 어느 구간에 속하는지로 결정됩니다. 가중치가 높을수록 당첨 확률이 비례하여 올라갑니다.

---
**제작**: Xellos with antigravity (2026)
