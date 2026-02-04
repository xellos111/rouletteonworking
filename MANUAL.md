# 🎰 라이믹스 룰렛 모듈 (Rhymix Roulette Module)
> **Luxury Gold 테마**가 적용된 이벤트 룰렛 모듈

## 📌 설치 및 적용 가이드

### 1단계: 모듈 설치
1. `modules/roulette` 폴더를 서버의 `modules/` 경로에 업로드합니다.
2. 관리자 페이지 > **모듈/애드온** > **룰렛 이벤트** 항목을 찾아 **[업데이트]** 버튼을 클릭합니다.

### 2단계: 모달(팝업) 스크립트 등록 (필수)
이 모듈은 깔끔한 **레이어 팝업** 형태로 작동하도록 설계되었습니다.
관리자 페이지 > **사이트 디자인 설정** > **레이아웃(또는 헤더/푸터) 스크립트** 설정란에 아래 코드를 추가하세요.

**[포함된 기능]**:
1. 레이지 로딩 (Lazy Loading): 클릭 전까지 데이터 로딩 0.
2. 자동 숨김: 팝업이 뜨면 '통합관리자', '포스트잇' 등의 버튼을 잠시 숨김.

```javascript
<!-- 룰렛 모달 시스템 (Luxury Gold + 관리자 버튼 숨김) -->
<script>
jQuery(document).ready(function($) {
    // 1. 팝업용 HTML 생성
    var modalHtml = 
    '<div id="roulette-layer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">' +
        // 배경색: #fcf9f2 (연한 아이보리) / 테두리: #D4AF37 (골드)
        '<div style="position:relative; width:400px; max-width:95%; height:650px; max-height:85%; background:#fcf9f2; border-radius:25px; overflow:hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25); border: 2px solid #D4AF37;">' +
             // 닫기 버튼: 누르면 closeRouletteLayer 실행
             '<button onclick="closeRouletteLayer();" style="position:absolute; top:15px; right:15px; z-index:100; background:rgba(212, 175, 55, 0.2); border:none; width:32px; height:32px; border-radius:50%; color:#5c450a; font-size:20px; cursor:pointer; font-weight:bold; display:flex; align-items:center; justify-content:center; transition:0.2s;">&times;</button>' +
            // 룰렛 화면 불러오기 (iframe)
            '<iframe src="" id="roulette-frame" style="width:100%; height:100%; border:none; background:transparent;" scrolling="no"></iframe>' +
        '</div>' +
    '</div>';
    
    // 2. 만약 이미 존재하면 지우고 다시 생성 (중복 방지)
    if($('#roulette-layer').length > 0) $('#roulette-layer').remove();
    $('body').append(modalHtml);
    
    // 3. 팝업 열기 함수
    window.openRouletteLayer = function() {
        var frame = $('#roulette-frame');
        // Lazy Loading: 처음 열 때만 주소를 로딩 (서버 부하 방지)
        if(frame.attr('src') === "") {
            frame.attr('src', '/?act=dispRouletteIndex&mode=modal'); 
        }
        $('#roulette-layer').css('display', 'flex');
        
        // [중요] 팝업 뜰 때 화면 가리는 관리자 버튼/위젯들 잠시 숨기기
        $('#rhymix_admin_bar, .x_btn-primary, .postit-toggle, #dok_sticker').hide();
    };

    // 4. 팝업 닫기 함수
    window.closeRouletteLayer = function() {
        $('#roulette-layer').hide();
        
        // [복구] 숨겼던 관리자 버튼들 다시 보이기
        $('#rhymix_admin_bar, .x_btn-primary, .postit-toggle, #dok_sticker').show();
    };
});
</script>
```

### 3단계: 메뉴 및 버튼 연결
어디서든 `openRouletteLayer()` 함수를 호출하면 룰렛이 뜹니다.

- **메뉴 연결**: 메뉴 편집 > 외부 링크 URL에 `javascript:openRouletteLayer();` 입력
- **버튼 연결**: `<button onclick="openRouletteLayer()">룰렛 하기</button>`

---

## ⚡ 자주 묻는 질문 (FAQ)

### Q. 사이트 속도가 느려지지 않나요?
**A. 전혀 느려지지 않습니다! (Lazy Loading 적용)**
보통 아이프레임(Iframe)은 페이지 로딩 속도를 저하시키는 주범입니다. 하지만 이 모듈은 **"Lazy Loading(지연 로딩)"** 기술을 사용합니다.
- 사이트 접속 시: 빈 껍데기(`src=""`)만 생성하므로 로딩 시간이 **0초**입니다.
- 버튼 클릭 시: 그제서야 룰렛 주소를 연결하므로, **사용자가 원할 때만** 데이터를 사용합니다.

### Q. '통합관리자' 버튼이 모달 위로 튀어나와요.
**A. 자동 숨김 기능이 해결해줍니다.**
위의 스크립트에는 팝업이 열릴 때 `#rhymix_admin_bar`, `.x_btn-primary` 등을 자동으로 숨기는 코드가 포함되어 있습니다. 별도의 설정 없이 스크립트만 잘 넣으시면 됩니다.

---

## 🛠 제작 및 개발 워크플로우 (Development Workflow)

### 1️⃣ 분석 및 설계 (Analysis & Design)
- **목표**: 기존 HTML/JS 프로토타입을 라이믹스 모듈로 이식.
- **설계**: `View`(화면)와 `Controller`(로직) 분리, `config.js`의 JSON 데이터를 DB화.

### 2️⃣ 핵심 기능 구현
- **관리자 UI**: 엑셀 형태의 입력 테이블을 통해 `items_json`을 쉽게 편집하도록 구현.
- **DB 연동**: `module_config`에 설정 저장, `roulette_log`에 당첨 내역 저장.
- **모달(Modal)**: `layout=none` 뷰 모드를 추가하고, 외부 간섭을 차단하기 위해 **Iframe** 방식 채택.

### 3️⃣ 디자인 고도화 (Luxury Gold)
- **테마 변경**: 칙칙한 네온 스타일 -> **Luxury Gold (화이트/골드)** 테마로 전면 교체.
- **디테일업**:
    - **그라데이션**: 밋밋한 단색 대신 아이보리빛 그라데이션 적용.
    - **3D 효과**: 골드 링 테두리와 입체적인 그림자 추가.
    - **UI 최적화**: 결과창 중앙 정렬, 화살표 위치 미세 조정, 불필요한 관리자 버튼 제거.

---
**최종 업데이트**: 2026-02-04
