<?php

require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/libs/Smarty.class.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/Conf.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/Functions.php';

require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/dao/SeatDAO.class.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/entity/Seat.class.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/dao/ReserveDAO.class.php';
require_once $_SERVER['DOCUMENT_ROOT']. '/hal_cinema/classes/entity/Reserve.class.php';
require_once $_SERVER["DOCUMENT_ROOT"]. '/hal_cinema/classes/entity/MovieSchedule.class.php';
require_once $_SERVER["DOCUMENT_ROOT"]. '/hal_cinema/classes/dao/MovieScheduleDAO.class.php';

$smarty = new Smarty();
$smarty->setTemplateDir($_SERVER['DOCUMENT_ROOT'] . "/hal_cinema/templates/");
$smarty->setCompileDir($_SERVER['DOCUMENT_ROOT'] . "/hal_cinema/templates_c/");

$tplPath = "reserve/seat.tpl";
$scheduleId = "1";
if(isset($_GET["scheduleId"])) {
    $scheduleId = $_GET["scheduleId"];
}
//if(loginCheck()) {
//    $validationMsgs[] = "ログインしていないか、前回ログインしてから一定時間が経過しています。もう一度ログインし直してください。";
//    $smarty->assign("validationMsgs", $validationMsgs);
//    $tplPath = "login.tpl";
//} else {

    try {
        $db = new PDO(DB_DNS, DB_USERNAME, DB_PASSWORD);

        // シアターID取得
        $movieScheduleDAO = new MovieScheduleDAO($db);
        $movieSchedule = null;
        $movieSchedule = $movieScheduleDAO->findByPK($scheduleId);
        $smarty->assign("movieSchedule", $movieSchedule);

        // 予約情報
        $reserveDAO = new ReserveDAO($db);
        $reserves = array();
        $reserves = $reserveDAO->findByScheduleId($scheduleId);

        // 座席情報
        $seatDAO = new SeatDAO($db);
        $seats = array();
        $seats = $seatDAO->findByTheaterId($scheduleId);

        // 予約情報
        $reserveDAO = new ReserveDAO($db);
        $reserves = array();
        $reserves = $reserveDAO->findByScheduleId($scheduleId);
        $reservesArray = array();
        foreach ($reserves as $reserve) {
            $reservesArray[] = $reserve->getSeat();
        }
        $smarty->assign("reserves", json_encode($reservesArray));

        // HACK 便宜的に小文字でインクリメントしているが、出来れば大文字でインクリメントした方がスマート
        $seatRowAlphabet = 'a';
        // AAAAAAAAAなどの１列の席情報を格納する
        $seatRow = "";
        // View側のjsのためのmap情報
        $seatMap = array();
        foreach ($seats as $seat) {
            // TODO 現状はA~Zまでしか許容していない。AAまで席が増える場合は対応必要
            if(strtoupper($seatRowAlphabet) != substr($seat->getSeat(), 0, 1)) {
                $seatColumns[] = strtoupper($seatRowAlphabet);
                $seatRowAlphabet++;
                $seatMap[] =  strtoupper($seatRow);
                $seatRow = "";
            }
            $seatRow .= $seatRowAlphabet;
        }
        $seatMap[] = strtoupper($seatRow);
        $seatColumns[] = strtoupper($seatRowAlphabet);

        $smarty->assign("scheduleId", $scheduleId);
        $smarty->assign("seatColumns", json_encode($seatColumns));
        $smarty->assign("seatMap", json_encode($seatMap));

    } catch (PDOException $ex) {
        print_r($ex);
        $smarty->assign("errorMsg", "DB接続に失敗しました。");
        $tplPath = "error.tpl";
    } finally {
        $db = null;
    }
//    $smarty->display($tplPath);
//}


$smarty->display($tplPath);
