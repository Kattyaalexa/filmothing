<?php

function draw_loginform() {
	return "
	<form method='post' name='loginform'>
		<ul style=\"width: 50px;\">
		<li><label for='login'>login</label><input type='text' name='login' value='' size='12' /></li>
		<li><label for='pass'>password</label><input type='password' name='pass' value='' size='12' /></li>
		<li><input type='button' value='login' onclick=\"sendf(this.form);\" /></li>
		</ul>
	</form>
	<div id=\"intrologo\"><img src=\"images/tapes.jpg\"/></div>
	<div id=\"introtext\"><p>Система 'ФильмоВещь' приветствует Тебя(Вас)!</p><p>Введи(те) логин и пароль для входа в систему. Для регистрации нажми(те) тег 'регистрация'.</p></div>
	<a href='#' onclick=\"sendi('registry','show');\">регистрация</a>&nbsp;&nbsp;<a href='#' onclick=\"sendi('recovery','show');\">забыли пароль?</a>
	
	";
}

function draw_regform() {
	return "
	<form method='post' name='regform'>
		<ul style=\"width: 50px;\">
		<li><label for='login'>login</label><input type='text' name='login' value='' size='12' /></li>
		<li><label for='pass'>password</label><input type='password' name='pass' value='' size='12' /></li>
		<li><label for='pass2'>confirm password</label><input type='password' name='pass2' value='' size='12' /></li>
		<li><label for='email'>email</label><input type='text' name='email' value='' size='24' /></li>
		<li><input type='button' value='registry' onclick=\"sendf(this.form);\" /></li>
		</ul>
	</form>
	<a href='#' onclick=\"sendi('login','show');\">login</a>
	<div id=\"intrologo\"><img src=\"images/cassets.jpg\"/></div>
	<div id=\"introtext\"><p>Регистрация необходима для создания аккаунта для входа в систему. </p><p>Необходимо указать правельный e-mail - он потребуется в случае восстановления пароля.</p></div>
	";
}

function draw_recovery() {
	return "
	<p>Если вы это читаете то - вероятно вы забыли пароль, либо пытаетесь взломать систему.</p>
	<p style=\"test-align: right;\">Кэп</p>
	<a href='#' onclick=\"sendi('login','show');\">login</a>";
}

function draw_addnewrecord() {
	return "
	<form method='post' name='newrec'>
		<ul style=\"width: 50px;\">
		<li><label for='caption'>название</label><input type='text' name='caption' value='' size='40' /></li>
		<li><label for='genre'>жанр</label><input type='text' name='genre' value='' size='16' /></li>
		<li><label for='country'>страна</label><input type='text' name='country' value='' size='16' /></li>
		<li><label for='director'>режиссер</label><input type='text' name='director' value='' size='24' /></li>
		<li><label for='actors'>актеры</label><input type='text' name='actors' value='' size='40' /></li>
		<li><label for='story'>сюжет</label><textarea name='story' cols='40' rows='6' /></textarea>
		<li><label for='info'>информация</label><textarea name='info' cols='40' rows='6' /></textarea>
		<li><input type='button' value='save' onclick=\"sendf(this.form);\" /></li>
		</ul>
	</form>";
}

function draw_topline($username) {
	return "
	<p>Hello, <a href='#' onclick=\"sendi('user','show');\">".$username."</a>&nbsp;(&nbsp;<a href='#' onclick=\"sendi('logout','');\">logout</a>&nbsp;)</p>";
}

function  draw_button(&$_data) {
	return "
	<input type='button' value='".$_data['caption']."' onclick=\"sendi('".$_data['sender']."','".$_data['data']."');\" />
";
}

class CDB {
	public $dbg=NULL;
	public $link=NULL;
	public $config=array('host'=>'', 'db_name'=>'', 'user'=>'', 'password'=>'');
	
	function __construct(&$_config, &$_dbg=NULL) {
		if($_dbg!=NULL) {
			$this->dbg=$_dbg;
		}
		if($_config!=NULL) {
			$this->config=array();
			$this->config['host']=$_config['host'];
			$this->config['db_name']=$_config['db_name'];
			$this->config['user']=$_config['user'];
			$this->config['password']=$_config['password'];
		}
		//echo "<p>".$this->config['host'].",".$this->config['user'].",".$this->config['password']."</p>";
	}
	
	function connect() {
		if($this->link==NULL) {
			$this->link = @mysql_connect($this->config['host'],$this->config['user'],$this->config['password']);
			if ($this->link) {
				mysql_select_db($this->config['db_name'],$this->link);
				if ($this->link) {
				//	mysql_query("SET NAMES utf8");
				//	mysql_query("SET CHARACTER SET utf8");
					return true;
				}
			}
		}
		return false;
	}
	
	function disconnect() {
		if($this->link !=NULL) {
			mysql_close($this->link);
			$this->link = NULL;
		}
	}
}


class CUser  {
	public $dbg=NULL;
	public $user=NULL;

	function __construct(&$_dbg=NULL) {
		if($_dbg!=NULL) {
			$this->dbg=$_dbg;
		}
		$this->user = array();
	}
	//$this->dbg->add_line
	function do_login($_user, $_password) {
		$query="select user_id, user_login from users where user_login='".$_user."' and user_pass='".$_password."'";
		$this->dbg->add_line($query);
		$mq = mysql_query($query);
		if($mq && mysql_num_rows($mq)==1) {
			$this->user=mysql_fetch_assoc($mq);
			//$query="update sessions set sess_timestime=".microtime()." where user_id=".$this->user['user_id'];
			session_regenerate_id();
			$query="update sessions set sess_value='".session_id()."' where user_id=".$this->user['user_id'];
			$this->dbg->add_line($query);
			$mq= mysql_query($query);
			if($mq) {
				$num=mysql_affected_rows();
				if($num == 0 ) {
					$query="insert into sessions set sess_value='".session_id()."', user_id=".$this->user['user_id'];
					$this->dbg->add_line($query);
					return (mysql_query($query) && mysql_affected_rows());
				} else if( $num == 1 ) {
					return true;
				}
			}
		}
		return false;
	}
	
	function do_registry($_login, $_password, $_email) {
		$query="select * from users where user_login='".$_login."' or user_email='".$_email."'";
		$this->dbg->add_line($query);
		$mq = mysql_query($query);
		if( $mq && mysql_num_rows($mq)==0 ) {
			$this->dbg->add_line($query);
			$query="insert into users set user_login='".$_login."', user_pass='".$_password."', user_email='".$_email."'";
			return ( mysql_query($query) && mysql_affected_rows()==1 );
		} else {
			$this->dbg->add_line("запись с таким логином емейлом уже есть либо запрос накрылся");
		}
		return false;
	}
	
	function do_logout() {
		$query="update sessions set sess_value='".md5(microtime())."' where user_id=".$this->user['user_id'];
		return ( mysql_query($query) && mysql_affected_rows()==1 );
	}
	
	function do_auth() {
		$query="select users.user_id from sessions, users where sessions.user_id=users.user_id and sessions.sess_value='".session_id()."'";
		$this->dbg->add_line($query);
		$mq = mysql_query($query);
		if($mq && mysql_num_rows($mq)==1) {
			$this->user=mysql_fetch_assoc($mq);
			return true;
		}
		return false;
	}
}

class CCommander {
	public $carr;
	function __construct() {
		$this->carr = array();
		//$data_arr = $this->carr;
	}
	function add_cmd($_target, $_data, $_mode=0) {
		 $this->carr[]=array( 'target'=>$_target, 'data'=>$_data, 'mode'=>$_mode );
	}
	
	function add_cmd_arr($_target, &$_data, $_mode=0) {
		 $this->carr[]=array( 'target'=>$_target, 'data'=>$_data, 'mode'=>$_mode );
	}

	function get_cmd() {
		return $this->carr;
	}
}

class CMyDebug {
	public $cmd;
	public $log;
	public $debugenable;
	
	function __construct(&$_cmd=NULL, $mode=false) {
		$this->cmd=$_cmd;
		$this->set_mode($mode);
	}
	
	function set_mode($mode) {
		if(is_bool($mode)) {
			$this->debugenable=$mode;
		}
	}
	
	function add_line($_line) {
		if($this->debugenable) {
			$data_sub = date("H:i:s");//date("Y-m-d H:i:s", str)
			$this->cmd->add_cmd( 'debug',"<p>".$data_sub." > ".$_line."</p>", 1 );
		}
	}
}


class CState {
	public $state_unit;
	public $state_name_list;

	//public $state_list;
	function __construct() {
		$this->state_name_list=array('mode','username', 'pagenum', 'item');
		$this->state_unit=array();
	}
	
	function get_state($name, $xvalue=NULL) {
		if(!isset($_SESSION[$name])) {
			if($xvalue!=NULL) {
				$_SESSION[$name]=$xvalue;
			} else {
				$_SESSION[$name]="";
			}
		}
		return $_SESSION[$name];
	}
	
	function set_state($name, $value) {
		$_SESSION[$name]=$value;
	}
	
	function scan_states() {
		$this->state_unit=array();
		foreach($this->state_name_list as $state_name) {
			if(isset($_SESSION[$state_name]) ) {
				$this->state_unit[$state]=$_SESSION[$state_name];
			} else {
				$_SESSION[$state_name]="";
			}
		}
	}
}

class CVizuller {
	public $dbg=NULL;
	public $data=NULL;
	public $filter=NULL;
	function __construct( $_dbg=NULL ) {
		if($_dbg!=NULL) {
			$this->dbg=$_dbg;
		}
		$this->data=array();
		$this->filter=array();
	}

	function get_size() {
		return count($this->data);
	}
	
	function add_record(&$_row) {
		$this->data[]=$_row;
	}

	
	function render_list(&$_template=NULL, &$_action=NULL) {
		$out="<table>";
		if($_template!=NULL && is_array($_template) ) {
			$out.="<tr><th>".join("</th><th>",array_values($_template) )."</th></tr>";
			foreach ($this->data as $line) {
				$out.="<tr>";
				$templkeys=array_keys($_template);
				foreach($templkeys as $key) {
					if($_action!=NULL && array_key_exists($key, $_action) ) {
						$out.="<td><a href='#' onclick=\"sendi('".$_action[$key][0]."','".$line[$_action[$key][1]]."');\">".$line[$key]."</td>";
					} else {
						$out.="<td>".$line[$key]."</td>";
					}
				}
				if($_action!=NULL && is_array($_action)){
					foreach( $_action as $name=>$act) {
						if(!in_array($name, $templkeys)) {
							$out.="<td><a href='#' onclick=\"sendi('".$act[0]."','".$line[$act[1]]."');\">".$name."</td>";
						}
					}
				}
				$out.="</tr>";
			}
		} else {
			foreach ($this->data as $line) {
				$out.="<tr><td>".join("</td><td>",$line )."</td></tr>";
			}
		}
		$out.="</table>";
		return $out;
	}
	
	function render_record() {
		$out="
			<h4>Название</h4>
			<p>".$this->data[0]['mat_caption']."</p>
			<hr>
			<p style=\"text-align: right;\">юзер: ".$this->data[0]['user_login']." (рейтинг ".$this->data[0]['user_rate'].")</p>
			<li><span>Жанр: </span>".$this->data[0]['mat_genre']."</li>
			<li><span>Страна: </span>".$this->data[0]['mat_country']."</li>
			<li><span>Режиссер: </span>".$this->data[0]['mat_director']."</li>
			<li><span>Актеры: </span>".$this->data[0]['mat_actors']."</li>
			<li><p>Описание</p>".$this->data[0]['mat_story']."</li>
			<li><p>Дополнительно</p>".$this->data[0]['mat_info']."</li>
		";
		return $out;
	}

}

class CMovie {
	public $dbg=NULL;
	public $data=NULL; //internal storing
	public $pagesize=5;
	function __construct( $_dbg=NULL , $_page_size=5) {
		if($_dbg!=NULL) {
			$this->dbg=$_dbg;
		}
		$this->data=array('set'=>'','rec'=>''); //set of data and one line of a data
		
		$this->pagesize=$_page_size;
	}
	
	function get_pagesize() {
		return $this->pagesize;
	}
	
	function get_list($_user_id, &$_vizuller, $_page_num=0) {
		if($_id!=NULL) {
			$query = "select materials.mat_id, materials.mat_caption, materials.mat_genre, materials.mat_rate from materials, matlinks where materials.mat_id=matlinks.mat_id and matlinks.user_id=".$_user_id;
		} else {
			$query = "select materials.mat_id, materials.mat_caption, materials.mat_genre, materials.mat_rate from materials";
		}
		//if($_page_num!=NULL) {
		$query .= " limit ".$_page_num.", ".$this->pagesize;
		//}
		$this->dbg->add_line($query);
		$mq = mysql_query($query);
		if ( $mq && mysql_affected_rows()>0 ) {
			while($row=mysql_fetch_array($mq)) {
				//$this->data['set'][]=$row;
				$_vizuller->add_record($row);
			}
			return true;
		}
		return false;
	}
	
	function get_record($_id, &$_vizuller) {
		$query = "select materials.mat_id, materials.mat_caption, materials.mat_genre, materials.mat_country, materials.mat_director, materials.mat_actors, materials.mat_story, materials.mat_info, materials.mat_rate, users.user_id, users.user_login, users.user_rate from materials,users where materials.mat_id=".$_id." and users.user_id=materials.user_id";
		$this->dbg->add_line($query);
		$mq = mysql_query($query);
		if ( $mq && mysql_affected_rows()==1 ) {
			$_vizuller->add_record(mysql_fetch_array($mq));
			return true;
		}
		return false;
	}

	//rec - simple array
	function add_new_record(&$_rec) {
		$query="insert into materials values (".join(',',$_rec).")";
		$this->dbg->add_line($query);
	//	$mq = mysql_query($query);
		if( mysql_query($query) && mysql_affected_rows()==1 ) {
			$query="insert into matlinks values (NULL, ".mysql_insert_id().", ".$_rec[1].",0)";
			$this->dbg->add_line($query);
			return ( mysql_query($query) && mysql_affected_rows()==1 );
		}
		return false;
	}

	// rec is associative array
	function modify_record(&$_usr, &$_rec) {
		$query="update materials ";
		if(is_array($_rec)) {
			$spl="";
			$query.=" set ";
			foreach ($_rec as $k=>$v) {
				$query.=$spl.$k."='".$v."'";
				$spl=", ";
			}
		}
		$query.="where materials.user_id=matlinks.mat_id and matlinks.user_id=".$_usr['user_id'];
		$mq = mysql_query($query);
		return ( mysql_query($query) && mysql_affected_rows()==1 );
	}
	
	function delete_record( $_mat_id, $_usr_id) {
		$query="delete from materials where user_id=".$_usr_id." and mat_id=".$_mat_id;
		$this->dbg->add_line($query);
		if ( mysql_query($query) && mysql_affected_rows()==1 ) {
			$query="delete from matlinks where mat_id=".$_mat_id;
			$this->dbg->add_line($query);
			return ( mysql_query($query) && mysql_affected_rows()==1 );
		}
		return false;
	}
}

?>