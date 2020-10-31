//script должен вызываться ежеминутно. у меня это в onnewminute
//обработаем задачи

if (file_exists('./modules/app_calendar/app_calendar.class.php')) 
{ 
    include_once('./modules/app_calendar/app_calendar.class.php'); 
    $calendar = new app_calendar(); 
    $sql = "SELECT * FROM `calendar_events` WHERE `IS_TASK`=1 and `IS_DONE`=0 and `AUTODONE`=1 and IS_NODATE<>1
            and date_FORMAT(`DUE`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
     $tasks = SQLSelect($sql);
     $total = count($tasks);
     for ($i = 0; $i < $total; $i++) {
       debmes('task autodone finded -'.$tasks[$i]['TITLE'].' Done it!',"calendar");
       $id = $tasks[$i]['ID'];
       if($id){
         $calendar -> task_done($id, 0);
       }
     }
    //закроем окончившиеся события и вычислим новые для повторов
    $sql = "SELECT * FROM `calendar_events` WHERE `IS_TASK`=0 and `IS_DONE`=0 and IS_NODATE<>1
            and date_FORMAT(`END_TIME`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
     $tasks = SQLSelect($sql);
     $total = count($tasks);
     for ($i = 0; $i < $total; $i++) {
       debmes('event done finded -'.$tasks[$i]['TITLE'].'. update it',"calendar");
       $id = $tasks[$i]['ID'];
       if($id){
         $calendar -> task_done($id, 0);
       }
     }
    //закроем окончившиеся по времени повторяющиеся задачи и вычислим новые для повторов
    $sql = "SELECT * FROM `calendar_events` WHERE `IS_TASK`=1 and `IS_DONE`=0 and IS_REPEATING = 1
            and date_FORMAT(`END_TIME`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
     $tasks = SQLSelect($sql);
     $total = count($tasks);
     for ($i = 0; $i < $total; $i++) {
       debmes('repeat task done finded -'.$tasks[$i]['TITLE'].'. update it by ending time',"calendar");
       $id = $tasks[$i]['ID'];
       if($id){
         $calendar -> task_done($id, 1);//запустим обновление без запусков скриптов
       }
     }
    //напоминалки
    $sql = "SELECT * FROM `calendar_events` WHERE `IS_REMIND`=1 
            and date_FORMAT(`REMIND_TIME`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
     $tasks = SQLSelect($sql);
     $total = count($tasks);
     for ($i = 0; $i < $total; $i++) {
       debmes('reminder finded -'.$tasks[$i]['TITLE'].'. process it',"calendar");
       $id = $tasks[$i]['ID'];
       if($id){
         $calendar -> process_remind($id);
       }
     }

}