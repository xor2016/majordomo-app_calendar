<?php
/**
* Calendar 
*
* App_calendar
*
* @package project
* @author Serge J. <jey@tut.by>
* @copyright http://www.atmatic.eu/ (c)
* @version 0.1 (wizard, 17:05:45 [May 07, 2012])
* задача - есть начало и конец. авто закрывается по началу, просроченная - по концу
* событие - есть начало и конец. авто закрывается по концу
*/
Define('DEF_REPEAT_TYPE_OPTIONS', '1=Yearly|2=Monthly|3=Weekly|4=Daily'); // options for 'REPEAT_TYPE'
//
//
class app_calendar extends module {
/**
* app_calendar
*
* Module class constructor
*
* @access private
*/
function app_calendar() {
  $this->name="app_calendar";
  $this->title="<#LANG_APP_CALENDAR#>";
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
  $this->checkInstalled();
}

/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->data_source)) {
  $p["data_source"]=$this->data_source;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $data_source;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($data_source)) {
   $this->data_source=$data_source;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }
}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
 
   
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }

  $this->checkSettings();

  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['DATA_SOURCE']=$this->data_source;
  $out['TAB']=$this->tab;
  if ($this->single_rec) {
   $out['SINGLE_REC']=1;
  }
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;
}

/**
* Title
*
* Description
*
* @access public
*/
 function checkSettings() {
  
  $settings=array(
   array(
    'NAME'=>'APP_CALENDAR_SOONLIMIT', 
    'TITLE'=>'Сколько дней показывать в "Скоро"', 
    'TYPE'=>'text',
    'DEFAULT'=>'14'
    ),
   array(
    'NAME'=>'APP_CALENDAR_SHOWDONE', 
    'TITLE'=>'Показывать недавно выполненные дела',
    'TYPE'=>'yesno',
    'DEFAULT'=>'1'
    ),
   array(
    'NAME'=>'APP_CALENDAR_SHOWCALENDAR', 
    'TITLE'=>'Показывать календарь в Делах и Событиях',
    'TYPE'=>'yesno',
    'DEFAULT'=>'1'
    ),
   array(
    'NAME'=>'APP_CALENDAR_REMINDERTIME', 
    'TITLE'=>'Стандартное время напоминания', 
    'TYPE'=>'text',
    'DEFAULT'=>'10:15'
    ),
   array(
    'NAME'=>'APP_CALENDAR_LOGGING', 
    'TITLE'=>'Логгирование', 
    'TYPE'=>'yesno',
    'DEFAULT'=>'1'
    ),
   );


   foreach($settings as $k=>$v) {
    $rec=SQLSelectOne("SELECT ID FROM settings WHERE NAME='".$v['NAME']."'");
    if (!$rec['ID']) {
     $rec['NAME']=$v['NAME'];
     $rec['VALUE']=$v['DEFAULT'];
     $rec['DEFAULTVALUE']=$v['DEFAULT'];
     $rec['TITLE']=$v['TITLE'];
     $rec['TYPE']=$v['TYPE'];
     $rec['DATA']=$v['DATA'];
     $rec['ID']=SQLInsert('settings', $rec);
     Define('SETTINGS_'.$rec['NAME'], $v['DEFAULT']);
    }
   }

 }

/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
if ($this->view_mode<>"")debmes('class admin -view_mode '.$this->view_mode);
if ($this->mode<>"")debmes('class admin -mode '.$this->mode);
if ($this->datasource<>"")debmes('class admin -datasource '.$this->datasource);
if ($_GET['data_source']<>"")debmes('class admin -get datasource '.$_GET['data_source']);
if ($_POST['data_source']<>"")debmes('class admin -post datasource '.$_POST['data_source']);

 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='calendar_events' || $this->data_source=='') {
  if ($this->view_mode=='' || $this->view_mode=='search_calendar_events') {
   $this->search_calendar_events($out);
  }
  if ($this->view_mode=='edit_calendar_events') {
//   $this->edit_calendar_events($out, $this->id);
     $this->usual_edit($out, $this->id);
   }
  if ($this->view_mode=='delete_calendar_events') {
   $this->delete_calendar_events($this->id);
   $this->redirect("?data_source=calendar_events");
  }
  if ($this->view_mode=='delete_all_task') {
   $this->delete_all_task();
   $this->redirect("?data_source=calendar_events");
  }
 if ($this->view_mode=='delete_all_past_events') {
   $this->delete_all_past_events();
   $this->redirect("?data_source=calendar_events");
  }


 }
 if ($this->data_source=='calendar_full') 
   $this->calendar_full($out);
 if (isset($this->data_source) && !$_GET['data_source'] && !$_POST['data_source']) {
  $out['SET_DATASOURCE']=1;
 }
 if ($this->data_source=='calendar_categories') {
  if ($this->view_mode=='' || $this->view_mode=='search_calendar_categories') {
   $this->search_calendar_categories($out);
  }
  if ($this->view_mode=='edit_calendar_categories') {
   $this->edit_calendar_categories($out, $this->id);
  }
  if ($this->view_mode=='delete_calendar_categories') {
   $this->delete_calendar_categories($this->id);
   $this->redirect("?data_source=calendar_categories");
  }
 }
}
/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
if ($this->view_mode<>"")debmes('class usual -view_mode '.$this->view_mode);
if ($this->mode<>"")debmes('class usual -mode '.$this->mode);
if ($this->datasource<>"")debmes('class usual -datasource '.$this->datasource);
if ($_GET['data_source']<>"")debmes('class usual -get datasource '.$_GET['data_source']);
if ($_POST['data_source']<>"")debmes('class usual -post datasource '.$_POST['data_source']);
 if ($this->view_mode=='edit') {
  $this->usual_edit($out,$id);
 }

 if ($this->view_mode=='') {
  

  if ($this->mode=='is_done') {
   global $id;
   $this->task_done($id);
   $this->redirect("?");
  }

  if ($this->mode=='reset_done') {
   global $id;

   $rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
   $rec['IS_DONE']=0;
   $rec['DONE_WHEN'] = null;
   SQLUpdate('calendar_events', $rec);

   $this->redirect("?");
  }


  if (defined('TEMP_APP_CALENDAR_SHOW_CALENDAR')==false and SETTINGS_APP_CALENDAR_SHOWCALENDAR==1) { 
   $m=date('m');
   $m1=$m+1;

   if ($_GET['year_calendar']==1) {
    $out['YEAR_CALENDAR']=1;
    $m=1; 
    $m1=12; 
   } else {
    if (IsSet($this->currentmonth)) {
     $m1=(int)date('m',time());
     $m2=$m1;
    } else { 
     if (IsSet($this->mon1)) {
      $m=(int)$this->mon1;
     } 
    
     if (IsSet($this->mon2)) {
      $m1=(int)$this->mon2;
     }
    }  
   }
   $this->calendar_full($out,$m,$m1);
   $out['SHOW_CALENDAR']=1;
  }

  if (IsSet($this->calendar) or $_GET['calendar']==1)
   $out['ONLY_CALENDAR']=1;
  else
 {

  $events_today = SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time, calendar_events.*, calendar_categories.ICON, (SELECT COUNT( d.ID ) FROM calendar_events d WHERE d.parent_id = calendar_events.id ) IS_MAIN FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE TO_DAYS(DUE)<=TO_DAYS(NOW()) and END_TIME>=NOW() and IS_NODATE=0 AND IS_DONE=0 AND ifnull(AT_CALENDAR,1)!=0 ORDER BY DUE");

  if ($events_today) {
   $out['EVENTS_TODAY']=$events_today;
   $out['EVENTS_TODAY_REC_COUNT'] = count($events_today);
  }

//no date with progress
$events_nodate = SQLSelect("SELECT DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time, calendar_events . * , calendar_categories.ICON, (SELECT COUNT( d.ID ) FROM calendar_events d WHERE d.parent_id = calendar_events.id ) IS_MAIN, (  SELECT round(SUM( c.IS_DONE ) * 100 / COUNT( c.ID )) FROM calendar_events c WHERE c.PARENT_ID = calendar_events.ID ) PR FROM calendar_events calendar_events LEFT JOIN calendar_categories ON calendar_events.calendar_category_id = calendar_categories.id WHERE IS_NODATE=1 AND IS_DONE=0 AND ifnull(AT_CALENDAR,1)!=0  ORDER BY TITLE");

  if ($events_nodate) {
   $out['EVENTS_NODATE']=$events_nodate;
   $out['EVENTS_NODATE_REC_COUNT'] = count ($events_nodate);
  }
//tomoroow
  $events_tomorrow = SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%H:%i' ) due_time,calendar_events.*,calendar_categories.ICON, (SELECT COUNT( d.ID ) FROM calendar_events d WHERE d.parent_id = calendar_events.id ) IS_MAIN FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE  TO_DAYS(DUE)=TO_DAYS(NOW())+1 and TO_DAYS(END_TIME)>=TO_DAYS(NOW())+1 and IS_NODATE=0 AND IS_DONE=0 AND ifnull(AT_CALENDAR,1)!=0  ORDER BY DUE");

  if ($events_tomorrow) {
   $out['EVENTS_TOMORROW']=$events_tomorrow;
   $out['EVENTS_TOMORROW_REC_COUNT'] = count($events_tomorrow);
  }
////////////////////////////////////////////////////////////////////////
if (SETTINGS_APP_CALENDAR_SOONLIMIT) {
  $soon_limit = SETTINGS_APP_CALENDAR_SOONLIMIT;
}else{
  $soon_limit = 7;
}
  $events_after = SQLSelect("SELECT DATE_FORMAT( calendar_events.due, '%d.%m.%y %H:%i' ) due_time, DATE_FORMAT( calendar_events.due, '%d.%m.%y' ) due_date,calendar_events.*,calendar_categories.ICON, (SELECT COUNT( d.ID ) FROM calendar_events d WHERE d.parent_id = calendar_events.id ) IS_MAIN FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE  TO_DAYS(DUE)>=TO_DAYS(NOW())+2 and TO_DAYS(END_TIME)>=TO_DAYS(NOW())+2 and IS_NODATE=0 AND IS_DONE=0 and TO_DAYS(DUE)<=TO_DAYS(NOW())+ $soon_limit  AND ifnull(AT_CALENDAR,1)!=0  ORDER BY DUE");

  if ($events_after) {
   $out['EVENTS_AFTER']=$events_after;
   $out['EVENTS_AFTER_REC_COUNT'] = count ($events_after);
  }

//просроченная задача
  $events_overdue = SQLSelect("SELECT DATE_FORMAT( calendar_events.due, '%d.%m.%y %H:%i' ) due_time,calendar_events.*,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE DUE < NOW() AND END_TIME< NOW() AND IS_DONE=0 AND IS_NODATE=0 AND IS_TASK=1  AND ifnull(AT_CALENDAR,1)!=0 ORDER BY DUE");

  if ($events_overdue) {
   $out['EVENTS_OVERDUE']=$events_overdue;
   $out['EVENTS_OVERDUE_REC_COUNT'] = count ($events_overdue);
  }
//все записи
  $events_all = SQLSelect("SELECT  case when IS_NODATE=0 then DATE_FORMAT( calendar_events.due, '%d.%m.%y %H:%i' ) else null end  due_time, calendar_events.*,calendar_categories.ICON,calendar_categories.title CAT_NAME  FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE 1=1 ORDER BY IS_NODATE desc, DUE desc");

  if ($events_all) {
   $out['EVENTS_ALL']=$events_all;
   //$out['EVENTS_ALL_REC_COUNT'] = count ($events_all);
  }

  if (SETTINGS_APP_CALENDAR_SHOWDONE=='1') {
   $recently_done=SQLSelect("SELECT  DATE_FORMAT( calendar_events.due, '%d.%m.%y %H:%i' ) as due_time, calendar_events.*, calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id  WHERE IS_TASK=1 AND (IS_DONE=1 OR IS_REPEATING=1) AND TO_DAYS(NOW())-TO_DAYS(DONE_WHEN)<=1")  ;
   if ($recently_done) {
    $out['RECENTLY_DONE']=$recently_done;
    $out['RECENTLY_DONE_REC_COUNT'] = count($recently_done);
   }
  }
  $out['CATEGORIES'] = SQLSelect("SELECT * from calendar_categories ORDER BY TITLE");

 }
 }

}

/**
* Title
*
* Description
*
* @access public
*/
 function usual_edit(&$out, $id) {

  global $title;
  global $id;

  if ($id) {
	$rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");

	if ($this->mode=='delete') {
		SQLExec("DELETE FROM calendar_events WHERE ID='".(int)$rec['ID']."'");
		//освободим подчиненные задачи
		SQLExec("UPDATE calendar_events SET PARENT_ID=0 WHERE PARENT_ID='".$rec['ID']."'");
	$this->redirect("?");
}

  } else { 
	//add new by title
	$out['TITLE']=$title;
	$out['DUE'] = date('Y-m-d H:i:00');
	$out['END_TIME'] = date('Y-m-d H:i:00');
	$out['REMIND_TIME']= date('Y-m-d H:i:00');
	$out['IS_TASK'] = 0;
	$out['IS_NODATE'] = 0;
	$out['ALL_DAY'] = 0;
	$out['IS_REPEATING'] = 0;
	$out['LOG'] = '';

  }

  if ($this->mode=='update') {
   $ok=1;

   global $is_task;
   global $notes;

   $rec['TITLE']=$title;

   if (!$rec['TITLE']) {
    $ok=0;
    $out['ERR_TITLE']=1;
   }

   $rec['IS_TASK']=(int)$is_task;
   $rec['NOTES']=$notes;

   global $due; //начало события 
   $rec['DUE'] = $due;
   if (!$rec['DUE'] ) {
    $rec['DUE']=date('Y-m-d H:i'.':00');
   }

   global $end_time; //конец события
   $rec['END_TIME'] = $end_time;
   if (!$rec['END_TIME']) {
    $rec['END_TIME']=$rec['DUE'];
   }
   
   global $is_repeating;
   $rec['IS_REPEATING']=(int)$is_repeating;

   global $is_repeating_after;
   $rec['IS_REPEATING_AFTER']=(int)$is_repeating_after;

   global $repeat_in;
   $rec['REPEAT_IN']=(int)$repeat_in;

   global $repeat_type;
   $rec['REPEAT_TYPE']=(int)$repeat_type;
   
   global $is_done;
   if ($is_done && !$rec['IS_DONE']) {
    $marked_done=1;
   }
   $rec['IS_DONE']=(int)$is_done;

   global $is_nodate; //сложно - без указания даты - всегда - спец. обработка(
   $rec['IS_NODATE']=(int)$is_nodate;
   if ($is_nodate) {
    $rec['IS_REPEATING']=0; //ignore sets
    $rec['ALL_DAY']=0;      
   }

   global $all_day; //на весь день - с 00:00 до 23:59
   $rec['ALL_DAY'] = (int)$all_day;
   if($all_day){
     $rec['DUE'] = date('Y-m-d',strtotime($rec['DUE'])).' 00:00:00';
     $rec['END_TIME'] = date('Y-m-d',strtotime($rec['END_TIME'])).' 23:59:00';
   }

   global $user_id;
   $rec['USER_ID']=(int)$user_id;

   global $calendar_category_id;
   $rec['CALENDAR_CATEGORY_ID']=(int)$calendar_category_id;
	
   global $done_script_id;
   $rec['DONE_SCRIPT_ID']=(int)$done_script_id;
   ////////////////////////////////
   global $done_code;
   $rec['DONE_CODE'] = $done_code;

   global $is_remind; //напоминание есть
   $rec['IS_REMIND'] = (int)$is_remind; 

   global $remind_time; //его время рассчитанное/указаное
   $rec['REMIND_TIME'] = $remind_time;
   if (!$rec['REMIND_TIME'] ) {
    //$rec['REMIND_TIME'] = $rec['DUE']; //todo
   }

   global $remind_type; // сказать/выполнить код
   $rec['REMIND_TYPE'] = $remind_type;

   global $remind_code; 
   $rec['REMIND_CODE'] = $remind_code;

   global $week_days;
   $rec['WEEK_DAYS'] = @implode(',', $week_days);
   if (is_null($rec['WEEK_DAYS'])) {
        $rec['WEEK_DAYS'] = '';
    }
   global $autodone; //для задач - автоматическое завершение при начале
   $rec['AUTODONE'] = $autodone;
 
   global $remind_in;//напомнить за remind_in мин/час/дней ???
   $rec['REMIND_IN'] = $remind_in;

   global $remind_timer; // 0..10 мин/час/дней/как явно указано в REMIND_TIME
   $rec['REMIND_TIMER'] = $remind_timer;
   if($remind_timer < 10) {  
     $delta = array(0 => 5*60,
                  1 => 15*60,
                  2 => 30*60,
                  3 => 45*60,
                  4 => 60*60,
                  5 => 2*60*60,
                  6 => 8*60*60,
                  7 => 12*60*60,
                  8 => 24*60*60,
                  9 => 48*60*60,
                  );
     $remd = strtotime($rec['DUE']) - $delta[$remind_timer];
     if($remd < time()) $remd = $remd + 60; //must be in future
     $rec['REMIND_TIME'] = date('Y-m-d H:i'.':00',$remd);

   }
   global $parent_id;
   $rec['PARENT_ID'] = $parent_id;
   global $autodone_by_childs;
   $rec['AUTODONE_BY_CHILDS'] = $autodone_by_childs;

   global $log;
   $rec['LOG'] = $log;
////////////////////////////////
}
   if ($ok) {
    if ($rec['ID']) {
     SQLUpdate('calendar_events', $rec);
    } else {
     $rec['ADDED']=date('Y-m-d H:i:s');
     $rec['ID']=SQLInsert('calendar_events', $rec);
    }
    if ($marked_done) {
     $this->task_done($rec['ID']);
    }

    $this->redirect("?");
   }


  //}


  outHash($rec, $out);
  $out['DONE_WHEN'] = date('d.m.Y H:i:00',strtotime($rec['DONE_WHEN']));
  $out['USERS'] = SQLSelect("SELECT * FROM users ORDER BY NAME");
  //$out['LOCATIONS']=SQLSelect("SELECT * FROM gpslocations ORDER BY TITLE");
  $out['SCRIPTS'] = SQLSelect("SELECT ID, TITLE FROM scripts ORDER BY TITLE");
  $out['CALENDAR_CATEGORIES'] = SQLSelect("SELECT ID, TITLE from calendar_categories ORDER BY TITLE");
//обработка дней недели
$w_days = array();
if ($rec['WEEK_DAYS']!=='') {
  $w_days = explode(',', $rec['WEEK_DAYS']);
}

$days = array( 1=>"Пн","Вт","Ср","Чт","Пт","Сб","Вс");
	for ($i = 1; $i < 8; $i++) {
	    $out['WDAYS'][] = array(
	         'VALUE'    => $i,
	         'DNAME'    => $days[$i],
	         'SELECTED' => (in_array($i, $w_days))?1:0,
	   );
	    
	}
  if ($out['ID']) {
    //подчиненные задачи + признак просрочки - overdue
    $out['OTHERS'] = SQLSelect("SELECT ID, TITLE, IS_DONE, case when DUE < NOW() AND END_TIME< NOW() AND IS_DONE=0 AND IS_NODATE=0 then '1' else '0' end  OVERDUE FROM calendar_events WHERE PARENT_ID=".$out['ID']." ORDER BY TITLE");
    $out['OTHERS_REC_COUNT'] = count($out['OTHERS']);
    //progress main ???
    if($out['OTHERS_REC_COUNT']>0) {
      $rec = SQLSelectOne( "SELECT sum(IS_DONE)*100/count(ID) PR FROM calendar_events WHERE PARENT_ID=".$out['ID']);
      $out['PROGRESS'] = round($rec['PR']);
    }else{
      $out['PROGRESS'] = 0;
    }
	//список задач для выбора главной
    $out['FOR_LINKED_TASKS'] = SQLSelect("SELECT calendar_events.`ID`, calendar_events.`TITLE`,calendar_events.`DUE`,calendar_events.`PARENT_ID` ,calendar_categories.ICON FROM calendar_events left join calendar_categories on calendar_events.calendar_category_id=calendar_categories.id WHERE `IS_TASK`=1 and (`IS_DONE`=0 or `IS_REPEATING`=1) and calendar_events.`ID`<>".$out['ID']." order by `TITLE`");

    
  }
}



/**
* Title
*
* Description
*
* @access public
*/


 function task_done($id, $autoend = 0) {

  $rec = SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
   //снимем разовое напоминание для завершенной задачи
   //if($rec['REMIND_TIMER'] == 10) $rec['IS_REMIND'] = 0;

   if($autoend){ //завершилась по времени окончания (для нового повтора)
     $rec['DONE_WHEN'] = null;
     $rec['IS_DONE'] = 0;
	if('SETTINGS_APP_CALENDAR_LOGGING') debmes('repeated task "'.$rec['TITLE'].'" ended but not marked done, renew due date only','calendar');
	$rec['LOG'] = date('d.m.y H:i:s').' - завершено без исполнения, поставлен очередной срок';
   }else{ //задача была выполнена
     $rec['DONE_WHEN'] = date('Y-m-d H:i:s');
     $rec['IS_DONE'] = 1;
     if('SETTINGS_APP_CALENDAR_LOGGING') debmes('task "'.$rec['TITLE'].'" is done!','calendar');
	$rec['LOG'] = date('d.m.y H:i:s').' - завершено';
   }
  if ($rec['IS_REPEATING']) {
  
   $due_time = strtotime(date('Y-m-d H:i:00',strtotime($rec['DUE']))); //unixtime
   $end_time = strtotime(date('Y-m-d H:i:00',strtotime($rec['END_TIME']))); //unixtime

   //от греха поставим сегодня для пусто
   if(!$due_time) $due_time = $rec['ALL_DAY']?(strtotime(date('Y-m-d')." 00:00:00")):time();
   if(!$end_time) $end_time = $rec['ALL_DAY']?(strtotime(date('Y-m-d')." 23:59:00")):$due_time;
   $repeat_in = $rec['REPEAT_IN']?$rec['REPEAT_IN']:1;
   //найдем длительность для определения нового end_time
   $duration = $end_time - $due_time;
   $part_due = date_parse($rec['DUE']);
   
   $rec['IS_DONE']=0; //вернём для нового срока признак выполнения в 0
   if ($rec['REPEAT_TYPE'] == 1) {//годы
    // yearly task
   $due_time_next_year = mktime($part_due['hour'],$part_due['minute'],0,$part_due['month'],$part_due['day'],$part_due['year']+$repeat_in*1);
    $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_year);
    
    $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_year + $duration);
   } elseif ($rec['REPEAT_TYPE'] == 2) {//месяцы
    // monthly task
    $time_next_month = $due_time + $repeat_in*31*24*60*60;
    $due_time_next_month = mktime($part_due['hour'],$part_due['minute'],0, date('m', $time_next_month), $part_due['day'], date('Y', $time_next_month));
    $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_month);
    $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_month + $duration);

   } elseif ($rec['REPEAT_TYPE'] == 3) {//недели
     if(!$rec['WEEK_DAYS']){
       // weekly task
      $due_time_next_week = $due_time + 7*24*60*60;
      $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_week);
      $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_week + $duration);

      $rec['WEEK_DAYS'] = date("N", $due_time_next_week);//запишем на будущее чтобы галочка стояла(?)
     }else{
       //задача назначена на пн, вт 
       //если задача пн закрыта во вт, то следующий срок - пн
       $week_days = array();
	   if ($rec['WEEK_DAYS'] !== '') $week_days = explode(',', $rec['WEEK_DAYS']);
       $dd = time();
       $due = date("N", $dd); //переведём в формат пн=1...вс=7
       for($i = 0; $i < 7;$i++){
         $dd = $dd + $repeat_in*24*60*60; //след. дата
         $due = $due + 1;
         if($due > 7) $due = 1;
         if(in_array($due, $week_days)) { //первый следующий запуск
           //$next = $dd;
           break;
         }
       }
       $due_time_next_week = mktime($part_due['hour'],$part_due['minute'],0, date('m', $dd), date('j', $dd), date('Y', $dd));
       $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_week);
       $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_week + $duration);
     }
//todo
   } elseif ($rec['REPEAT_TYPE'] == 4) {//дни
       $due_time_next_day = $due_time + $repeat_in*24*60*60;
       $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_day);
       $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_day + $duration);

   } elseif ($rec['REPEAT_TYPE'] == 5) {//часы

       $due_time_next_hour = $due_time + $repeat_in*60*60;
       $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_hour);
       $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_hour + $duration);

   } elseif ($rec['REPEAT_TYPE'] == 6) {//минуты

       $due_time_next_minute = $due_time + $repeat_in*60;
       $rec['DUE'] = date('Y-m-d H:i:00', $due_time_next_minute);
       $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time_next_minute + $duration);
       //debmes('repeat_in '.$repeat_in.' task '.$rec['TITLE'],'calendar');

   } elseif ($rec['REPEAT_TYPE'] == 9) {//custom repeat task
    
    if ($rec['IS_REPEATING_AFTER']) {
     $rec['DUE'] = date('Y-m-d H:i:00', time() + $repeat_in*24*60*60);
     $rec['END_TIME'] = date('Y-m-d H:i:00', time() + $repeat_in*24*60*60 + $duration);
    } else {
     $rec['DUE'] = date('Y-m-d H:i:00', $due_time + $repeat_in*24*60*60);
     $rec['END_TIME'] = date('Y-m-d H:i:00', $due_time + $repeat_in*24*60*60 + $duration);
     
    }
  }
  //upd remind for repeat events/tasks
  if($rec['IS_REMIND'] && $rec['REMIND_TIMER']<10){
     $delta = array(0 => 5*60,
                  1 => 15*60,
                  2 => 30*60,
                  3 => 45*60,
                  4 => 60*60,
                  5 => 2*60*60,
                  6 => 8*60*60,
                  7 => 12*60*60,
                  8 => 24*60*60,
                  9 => 48*60*60,
                  );
     $remd = strtotime($rec['DUE']) - $delta[$remind_timer];
     if($remd < time()) $remd = $remd + 60; //must be in future
     $rec['REMIND_TIME'] = date('Y-m-d H:i'.':00',$remd);
  }

}


  SQLUpdate('calendar_events', $rec);
	  if(!$autoend){//без запусков кода при истечении срока повторяющейся задачи
		  if ($rec['DONE_SCRIPT_ID']) {
		   runScriptSafe($rec['DONE_SCRIPT_ID'], $rec);
		  }elseif($rec['DONE_CODE']){

			                        try {
			                            $code = $rec['DONE_CODE'];
			                            $success = eval($code);
			                            if ($success === false)
			                                DebMes("Error in Calendar Done code: " . $code);
			                        } catch (Exception $e) {
			                            DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
			                        }

		  }
	 }
 }

 //todo 
 function process_remind($id) {
  $rec = SQLSelectOne("SELECT * FROM calendar_events WHERE ID='".(int)$id."'");
  if($rec['ID']){
	  $rec['IS_REMIND'] = 0;
	  SQLUpdate('calendar_events', $rec);
	  if($rec['REMIND_TYPE']==0){
	    say("Напоминаю о событии " .$rec['TITLE'],2);
	  }else{
		if ($rec['REMIND_CODE']){
			                        try {
			                            $code = $rec['REMIND_CODE'];
			                            $success = eval($code);
			                            if ($success === false)
			                                DebMes("Error in Calendar Reminder code: " . $code);
			                        } catch (Exception $e) {
			                            DebMes('Error: exception ' . get_class($e) . ', ' . $e->getMessage() . '.');
			                        }
			                    }
	  }

 }
}
/**
* calendar_events search
*
* @access public
*/
 function search_calendar_events(&$out) {
  require(DIR_MODULES.$this->name.'/calendar_events_search.inc.php');
 }
/**
* calendar_events edit/add
*
* @access public
*/
 function edit_calendar_events(&$out, $id) {
  require(DIR_MODULES.$this->name.'/calendar_events_edit.inc.php');
 }
/**
* calendar_events delete record
*
* @access public
*/
 function delete_calendar_events($id) {
  //$rec=SQLSelectOne("SELECT * FROM calendar_events WHERE ID='$id'");//???
  SQLExec("DELETE FROM calendar_events WHERE ID='".$id."'");
  //освободим подчиненные задачи
  SQLExec("UPDATE calendar_events SET PARENT_ID=0 WHERE PARENT_ID='".$id."'");

 }

/**
* calendar_events delete all task
*
* @access public
*/
 function delete_all_task() {
  SQLExec("DELETE FROM calendar_events WHERE IS_TASK=1 and IS_DONE=1 and (TO_DAYS(NOW())-TO_DAYS(DONE_WHEN))>1");
 }

/**
* calendar_events delete all past events
*
* @access public
*/
 function delete_all_past_events() {
$hl_ID=-1;
$workdays_ID=-1;
$rec=SQLSelectOne('select ID from calendar_categories where holidays=1');
if ($rec) 
 $hl_ID=$rec['ID'];

$rec=SQLSelectOne('select ID from calendar_categories where workdays=1');
if ($rec) 
 $workdays_ID=$rec['ID'];

  SQLExec("DELETE FROM calendar_events WHERE CALENDAR_CATEGORY_ID<>".$hl_ID." AND CALENDAR_CATEGORY_ID<>".$workdays_ID." AND IS_TASK=0 and IS_REPEATING=0 and (TO_DAYS(NOW())-TO_DAYS(DUE))>1");
 }

/**
* calendar_categories search
*
* @access public
*/
 function search_calendar_categories(&$out) {
  require(DIR_MODULES.$this->name.'/calendar_categories_search.inc.php');
 }
/**
* calendar_categories edit/add
*
* @access public
*/
 function edit_calendar_categories(&$out, $id) {
  require(DIR_MODULES.$this->name.'/calendar_categories_edit.inc.php');
 }
/**
* calendar_categories delete record
*
* @access public
*/
 function delete_calendar_categories($id) {
  $rec=SQLSelectOne("SELECT * FROM calendar_categories WHERE ID='$id'");
  // some action for related tables
  @unlink(ROOT.'./cms/calendar/'.$rec['ICON']);
  SQLExec("DELETE FROM calendar_categories WHERE ID='".$rec['ID']."'");
 }

/**
* calendar_full
*
* @access public
*/
 function calendar_full(&$out,$m1=1,$m2=12) {
  require(DIR_MODULES.$this->name.'/calendar_full.inc.php');
 }
/**
* GetHolidays
*
* @access public
*/
 function calendar_getholidays() {
$year=date('Y');

$rec=SQLSelectOne('select ID from calendar_categories where holidays=1');
if ($rec) {
$hl_ID=$rec['ID'];
//Удаляем все записи за текущий год из календаря
//с категорией у которой стоит галочка Праздники
SQLExec('delete from calendar_events where CALENDAR_CATEGORY_ID=' . $hl_ID . ' and Year(DUE)=' . $year);
$rec=SQLSelectOne('select ID from calendar_categories where workdays=1');
$workdays_ID=$rec['ID'];
//Удаляем все записи за текущий год из календаря
//с категорией у которой стоит галочка Праздники
SQLExec('delete from calendar_events where CALENDAR_CATEGORY_ID=' . $workdays_ID . ' and Year(DUE)=' . $year);

$calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.date('Y').'/calendar.xml');
$hd=$calendar->holidays->holiday; 
$calendar = $calendar->days->day;
foreach( $hd as $hday ){
    $id = (array)$hday->attributes()->id;
    $id = $id[0]; 
    $title = (array)$hday->attributes()->title;
    $title = $title[0]; 
    $holidays[$id]=$title;
}

//все праздники за текущий год
foreach( $calendar as $day ){
    $d = (array)$day->attributes()->d;
    $d = $d[0];
    //не считая короткие дни
    if( $day->attributes()->t == 1 ) {
     $h=$day->attributes()->h;
     if (isset($holidays[(int)$h]))
      $hd_name=$holidays[(int)$h];
     else
      $hd_name='Выходной день';
//     $arHolidays[] = array('DAY'=>substr($d, 3, 2),'MONTH'=>substr($d, 0, 2),'HD_NAME'=>$hd_name);
     $Record = Array();
	 $Record['IS_TASK'] = 0;
     $Record['DUE'] = $year . '-' . substr($d, 0, 2) . '-' . substr($d, 3, 2) .' 00:00:00';
     $Record['END_TIME'] = $year . '-' . substr($d, 0, 2) . '-' . substr($d, 3, 2) .' 23:59:00';
     $Record['ALL_DAY'] = 1; 
     $Record['CALENDAR_CATEGORY_ID'] = $hl_ID;
     $Record['TITLE'] = $hd_name;
     $Record['ID']=SQLInsert('calendar_events', $Record);
     
    }
    elseif ( $day->attributes()->t ==3 ) {
//     $arWorkdays[]=array('DAY'=>substr($d, 3, 2),'MONTH'=>substr($d, 0, 2));
     $Record = Array();
	 $Record['IS_TASK'] = 0;
     $Record['DUE'] = $year . substr($d, 0, 2) . substr($d, 3, 2) .' 00:00:00' ;
     $Record['END_TIME'] = $year . '-' . substr($d, 0, 2) . '-' . substr($d, 3, 2) .' 23:59:00';
     $Record['ALL_DAY'] = 1; 
     $Record['CALENDAR_CATEGORY_ID'] = $workdays_ID;
     $Record['TITLE'] = 'Перенесенный рабочий день';
     $Record['ID']=SQLInsert('calendar_events', $Record);

    }
}
}
}
	function processSubscription($event, $details=''){
	if ($event == 'MINUTELY') {
		//process autodone tasks by due
		$sql = "SELECT ID,TITLE FROM `calendar_events` WHERE `IS_TASK`=1 and `IS_DONE`=0 and `AUTODONE`=1 and IS_NODATE=0 and date_FORMAT(`DUE`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
		$tasks = SQLSelect($sql);
		$total = count($tasks);
		for ($i = 0; $i < $total; $i++) {
			if('SETTINGS_APP_CALENDAR_LOGGING') debmes('autodone task finded "'.$tasks[$i]['TITLE'].'". process...',"calendar");
			$id = $tasks[$i]['ID'];
			if($id){
				$this -> task_done($id, 0);
			}
		}
		//закроем окончившиеся события и вычислим новые для повторов by end_time
		$sql = "SELECT ID,TITLE FROM `calendar_events` WHERE `IS_TASK`=0 and `IS_DONE`=0 and IS_NODATE=0 and date_FORMAT(`END_TIME`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
		$tasks = SQLSelect($sql);
		$total = count($tasks);
		for ($i = 0; $i < $total; $i++) {
			if('SETTINGS_APP_CALENDAR_LOGGING') debmes('ended event finded "'.$tasks[$i]['TITLE'].'". update it',"calendar");
			$id = $tasks[$i]['ID'];
			if($id){
				$this -> task_done($id, 0);
			}
		}
		//закроем окончившиеся по времени повторяющиеся задачи и вычислим новые для повторов
		$sql = "SELECT ID,TITLE FROM `calendar_events` WHERE `IS_TASK`=1 and `IS_DONE`=0 and IS_REPEATING = 1 and date_FORMAT(`END_TIME`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
		 $tasks = SQLSelect($sql);
		 $total = count($tasks);
		 for ($i = 0; $i < $total; $i++) {
			if('SETTINGS_APP_CALENDAR_LOGGING') debmes('repeat ended task finded "'.$tasks[$i]['TITLE'].'". process...',"calendar");
			$id = $tasks[$i]['ID'];
			if($id){
				$this -> task_done($id, 1);//запустим обновление без запусков скриптов
			}
		}
		//напоминалки
		$sql = "SELECT ID,TITLE FROM `calendar_events` WHERE `IS_REMIND`=1 and IS_DONE=0 and date_FORMAT(`REMIND_TIME`, '%Y%m%d%H%i')<=date_FORMAT(NOW(), '%Y%m%d%H%i')";
		$tasks = SQLSelect($sql);
		$total = count($tasks);
		for ($i = 0; $i < $total; $i++) {
			if('SETTINGS_APP_CALENDAR_LOGGING') debmes('reminder finded for task "'.$tasks[$i]['TITLE'].'". process ...',"calendar");
			$id = $tasks[$i]['ID'];
			if($id){
				$this -> process_remind($id);
			}
		}
		//закроем главные задачи, у которых выполнены все подчиненные
		$sql = "SELECT ID,TITLE FROM calendar_events calendar_events WHERE (SELECT SUM(c.IS_DONE)/COUNT(c.ID) FROM calendar_events c WHERE c.PARENT_ID = calendar_events.ID )>=1 and AUTODONE_BY_CHILDS=1 AND IS_DONE=0";
		$tasks = SQLSelect($sql);
		$total = count($tasks);
		for ($i = 0; $i < $total; $i++) {
			if('SETTINGS_APP_CALENDAR_LOGGING') debmes('main task finded "'.$tasks[$i]['TITLE'].'". Done it by all childs finished!',"calendar");
			$id = $tasks[$i]['ID'];
			if($id){
				$this -> task_done($id, 0);
			}
		}

	}
}

/**

* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
 @umask(0);
  if (!Is_Dir(ROOT."./cms/calendar")) {
   mkdir(ROOT."./cms/calendar", 0777);
  }
  parent::install();
 }
/**
* Uninstall
*
* Module uninstall routine
*
* @access public
*/
 function uninstall() {
  SQLExec('DROP TABLE IF EXISTS calendar_events');
  SQLExec('DROP TABLE IF EXISTS calendar_categories');
  unsubscribeFromEvent('app_calendar', 'MINUTELY');
  parent::uninstall();
 }
/**
* dbInstall
*
* Database installation routine
*
* @access private
*/
 function dbInstall($data) {
/*
calendar_events - Events
calendar_categories - Categories
*/
  $data = <<<EOD
 calendar_events: ID int(10) unsigned NOT NULL auto_increment
 calendar_events: TITLE varchar(255) NOT NULL DEFAULT ''
 calendar_events: SYSTEM varchar(255) NOT NULL DEFAULT ''
 calendar_events: NOTES text
 calendar_events: DUE datetime
 calendar_events: ADDED datetime
 calendar_events: DONE_WHEN datetime
 calendar_events: IS_TASK int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_DONE int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_NODATE int(3) NOT NULL DEFAULT '0'
 calendar_events: IS_REPEATING int(3) NOT NULL DEFAULT '0'
 calendar_events: REPEAT_TYPE int(3) NOT NULL DEFAULT '0'
 calendar_events: WEEK_DAYS varchar(255) NOT NULL DEFAULT ''
 calendar_events: IS_REPEATING_AFTER int(3) NOT NULL DEFAULT '0'
 calendar_events: REPEAT_IN int(10) NOT NULL DEFAULT '0'
 calendar_events: USER_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: CALENDAR_CATEGORY_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: DONE_SCRIPT_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: DONE_CODE text
 calendar_events: LOG text
 calendar_events: END_TIME datetime
 calendar_events: IS_REMIND int(3) NOT NULL DEFAULT '0'
 calendar_events: REMIND_TIME datetime DEFAULT NULL
 calendar_events: REMIND_TYPE int(3) NOT NULL DEFAULT '0'
 calendar_events: REMIND_TIMER INT(3) NOT NULL DEFAULT '0'
 calendar_events: REMIND_INT INT(3) NOT NULL DEFAULT '0'
 calendar_events: REMIND_CODE text
 calendar_events: ALL_DAY int(3) NOT NULL DEFAULT '0'
 calendar_events: AUTODONE int(3) NOT NULL DEFAULT '0'
 calendar_events: PARENT_ID int(10) NOT NULL DEFAULT '0'
 calendar_events: AUTODONE_BY_CHILDS int(3) NOT NULL DEFAULT '0'

 calendar_categories: ID int(10) unsigned NOT NULL auto_increment
 calendar_categories: TITLE varchar(255) NOT NULL DEFAULT ''
 calendar_categories: ACTIVE int(255) NOT NULL DEFAULT '0'
 calendar_categories: PRIORITY int(10) NOT NULL DEFAULT '0'
 calendar_categories: ICON varchar(70) NOT NULL DEFAULT ''
 calendar_categories: AT_CALENDAR tinyint(1) NOT NULL DEFAULT 0
 calendar_categories: CALENDAR_COLOR int(11) NOT NULL DEFAULT 0
 calendar_categories: HOLIDAYS tinyint(1) NOT NULL DEFAULT 0
 calendar_categories: WORKDAYS tinyint(1) NOT NULL DEFAULT 0
 
EOD;
  parent::dbInstall($data);
  subscribeToEvent('app_calendar', 'MINUTELY');
 }
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWF5IDA3LCAyMDEyIHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
?>
