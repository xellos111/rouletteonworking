# 🎰 라이믹스 룰렛 모듈 (Rhymix Roulette Module)
> **Luxury Gold 테마**가 적용된 이벤트 룰렛 모듈

## 📌 설치 및 적용 가이드

### 1단계: 모듈 설치
1. `modules/roulette` 폴더를 서버의 `modules/` 경로에 업로드합니다.
2. 관리자 페이지 > **모듈/애드온** > **룰렛 이벤트** 항목을 찾아 **[업데이트]** 버튼을 클릭합니다.

### 2단계: 모달(팝업) 스크립트 등록 (필수)
이 모듈은 깔끔한 **레이어 팝업** 형태로 작동하도록 설계되었습니다.
관리자 페이지 > **사이트 디자인 설정** > **레이아웃(또는 헤더/푸터) 스크립트** 설정란에 아래 코드를 추가하세요.

```javascript
<!-- 룰렛 모달 시스템 (Luxury Gold 적용됨) -->
<script>
jQuery(document).ready(function($) {
    // 1. 팝업용 HTML 생성 (아이보리 배경 + 골드 테두리)
    var modalHtml = 
    '<div id="roulette-layer" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(4px);">' +
        // 배경색: #fcf9f2 (연한 아이보리) / 테두리: #D4AF37 (골드)
        '<div style="position:relative; width:400px; max-width:95%; height:650px; max-height:85%; background:#fcf9f2; border-radius:25px; overflow:hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25); border: 2px solid #D4AF37;">' +
             // 닫기 버튼: 골드 톤 디자인
             '<button onclick="jQuery(\'#roulette-layer\').hide();" style="position:absolute; top:15px; right:15px; z-index:100; background:rgba(212, 175, 55, 0.2); border:none; width:32px; height:32px; border-radius:50%; color:#5c450a; font-size:20px; cursor:pointer; font-weight:bold; display:flex; align-items:center; justify-content:center; transition:0.2s;">&times;</button>' +
            // 룰렛 화면 불러오기 (iframe)
            '<iframe src="" id="roulette-frame" style="width:100%; height:100%; border:none; background:transparent;" scrolling="no"></iframe>' +
        '</div>' +
    '</div>';
    
    // 2. 만약 이미 존재하면 지우고 다시 생성 (중복 방지)
    if($('#roulette-layer').length > 0) $('#roulette-layer').remove();
    $('body').append(modalHtml);
    
    // 3. 전역 호출 함수 등록 (window.openRouletteLayer)
    window.openRouletteLayer = function() {
        var frame = $('#roulette-frame');
        // 처음 열 때만 주소 로딩 (부하 방지)
        if(frame.attr('src') === "") {
            frame.attr('src', '/?act=dispRouletteIndex&mode=modal'); 
        }
        $('#roulette-layer').css('display', 'flex'); // 팝업 보이기
    };
});
</script>
```

### 3단계: 메뉴 및 버튼 연결
어디서든 `openRouletteLayer()` 함수를 호출하면 룰렛이 뜹니다.

- **메뉴 연결**: 메뉴 편집 > 외부 링크 URL에 `javascript:openRouletteLayer();` 입력
- **버튼 연결**: `<button onclick="openRouletteLayer()">룰렛 하기</button>`

---

## 🛠 제작 및 개발 워크플로우 (Development Workflow)

이 모듈이 어떻게 완성되었는지 작업 과정을 기록합니다.

### 1️⃣ 분석 및 설계 (Analysis & Design)
- **기존 코드 분석**: HTML/JS로 된 단일 페이지 룰렛 소스를 분석하여 라이믹스 모듈 구조(MVC 패턴)로 변환을 계획함.
- **구조 설계**:
    - `Controller`: 포인트 차감, 확률 계산, 아이템 지급 로직 담당.
    - `View`: 사용자에게 룰렛 화면 출력 담당.
    - `Model`: DB 설정값 및 로그 처리 담당.

### 2️⃣ 관리자 페이지 구현 (Admin UI)
- **문제 해결**: 초기에는 복잡한 JSON 코드를 직접 입력해야 해서 오타 발생 위험이 높았음.
- **해결책**: 엑셀처럼 직관적인 **테이블 형태의 설정 UI**(`tpl/index.html`)를 개발.
    - 자바스크립트로 행 추가/삭제/수정 기능 구현.
    - 입력된 값을 자동으로 JSON으로 변환하여 안전하게 저장하는 로직 적용.

### 3️⃣ 데이터베이스 연동 (Database)
- **설정 저장**: 라이믹스 공용 테이블 `module_config`를 활용하여 모듈 설정을 저장.
- **로그 저장**: 게임의 신뢰성을 위해 `roulette_log` 테이블을 신규 생성.
    - 유저(`member_srl`), 당첨 아이템, 포인트 변동 내역 등을 기록.

### 4️⃣ 모달(Modal) 기능 구현
- **요구사항**: 페이지 이동 없이 현재 화면 위에서 깔끔하게 뜨기를 원함.
- **구현**:
    - `roulette.view.php`에 `&mode=modal` 파라미터를 추가하여, 호출 시 레이아웃(헤더/푸터)을 제거하고 순수 룰렛 내용만 출력하도록 함.
    - 브라우저의 `window.open` 팝업은 주소창이 보여 지저분하므로, **iframe 레이어 팝업 방식**을 채택하여 사이트 디자인과 일체감을 줌.

### 5️⃣ 테마 디자인 고도화 (Theming)
- **Dark Theme (초기)**: 네온 스타일의 어두운 테마였으나 사이트 분위기와 이질감 발생.
- **Light Theme**: 사이트 배경에 맞는 흰색 기반의 깔끔한 디자인으로 변경.
- **Luxury Gold Theme (최종)**:
    - 단순한 흰색이 심심하다는 피드백 반영.
    - 골드 그라데이션(`Linear Gradient`)과 3D 효과를 주는 그림자(`Box Shadow`) 적용.
    - 타이틀과 버튼에 애니메이션 효과 추가로 고급스러움 강조.
    - 모달 창과 내부 아이프레임의 배경색을 통합하여 이질감 완전 제거.

---
**작성일**: 2026-02-04
