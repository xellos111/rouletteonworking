<?php
class roulette extends ModuleObject
{
    private $triggers = array();

    public function moduleInstall()
    {
        return new BaseObject();
    }

    public function checkUpdate()
    {
        // 모듈 설정 갱신을 위해 무조건 true 반환 (개발 중)
        // 실제 배포 시에는 info.xml의 version과 비교하거나 module.xml 변경 감지 로직 필요
        return true;
    }

    public function moduleUpdate()
    {
        return new BaseObject();
    }
}
