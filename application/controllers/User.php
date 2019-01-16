<?php
header('Content-Type: application/json');
class User extends CI_Controller {
	public function index()
	{
		echo 'unAuthorize Access';
	}
	public function getUserList()
	{
		$api= array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		$pageno = $this->input->post('pageno');
		$pagelength= $this->input->post('pagelength');
		if(!isInt($pageno) && !isInt($pagelength)) {
			$result = -1;
			$msg = "입력값 오류";
			$cont = false;
		}

		if($cont) {
			$query = $this->db->get('member_list', $pageno, $pagelength);
			if ($query->num_rows() > 0)
			{
				$result = 1;
				$msg = "정상 ";
				$api['data'] = $query; // add user information
			} else {
				$result = -1;
				$msg = "비밀번호 확인 실패";
			}
		}

		$api['msg'] = $msg;
		$api['result'] = $result;
		return json_encode($api);

	}
	public function getUserInfo()
	{
		$api= array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		$idx= $this->input->get('idx');
		if(!isInt($idx)) {
			$result = -1;
			$msg = "idx is not int";
			$cont = false;
		}

		if($cont) {
			$query = $this->db->get_where('member_list', array('idx' => $idx));
			if ($query->num_rows() > 0)
			{
				$result = 1;
				$msg = "정상 ";
				$api['data'] = $query; // add user information
			} else {
				$result = -1;
				$msg = "비밀번호 확인 실패";
			}
		}

		$api['msg'] = $msg;
		$api['result'] = $result;
		return json_encode($api);

	}
	public function removeAccount()
	{
		$api = array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		$value = $this->input->post(NULL);
		$email = $value['email'];
		$password= $value['password'];

		// check id exist
		$idcheck = json_decode($this->idcheck($email), true);
		if($idcheck['result'] === 0) { // id exists
			$cont = true; 
		} else {
			$result = -1; // id exists
			$cont = false;
			$msg = "ID 없음";
		}

		// validate value
		if(injectCheck($email)) {
			$cont = true;
		} else { 
			$result = -1; // validate return fail
			$cont = false; 
			$msg = "email 입력값 체크 실패";
		}

		// check id exist
		$pwcheck = json_decode($this->passwordcheck($email, $password), true);
		if($pwcheck['result'] === 1) { // password correct
			$cont = true; 
		} else {
			$result = -1; // id exists
			$cont = false;
			$msg = "password 미일치";
		}

		if($cont) {
			// move user infomation to deleted table
			$SQL = "INSERT INTO `member_list_deleted` VALUES (SELECT * FROM `member_list` where id = '".$email."')";
			$query = $this->db->query($SQL);
			$this->db->delete('member_list', array('id' => $email));
		}
		$api['msg'] = $msg;
		$api['result'] = $result;
		return json_encode($api);
	}

	public function editPassword()
	{
		$api= array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		$value = $this->input->post(NULL);
		$email = $value['email'];
		$password_old = $value['password_old'];
		$password_new = $value['password_new'];

		// check id exist
		$idcheck = json_decode($this->idcheck($email), true);
		if($idcheck['result'] === 0) { // id exists
			$cont = true; 
		} else {
			$result = -1; // id exists
			$cont = false;
			$msg = "ID 없음";
		}

		// check id exist
		$pwcheck = json_decode($this->passwordcheck($email, $password), true);
		if($pwcheck['result'] === 1) { // password correct
			$cont = true; 
		} else {
			$result = -1; // id exists
			$cont = false;
			$msg = "password 미일치";
		}

		// validate value
		if(injectCheck($email)) {
			$cont = true;
		} else { 
			$result = -1; // validate return fail
			$cont = false; 
			$msg = "email 입력값 체크 실패";
		}


		if($cont) {
			$data = array(
				'password' => $password_new,
			);
			$this->db->where('email', $email);
			$this->db->update('member_list', $data);
		}

		$api['msg'] = $msg;
		$api['result'] = $result;
		return json_encode($api);
	}
	// edit member info except password
	public function edit()
	{
		$api= array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		$value = $this->input->post(NULL);
		$email = $value['email'];
		$name  = $value['name'];
		$phone = $value['phone'];

		// check id exist
		$idcheck = json_decode($this->idcheck($email), true);
		if($idcheck['result'] === 0) { // id exists
			$cont = true; 
		} else {
			$result = -1; // id exists
			$cont = false;
			$msg = "ID 없음";
		}

		// validate value
		if(injectCheck($email) && injectCheck($name) && injectCheck($phone)) {
			$cont = true;
		} else { 
			$result = -1; // validate return fail
			$cont = false; 
			$msg = "email, name 입력값 체크 실패";
		}


		if($cont) {
			$data = array(
				'name' => $name,
				'phone' => $phone,
			);
			$this->db->where('email', $email);
			$this->db->update('member_list', $data);

		}
		$api['msg'] = $msg;
		$api['result'] = $result;
		return json_encode($api);
	}
	public function signup()
	{
		$api= array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		$value = $this->input->post(NULL);
		$email = $value['email'];
		$name  = $value['name'];
		$password = $value['password'];
		$regdate = date("Y-m-d H:i:s");
		$affiliate = $value['affiliate'];

		$idcheck = json_decode($this->idcheck($email), true);
		if($idcheck['result'] === 1) { 
			$cont = true; 
		} else {
			$result = -1; // id exists
			$cont = false;
			$msg = "ID 중복";
		}

		if(injectCheck($email) && injectCheck($name) && injectCheck($affiliate)) {
			$cont = true;
		} else { 
			$cont = false; 
			$result = -1; // validate return fail
			$msg = "email, name, affiliate 입력값 체크 실패";
		}

		if($cont) {
			$data = array(
				'email' => $email,
				'name' => $name,
				'password' => $password,
				'regdate' => $regdate,
				'affiliate' => $affiliate,
			);
			$this->db->insert('member_list', $data);
		}

		$api['msg'] = $msg;
		$api['result'] = $result;
		return json_encode($api);
	}
	public function idcheck($id)
	{
		$result = array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		if(!$id) {
			$id = $this->input->post('email', TRUE);
		}
		if(injectCheck($id)) {
			$query = $this->db->get_where('member_list', array('email' => $id));
			if ($query->num_rows() > 0)
			{
				$result = 0;
				$msg = "이미 사용중인 ID";
			} else {
				$result = 1;
				$msg = "성공";
			}
		} else {
			$cont = false;
			$result = -1;
			$msg = "입력값 체크 실패";
		}
		$result['msg'] = $msg;
		$result['result'] = $result;
		return json_encode($result);
	}
	public function passwordcheck($id, $password)
	{
		$result = array(
			"result" => 1,
			"msg" => "성공"
		);
		$cont = true;
		$result = 1;
		$msg = "성공";

		if(injectCheck($id)) { 
			$cont = true;
		} else {
			$cont = false;
			$result = -1;
			$msg = "이메일 입력값 체크 실패";
		}
		if(strlen($password) != 64) {
			$cont = false;
			$result = -1;
			$msg = "비밀번호 오류";
		}

		if($cont) {
			$query = $this->db->get_where('member_list', array('email' => $id, 'password' => $password));
			if ($query->num_rows() > 0)
			{
				$result = 1;
				$msg = "정상 비밀번호";
			} else {
				$result = -1;
				$msg = "비밀번호 확인 실패";
			}
	}
	$result['msg'] = $msg;
	$result['result'] = $result;
	return json_encode($result);
}

function injectCheck($value) {
	$rtn = true;
	$value = urldecode($value);
    $chars = [ "<", ">", "\\", "'", "\""];
	for($i=0; $i<count($chars);$i++) {
		if(strpos($value, $chars[$i]) !== false) {
			$rtn = false;
			break;
		}
	}
	return $rtn;
}
?>

