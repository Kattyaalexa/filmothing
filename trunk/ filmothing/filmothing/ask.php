<?php
session_start();
if( @file_exists( "./lib.php")) {
    include_once "./lib.php";
}

class CProcess {
	public $dbg=NULL;
	public $cmd=NULL;
	public $usr=NULL;
	public $state=NULL;
	public $data=NULL;
	
	function __construct(&$_cmd, &$_usr, &$_state, &$_data, &$_dbg=NULL) {
		if($_dbg!=NULL) {
			$this->dbg=$_dbg;
		}

		$this->cmd=$_cmd;
		$this->usr=$_usr;
		$this->state=$_state;
		$this->data=$_data;
	}

	function process() {
		
		$this->dbg->add_line("PROCESS. state: ".$this->state->get_state('mode').", name:".$this->data['p']['name'].", data:".$this->data['p']['data']);
		switch($this->state->get_state('mode')) {
			case 'logout':
				$this->usr->do_logout();
				$this->state->set_state('mode','login');
				$this->state->set_state('username','');
				$this->cmd->add_cmd('topline', '');
				$this->cmd->add_cmd('control', '');
				$this->cmd->add_cmd('main', draw_loginform());
				break;
			case 'newrecord':
				switch($this->data['p']['name']) {
				/*	case 'movielist':
						$this->state->set_state('mode','movielist');
						$this->data['p']['name']='show';
						$this->process();
						break;*/
					case 'newrec':
						$this->dbg->add_line("new record mode processing");
						$checklist=array('caption','genre','country','director','actors','story','info');
						$filmdata=array('NULL',$this->usr->user['user_id']);
						foreach( $checklist as $k) {
							if( array_key_exists($k ,$this->data['p']['data']) ){
								$filmdata[]="'".$this->data['p']['data'][$k]."'";
							} else {
								$filmdata[]='empty';
							}
						}
						$filmdata[]=0;
						$this->dbg->add_line(join(' $ ',$filmdata));
						
						$mlist = new CMovie($this->dbg);
						if( $mlist->add_new_record($filmdata) ) {
							$this->state->set_state('mode','movielist');
							$this->data['p']['name']='show';
							$this->process();
						}
						//$this->cmd->add_cmd('control', '' );
						break;
					case 'show':
					default:
						$this->cmd->add_cmd('main', draw_addnewrecord() );
						
						//$this->dbg->add_line("авторизовано: user id: ".$this->usr->user['user_id']);
						$this->cmd->add_cmd('topline', draw_topline($this->state->get_state('username')) );
						$btn=array( 'caption'=>'добавить', 'sender'=>'newrecord','data'=>'show');
						$btn2=array( 'caption'=>'показать список', 'sender'=>'movielist','data'=>'show');
						$btn3=array( 'caption'=>'показать мой список', 'sender'=>'mymovies','data'=>'show');
						$this->cmd->add_cmd('control', draw_button($btn).draw_button($btn2).draw_button($btn3) );
						break;
					
						//$this->dbg->add_line("def name");
						//$this->data['p']['name']='show';
						//break;
				}
				break;
			case 'mymovies':
				$this->dbg->add_line("my movies");
				switch($this->data['p']['name']) {
					case 'moviedel':
						$mlist = new CMovie($this->dbg);
						if( $mlist->delete_record( $this->data['p']['data'], $this->usr->user['user_id']) ) {
							$this->cmd->add_cmd('main', 'запись удалена',1 );
							$this->data['p']['name']='show';
							$this->process();
						} else {
							$this->cmd->add_cmd('main', 'ошибка при удалении',1 );
						}
						break;
					case 'show':
						$this->dbg->add_line("my movies: show");
						$mlist = new CMovie($this->dbg);
						$tbl = new CVizuller($this->dbg);
						
						if($mlist->get_list($this->usr->user['user_id'], $tbl)) {
							//$this->dbg->add_line("test: ".join(" % ", $tbl->data[0]));
							$template=array('mat_caption'=>'название', 'mat_genre'=>'жанр', 'mat_rate'=>'рейтинг');
							$actions=array( 'mat_caption'=>array('moviedet','mat_id'), 'delete'=>array('moviedel','mat_id') );
							$this->cmd->add_cmd('main', $tbl->render_list($template, $actions) );
						}
						break;
					default:
						$this->cmd->add_cmd('topline', draw_topline($this->state->get_state('username')) );
						
						$btn=array( 'caption'=>'добавить', 'sender'=>'newrecord','data'=>'show');
						$btn2=array( 'caption'=>'показать список', 'sender'=>'movielist','data'=>'show');
						$btn3=array( 'caption'=>'показать мой список', 'sender'=>'mymovies','data'=>'show');
						$this->cmd->add_cmd('control', draw_button($btn).draw_button($btn2).draw_button($btn3) );
						$this->cmd->add_cmd('navigator', "" );
						//$left=array( 'caption'=>'влево', 'sender'=>'scrollleft','data'=>'');
						//$right=array( 'caption'=>'вправо', 'sender'=>'scrollright','data'=>'');
						//$this->cmd->add_cmd('navigator', draw_button($left)."&nbsp;<span id='pagenumber'>".$this->state->get_state('page')."</span>&nbsp;".draw_button($right) );
						
						$this->data['p']['name']='show';
						$this->process();
						//$this->dbg->add_line("my movies: def");
						//$this->data['p']['name']='show';
						//$this->process();
						break;
				}
				break;
			case 'movielist':
				switch($this->data['p']['name']) {
					case 'scrollleft':
						$page=$this->state->get_state('page') - 1;
						if($page<0) {
							$page=0;
						}
						$this->state->set_state('page',$page);
						$this->data['p']['name']='show';
						$this->process();
						break;
					case 'scrollright':
						$page=$this->state->get_state('page') + 1;
						$this->state->set_state('page',$page);
						$this->data['p']['name']='show';
						$this->process();
						break;
					case 'show':
						$mlist = new CMovie($this->dbg);
						$tbl = new CVizuller($this->dbg);
						
						$range_start=$this->state->get_state('page')*$mlist->get_pagesize();
						if($mlist->get_list(NULL, $tbl, $range_start )) {
							$template=array('mat_caption'=>'название', 'mat_genre'=>'жанр', 'mat_rate'=>'рейтинг');
							$actions=array( 'mat_caption'=>array('moviedet','mat_id') );
							$this->cmd->add_cmd('main', $tbl->render_list($template, $actions) );
							$this->cmd->add_cmd('pagenumber', $this->state->get_state('page')+1);
						} else {
							$page=$this->state->get_state('page') - 1;
							if($page < 0) {
								$page=0;
							}
							$this->state->set_state('page',$page);
						}
						break;
					default:
						$this->cmd->add_cmd('topline', draw_topline($this->state->get_state('username')) );
						
						$btn=array( 'caption'=>'добавить', 'sender'=>'newrecord','data'=>'show');
						$btn2=array( 'caption'=>'показать список', 'sender'=>'movielist','data'=>'show');
						$btn3=array( 'caption'=>'показать мой список', 'sender'=>'mymovies','data'=>'show');
						$this->cmd->add_cmd('control', draw_button($btn).draw_button($btn2).draw_button($btn3) );
						
						$left=array( 'caption'=>'влево', 'sender'=>'scrollleft','data'=>'');
						$right=array( 'caption'=>'вправо', 'sender'=>'scrollright','data'=>'');
						$this->cmd->add_cmd('navigator', draw_button($left)."&nbsp;<span id='pagenumber'>".$this->state->get_state('page')."</span>&nbsp;".draw_button($right) );
						
						$this->data['p']['name']='show';
						$this->process();
						break;
				}
				break;
			case 'moviedet':
				switch($this->data['p']['name']) {
					case 'show':
					default:
						$mlist = new CMovie($this->dbg);
						$tbl = new CVizuller($this->dbg);
						if($mlist->get_record($this->data['p']['data'], $tbl)) {
							$this->dbg->add_line("##".$tbl->data[0]['mat_caption']);
							$this->cmd->add_cmd('main', $tbl->render_record() );
						} else {
							$this->state->set_state('mode','movielist');
							$this->data['p']['name']='show';
							$this->process();
						}
						$this->cmd->add_cmd('topline', draw_topline($this->state->get_state('username')) );
						$btn=array( 'caption'=>'добавить', 'sender'=>'newrecord','data'=>'show');
						$btn2=array( 'caption'=>'показать список', 'sender'=>'movielist','data'=>'show');
						$btn3=array( 'caption'=>'показать мой список', 'sender'=>'mymovies','data'=>'show');
						$this->cmd->add_cmd('control', draw_button($btn).draw_button($btn2).draw_button($btn3) );
						break;
				}
			break;
			default:
				$this->state->set_state('mode','movielist');
				$this->data['p']['name']='';
				$this->cmd->add_cmd('topline', draw_topline($this->state->get_state('username')) );
				//$btn=array( 'caption'=>'добавить', 'sender'=>'newrecord','data'=>'show');
				//$btn2=array( 'caption'=>'показать список', 'sender'=>'movielist','data'=>'show');
				//$btn3=array( 'caption'=>'показать мой список', 'sender'=>'mymovies','data'=>'show');
				//$this->cmd->add_cmd('control', draw_button($btn).draw_button($btn2).draw_button($btn3) );
				$this->process();
				break;
			
				//$cmd->add_cmd('main', "<p>я не знаю - что тут должно рисоваться при исполненном вами сценарии навигации!</p>" );
				//$dbg->add_line("undef mode for ball processing... :( ");
				
			break;
		}
		$this->dbg->add_line("process end");
		$this->dbg->add_line("state: ".$this->state->get_state('mode'));
	}
}


/*	
$dbconfig = array(
	'host'=>'sql208.0fees.net',
	'db_name'=>'fees0_3157581_filmohren',
	'user'=>'fees0_3157581',
	'password'=>'3mk8a9');
*/
$dbconfig = array(
	'host'=>'localhost',
	'db_name'=>'db_filmohren',
	'user'=>'root',
	'password'=>'');

$cmd = new CCommander();
$dbg = new CMyDebug($cmd, true);
$db = new CDB($dbconfig, $dbg);
$proc = NULL;
$auth=true;
//$resp_arr = array('data'=>'','target'=>'','mode'=>'0');
if( $db->connect() ) {
	$usr = new CUser($dbg);
	$state = new CState();
	$data = array('p'=>'');
	
	$state->scan_states();
	
	if( isset($_POST['ball']) && $_POST['ball']!="" ) {
		$dbg->add_line("POST: ".$_POST['ball']);
	
		$_POST['ball']=str_replace("\\", "", $_POST['ball'] );
		$data['p']=json_decode($_POST['ball'],true);
		if( isset($data['p']) && is_array( $data['p']) &&
				isset($data['p']['data']) && is_array( $data['p']['data'] ) ) {
			$dbg->add_line("decoding successfull...");
			$dbg->add_line("sender: ".$data['p']['name']);
			foreach ($data['p']['data'] as $k=>$v) {
				$dbg->add_line($k.", ".$v);
			}	
		}
	}
	//$state->set_state('mode','');
	$dbg->add_line("STATE. mode:".$state->get_state('mode').", ".$state->get_state('username')); 
	$dbg->add_line("NAME:".$data['p']['name']."; DATA:".$data['p']['data']);
	if( $usr->do_auth() != true ) {
		$auth=false;
		$dbg->add_line("не авторизовано");
		switch($state->get_state('mode')) {
			case 'login':
				switch( $data['p']['name'] ) {
					case 'loginform':
						if( isset($data['p']['data']['login']) &&
								isset($data['p']['data']['pass']) &&
									$usr->do_login($data['p']['data']['login'], $data['p']['data']['pass'])) {
										$dbg->add_line("login success");
										$auth=true;
										$state->set_state('mode','movielist');
										$state->set_state('username',$usr->user['user_login']);
										$cmd->add_cmd('topline', '');
										$cmd->add_cmd('control', '');
						} else {
							$dbg->add_line("something wrong... your login request is not valid!");
						}
						break;
					case 'registry':
						$cmd->add_cmd('main', draw_regform());
						$state->set_state('mode','registry');
						break;
					case 'recovery':
						$dbg->add_line("Im recovery!");
						$cmd->add_cmd('main', draw_recovery());
						$state->set_state('mode','recovery');
						break;
					default:
						$cmd->add_cmd('main', draw_loginform());
						break;
				}
				break;
			case 'registry':
				$dbg->add_line("processing regestry mode");
				switch( $data['p']['name'] ) {
					case 'regform':
						$dbg->add_line("processing regform");
						if( isset($data['p']['data']['login']) &&
								isset($data['p']['data']['pass']) &&
									isset($data['p']['data']['pass2']) &&
										isset($data['p']['data']['email']) && 
											!strcmp ($data['p']['data']['pass'], $data['p']['data']['pass2']) &&
												$usr->do_registry($data['p']['data']['login'], $data['p']['data']['pass'], $data['p']['data']['email'])) {
							$auth=true;
							$state->set_state('mode','movielist');
							$state->set_state('username',$usr->user['user_login']);
							$cmd->add_cmd('topline', '');
							$cmd->add_cmd('control', '');
						} else {
							$dbg->add_line("something wrong... your registry request is not valid!");
						}
						break;
					case 'login':
						$cmd->add_cmd('main', draw_loginform());
						$state->set_state('mode','login');
						break;
					default:
						$cmd->add_cmd('main', draw_regform());
						break;
				}
				break;
			case 'recovery':
				$dbg->add_line("processing recovery mode");
				switch( $data['p']['name'] ) {
					case 'login':
						$cmd->add_cmd('main', draw_loginform());
						$state->set_state('mode','login');
						break;
					default:
						$cmd->add_cmd('main', draw_recovery());
						break;
				}
				break;
			default:
				$cmd->add_cmd('main', draw_loginform());
				$state->set_state('mode','login');
				break;
		}

		//process_guest($cmd, $usr, $state,  $data, $dbg);//$cmd->add_cmd('main', draw_loginform());
	}
	if($auth) {
		$modes=array('logout', 'movielist', 'newrecord', 'moviedet','mymovies');
		if( strcmp($data['p']['name'],"") && in_array($data['p']['name'], $modes) && strcmp($data['p']['name'],$state->get_state('mode'))) {
			$state->set_state('mode',$data['p']['name']);
			$data['p']['name']='show';
		}
		$proc = new CProcess($cmd, $usr, $state, $data, $dbg);
		$proc->process();
	}
	
	$db->disconnect();
} else {
	$dbg->add_line("bad db connection");
}

echo json_encode($cmd->get_cmd());
?>